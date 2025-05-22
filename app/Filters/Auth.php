<?php

namespace App\Filters;

use \AllowDynamicProperties;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use App\Models\AccessModel;

#[AllowDynamicProperties]
class Auth implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */

    public object $request;
    #[\ReturnTypeWillChange]
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['firebaseJWT']);
        $header     =   $request->getServer('HTTP_AUTHORIZATION');
        if(!$header) return throwResponseUnauthorized('[E-AUTH-000] Token is required');
        $arrHeader  =   $header != "" ? explode(' ', $header) : [];
        $token      =   count($arrHeader) > 0 && isset($arrHeader[1]) ? $arrHeader[1] : "";
        
        try {
            $dataDecode                 =   decodeJWTToken($token);
            $request->token             =   $token;
            $request->currentDateDT     =   Time::today(APP_TIMEZONE);
            $request->currentDate       =   Time::now(APP_TIMEZONE)->toDateString();
            $request->currentDateTime   =   Time::now(APP_TIMEZONE)->toDateTimeString();
            $request->currentTimeStamp  =   Time::now('UTC')->getTimestamp();
            $request->userData          =   $dataDecode;
            $idUserAdmin                =   $dataDecode->idUserAdmin;
            $tokenTimeCreate            =   $dataDecode->timeCreate;
            $tokenTimeCreate            =   Time::parse($tokenTimeCreate, APP_TIMEZONE);
            $minutesDifference          =   $tokenTimeCreate->difference(Time::now(APP_TIMEZONE))->getMinutes();
            $urlSegment2                =   $request->getUri()->getSegment(2);

            if($minutesDifference > MAX_INACTIVE_SESSION_MINUTES && $urlSegment2 != 'login'){
                return throwResponseUnauthorized('Session ends, please log in to continue');
            }

            if(isset($arguments) && $arguments[0] == 'mustNotBeLoggedIn'){
                if(isset($idUserAdmin) && $idUserAdmin != "" && intval($idUserAdmin) != 0){
                    return throwResponseUnauthorized('You are not allowed to perform this action because your login session is already active');
                }
            } else if(isset($arguments) && $arguments[0] == 'mustBeLoggedIn'){
                if(!isset($idUserAdmin) || $idUserAdmin == "" || intval($idUserAdmin) == 0){
                    return throwResponseUnauthorized('Please log in to perform this action');
                }

                $hardwareID         =   $dataDecode->hardwareID;
                $accessModel        =   new AccessModel();
                $isValidHardwareID  =   $accessModel->checkHardwareIDUserAdmin($idUserAdmin, $hardwareID);

                if(!$isValidHardwareID){
                    return throwResponseUnauthorized('[E-AUTH-001.1.2] Your hardware ID has changed, please log in to continue');
                }

                $accessModel->setLastActivityUserAdmin($idUserAdmin, $request->currentDateTime);
            }
        } catch (\Throwable $th) {
            return throwResponseUnauthorized('[E-AUTH-001] Invalid Token');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $responseBody   =   $response->getBody();
        $statusCode     =   $response->getStatusCode();
        $currentDateTime=   Time::now(APP_TIMEZONE)->toDateTimeString();

        if (!empty($responseBody)) {
            @json_decode($responseBody);
            if(json_last_error() === JSON_ERROR_NONE){
                $responseBody   =   json_decode($responseBody, true);
                if($statusCode == 200){
                    if(isset($responseBody['tokenPayload'])){
                        $responseBody['tokenPayload']['timeCreate'] =   $currentDateTime;
                        $newToken                                   =   encodeJWTToken($responseBody['tokenPayload']);
                        $responseBody['token']                      =   $newToken;
                        unset($responseBody['tokenPayload']);
                    } else if(isset($responseBody['tokenUpdate'])){
                        try {
                            $arrTokenOrigin     =   (array) decodeJWTToken($request->token);
                            foreach($responseBody['tokenUpdate'] as $keyUpdate => $valueUpdate){
                                $arrTokenOrigin[$keyUpdate] =   $valueUpdate;
                            }
                            $arrTokenOrigin['timeCreate']   =   $currentDateTime;
                            $newToken                       =   encodeJWTToken($arrTokenOrigin);
                            $responseBody['token']          =   $newToken;
                            unset($responseBody['tokenUpdate']);
                        } catch (\Throwable $th) {
                            return throwResponseInternalServerError("Internal server error - Auth");
                        }
                    } else {
                        $arrTokenOrigin                 =   (array) decodeJWTToken($request->token);
                        $arrTokenOrigin['timeCreate']   =   $currentDateTime;
                        $newToken                       =   encodeJWTToken($arrTokenOrigin);
                        $responseBody['token']          =   $newToken;
                    }

                    return $response->setBody(json_encode($responseBody));
                }
            } else {
                $messageError   =   "";
                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH           :   $messageError   =   ' - Maximum stack depth exceeded'; break;
                    case JSON_ERROR_STATE_MISMATCH  :   $messageError   =   ' - Underflow or the modes mismatch'; break;
                    case JSON_ERROR_CTRL_CHAR       :   $messageError   =   ' - Unexpected control character found'; break;
                    case JSON_ERROR_SYNTAX          :   $messageError   =   ' - Syntax error, malformed JSON'; break;
                    case JSON_ERROR_UTF8            :   $messageError   =   ' - Malformed UTF-8 characters, possibly incorrectly encoded'; break;
                    default                         :   $messageError   =   ' - Unknown error'; break;
                }
                return throwResponseInternalServerError("Internal server error ".$messageError);
            }
        }
    }
}
