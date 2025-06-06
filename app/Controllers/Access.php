<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\AccessModel;
use CodeIgniter\I18n\Time;

class Access extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $userData, $currentDateTime;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function check()
    {
        helper(['form', 'firebaseJWT', 'hashid']);

        $rules  =   [
            'hardwareID'    =>  ['label' => 'Hardware ID', 'rules' => 'required|alpha_numeric_punct|min_length[10]'],
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $hardwareID         =   strtoupper($this->request->getVar('hardwareID'));
        $username           =   $this->request->getVar('username');
        $userTimeZoneOffset =   $this->request->getVar('userTimeZoneOffset');
        $header             =   $this->request->getServer('HTTP_AUTHORIZATION');
        $explodeHeader      =   $header != "" ? explode(' ', $header) : [];
        $token              =   is_array($explodeHeader) && isset($explodeHeader[1]) && $explodeHeader[1] != "" ? $explodeHeader[1] : "";
        $timeCreate         =   Time::now(APP_TIMEZONE)->toDateTimeString();
        $statusCode         =   401;
        $responseMsg        =   'Please enter your username and password';
        $captchaCode        =   generateRandomCharacter(4, 3);

        $userAdminData  =   array(
            "name"  =>   "",
            "email" =>   ""
        );

        $tokenPayload   =   array(
            "idUserAdmin"       =>  0,
            "idUserAdminLevel"  =>  0,
            "username"          =>  "",
            "name"              =>  "",
            "email"             =>  "",
            "captchaCode"       =>  $captchaCode,
            "hardwareID"        =>  $hardwareID,
            "userTimeZoneOffset"=>  $userTimeZoneOffset,
            "timeCreate"        =>  $timeCreate
        );

        $defaultToken           =   encodeJWTToken($tokenPayload);

        if(isset($token) && $token != ""){
            try {
                $dataDecode     =   decodeJWTToken($token);
                $idUserAdmin    =   intval($dataDecode->idUserAdmin);
                $hardwareIDToken=   $dataDecode->hardwareID;
                $timeCreateToken=   $dataDecode->timeCreate;

                if($idUserAdmin != 0){
                    $accessModel    =   new AccessModel(); 
                    $userAdminDataDB=   $accessModel
                                        ->where("IDUSERADMIN", $idUserAdmin)
                                        ->first();

                    if(!$userAdminDataDB || is_null($userAdminDataDB)) return throwResponseUnauthorized('[E-AUTH-001.1.0] Your user is not registered. Please log in to continue', ['token'=>$defaultToken]);

                    $hardwareIDDB   =   $userAdminDataDB['HARDWAREID'];
                    $usernameDB     =   $userAdminDataDB['USERNAME'];

                    if($hardwareID == $hardwareIDDB && $hardwareID == $hardwareIDToken){
                        $timeCreateToken    =   Time::parse($timeCreateToken, APP_TIMEZONE);
                        $minutesDifference  =   $timeCreateToken->difference(Time::now(APP_TIMEZONE))->getMinutes();

                        if($minutesDifference > MAX_INACTIVE_SESSION_MINUTES){
                            return throwResponseForbidden('Session ends, please log in first');
                        }
            
                        $accessModel->update($idUserAdmin, ['DATETIMELOGIN' => $timeCreate]);

                        $userAdminData  =   [
                            "name"  =>   $userAdminDataDB['NAME'],
                            "email" =>   $userAdminDataDB['EMAIL']
                        ];

                        if(isset($username) && $username != null){
                            if($username != $usernameDB) {
                                $accessModel->where('HARDWAREID', $hardwareID)->set('HARDWAREID', 'null', false)->update();
                                return throwResponseConlflict('Invalid user, unmatched user credentials');
                            }
                        }

                        $tokenPayload['idUserAdmin']        =   $idUserAdmin;
                        $tokenPayload['idUserAdminLevel']   =   $userAdminDataDB['IDUSERADMINLEVEL'];
                        $tokenPayload['username']           =   $userAdminDataDB['USERNAME'];
                        $tokenPayload['name']               =   $userAdminDataDB['NAME'];
                        $tokenPayload['initialName']        =   getInitialsName($userAdminDataDB['NAME']);
                        $tokenPayload['email']              =   $userAdminDataDB['EMAIL'];
                        $statusCode                         =   200;
                        $responseMsg                        =   'Login successfully, continue';
                    } else {
                        return throwResponseUnauthorized('[E-AUTH-001.1.2] Hardware ID changed, please login to continue', ['token'=>$defaultToken]);
                    }
                }
            } catch (\Throwable $th) {
                return throwResponseUnauthorized('[E-AUTH-001.2.0] Invalid Token', ['token'=>$defaultToken]);
            }
        }

        $newToken       =   encodeJWTToken($tokenPayload);
        $optionHelper   =   $this->getDataOption();
        return $this->setResponseFormat('json')
                    ->respond([
                        'token'         =>  $newToken,
                        'userAdminData' =>  $userAdminData,
                        'optionHelper'  =>  $optionHelper,
                        'messages'      =>  [
                            "accessMessage" =>  $responseMsg
                        ]
                    ])
                    ->setStatusCode($statusCode);

    }

    public function login()
    {
        helper(['form']);
        $rules  =   [
            'username'  =>  'required|min_length[5]',
            'password'  =>  'required|min_length[5]',
            'captcha'   =>  'required|alpha_numeric|exact_length[4]'
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $accessModel    =   new AccessModel();
        $username       =   $this->request->getVar('username');
        $password       =   $this->request->getVar('password');
        $captcha        =   $this->request->getVar('captcha');
        $captchaToken   =   $this->userData->captchaCode;

        if($captcha != $captchaToken) return $this->fail('The captcha code you entered does not match');

        $dataUserAdmin  =   $accessModel->where("USERNAME", $username)->where("STATUS", 1)->first();

        if(!$dataUserAdmin) return $this->failNotFound('There are no matching usernames, enter another username');
 
        $passwordVerify =   password_verify($password, $dataUserAdmin['PASSWORD']);
        if(!$passwordVerify) return $this->fail('The password you entered is incorrect');

        $idUserAdmin        =   $dataUserAdmin['IDUSERADMIN'];
        $idUserAdminLevel   =   $dataUserAdmin['IDUSERADMINLEVEL'];
        $name               =   $dataUserAdmin['NAME'];
        $email              =   $dataUserAdmin['EMAIL'];
        $currentDateTime    =   $this->currentDateTime;
        $hardwareID         =   $this->userData->hardwareID;
        
        $dataUpdateUserAdmin    =   [
            'HARDWAREID'    =>   $hardwareID,
            'DATETIMELOGIN' =>   $currentDateTime    
        ];

        $accessModel        =   new AccessModel();
        $accessModel->where('HARDWAREID', $hardwareID)->set('HARDWAREID', 'null', false)->update();
        $accessModel->update($idUserAdmin, $dataUpdateUserAdmin);

        $tokenUpdate        =   array(
            "idUserAdmin"       =>  $idUserAdmin,
            "idUserAdminLevel"  =>  $idUserAdminLevel,
            "username"          =>  $username,
            "initialName"       =>  getInitialsName($name),
            "name"              =>  $name,
            "email"             =>  $email
        );
        
        $optionHelper   =   $this->getDataOption();
        return $this->setResponseFormat('json')
                    ->respond([
                        'tokenUpdate'   =>  $tokenUpdate,
                        'optionHelper'  =>  $optionHelper,
                        'message'       =>  "Login successfully"
                    ]);		
    }

    public function roketRedirect($base64JsonData = false)
    {
        if(!$base64JsonData || $base64JsonData == "") return view('errors/html/error_404', ['message' => '404 - Page not found']);
        helper(['firebaseJWT']);

        try {
            $accessModel    =   new AccessModel();
            $dataDecode     =   json_decode(base64_decode($base64JsonData));
            $redirectToken  =   $dataDecode->redirectToken;
            $destinationMenu=   $dataDecode->destinationMenu;
            $parameters     =   $dataDecode->parameters;
            $userAdminData  =   $accessModel
                                ->select('USERNAME')
                                ->where("REDIRECTTOKEN", $redirectToken)
                                ->first();

            if(!$userAdminData || is_null($userAdminData)) return view('errors/html/error_404', ['message' => '404 - Invalid token acess']);
            return view(
                'loginRedirect',
                [
                    'username'          =>  $userAdminData['USERNAME'],
                    'destinationMenu'   =>  $destinationMenu,
                    'parameters'        =>  json_encode($parameters)
                ]
            );
        } catch (\Throwable $th) {
            return view('errors/html/error_404', ['message' => '404 - Page not found, invalid data parameters']);
        }
    }

    public function logout($token = false)
    {
        if(!$token || $token == "") return $this->failUnauthorized('[E-AUTH-001.1] Token Required');
        helper(['firebaseJWT']);

        try {
            $dataDecode         =   decodeJWTToken($token);
            $idUserAdmin        =   $dataDecode->idUserAdmin;
            $hardwareID         =   $dataDecode->hardwareID;
            $accessModel        =   new AccessModel();
            $userAdminDataDB    =   $accessModel
                                    ->where("IDUSERADMIN", $idUserAdmin)
                                    ->first();

            if(!$userAdminDataDB || is_null($userAdminDataDB)) return redirect()->to(BASE_URL.'logoutPage');

            $hardwareIDDB       =   $userAdminDataDB['HARDWAREID'];

            if($hardwareID == $hardwareIDDB){
                $accessModel->where('HARDWAREID', $hardwareID)->set('HARDWAREID', 'null', false)->update();
            }

            return redirect()->to(BASE_URL.'logoutPage');
        } catch (\Throwable $th) {
            return $this->failUnauthorized('[E-AUTH-001.2] Token tidak valid - '.$th->getMessage());
        }
    }

    public function captcha($token = '')
    {
        if(!$token || $token == "") $this->returnBlankCaptcha();
        helper(['firebaseJWT']);

        try {
            $dataDecode     =   decodeJWTToken($token);
            $captchaCode    =   $dataDecode->captchaCode;
            $codeLength     =   strlen($captchaCode);

            generateCaptchaImage($captchaCode, $codeLength);
        } catch (\Throwable $th) {
            $this->returnBlankCaptcha();
        }
    }

    private function returnBlankCaptcha()
    {
        $img    =   imagecreatetruecolor(120, 20);
        $bg     =   imagecolorallocate ( $img, 255, 255, 255 );
        imagefilledrectangle($img, 0, 0, 120, 20, $bg);
        
        ob_start();
        imagejpeg($img, "blank.jpg", 100);
        $contents = ob_get_contents();
        ob_end_clean();

        $dataUri = "data:image/jpeg;base64," . base64_encode($contents);
        echo $dataUri;
    }

    private function getDataOption()
    {
        $accessModel            =   new AccessModel();
        $dataReservationType    =   encodeDatabaseObjectResultKey($accessModel->getDataReservationType(), 'ID', true);
        $dataAllAreaType        =   encodeDatabaseObjectResultKey($accessModel->getDataAllAreaType(), 'ID');
        $dataUserAdminLevel     =   encodeDatabaseObjectResultKey($accessModel->getDataUserAdminLevel(), 'ID');
        $dataUserAdminLevelMenu =   encodeDatabaseObjectResultKey($accessModel->getDataUserAdminLevelMenu(), 'ID');
        $dataNameTitle          =   encodeDatabaseObjectResultKey($accessModel->getDataNameTitle(), 'ID');
        $dataCountryPhoneCode   =   encodeDatabaseObjectResultKey($accessModel->getDataCountryPhoneCode(), 'ID');

        return [
            "dataReservationType"   =>  $dataReservationType,
            "dataAllAreaType"       =>  $dataAllAreaType,
            "dataUserAdminLevel"    =>  $dataUserAdminLevel,
            "dataUserAdminLevelMenu"=>  $dataUserAdminLevelMenu,
            "dataNameTitle"         =>  $dataNameTitle,
            "dataCountryPhoneCode"  =>  $dataCountryPhoneCode,
            "optionHours"	        =>  OPTION_HOURS,
            "optionMinutes"         =>  OPTION_MINUTES,
            "optionMinuteInterval"	=>  OPTION_MINUTEINTERVAL,
            "optionMonth"	        =>  OPTION_MONTH,
            "optionYear"	        =>  OPTION_YEAR
        ];
    }

    public function getDataOptionByKey($keyName, $optionName = false, $keyword = false)
    {
        $accessModel    =   new AccessModel();
        $optionName     =   $optionName != false ? $optionName : 'randomOption';
        $dataOption     =   [];
        $arrEncodeKey   =   ['ID'];

        switch($keyName){
            default :
                break;
        }

        $dataOption     =   encodeDatabaseObjectResultKey($dataOption, $arrEncodeKey);
        return $this->setResponseFormat('json')
                ->respond([
                    "dataOption"    =>  $dataOption,
                    "optionName"    =>  $optionName
                ]);
    }

    public function detailProfileSetting()
    {
        $accessModel    =   new AccessModel();
        $idUserAdmin    =   $this->userData->idUserAdmin;
        $detailUserAdmin=   $accessModel->getUserAdminDetail($idUserAdmin);

        if(is_null($detailUserAdmin)) return throwResponseNotFound("User admin details not found");
        unset($detailUserAdmin['IDUSERADMINLEVEL']);
        return $this->setResponseFormat('json')
                    ->respond([
                        "detailUserAdmin"   =>  $detailUserAdmin
                     ]);
    }

    public function saveDetailProfileSetting()
    {
        helper(['form']);
        $idUserAdmin  =   $this->userData->idUserAdmin;
        $rules          =   [
            'username'  => ['label' => 'Username', 'rules' => 'required|alpha_numeric|min_length[4]'],
            'name'      => ['label' => 'Nama', 'rules' => 'required|alpha_numeric_space|min_length[4]'],
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $accessModel        =   new AccessModel();
        $username           =   $this->request->getVar('username');
        $name               =   $this->request->getVar('name');
        $currentPassword    =   $this->request->getVar('currentPassword');
        $newPassword        =   $this->request->getVar('newPassword');
        $repeatPassword     =   $this->request->getVar('repeatPassword');
        $relogin            =   false;

        $arrUpdateUserAdmin =   [
            'NAME'      =>  $name,
            'USERNAME'  =>  $username
        ];

        if($currentPassword != "" || $newPassword != "" || $repeatPassword != ""){
			if($currentPassword == "") return throwResponseNotAcceptable("Please enter your old password (your current password)");
			if($newPassword == "") return throwResponseNotAcceptable("Please enter a new password");
            if($repeatPassword == "") return throwResponseNotAcceptable("Please enter a new password repeat");
			if($newPassword != $repeatPassword) return throwResponseNotAcceptable("The repetition of the password you entered is not match");
			
            $dataUserAdmin  =   $accessModel->where("IDUSERADMIN", $idUserAdmin)->first();
            if(!$dataUserAdmin) return $this->failNotFound('Your user data was not found, please try again later');
            $passwordVerify =   password_verify($currentPassword, $dataUserAdmin['PASSWORD']);
            if(!$passwordVerify) return $this->fail('The old password you entered is incorrect');
			
			$arrUpdateUserAdmin['PASSWORD'] =	password_hash($newPassword, PASSWORD_DEFAULT);
            $relogin                        =   true;
		}

        $accessModel->update($idUserAdmin, $arrUpdateUserAdmin);
        $tokenUpdate    =   [
            "username"  =>  $username,
            "name"      =>  $name
        ];

        return $this->setResponseFormat('json')
                    ->respond([
                        "message"       =>  "Your user data has been updated",
                        "relogin"       =>  $relogin,
                        "tokenUpdate"   =>  $tokenUpdate
                     ]);
    }
}