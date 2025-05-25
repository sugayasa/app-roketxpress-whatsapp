<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;
use App\Controllers\BaseController;
use App\Libraries\OneMsgIO;
use App\Models\MainOperation;
use App\Models\CronModel;

class Cron extends BaseController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
     use ResponseTrait;
    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function execChatCron()
    {
        echo date('Y-m-d H:i:s')." - Start\n";

        $cronModel          =   new CronModel();
        $mainOperation      =   new MainOperation();
        $oneMsgIO           =   new OneMsgIO();
        $currentDateTime    =   date('Y-m-d H:i:s');
        $currentTimeStamp   =   Time::now('UTC')->getTimestamp();
        $hourNow            =   date('H');

        if($hourNow > 7 && $hourNow < 22){
            $dataChatCron       =   $cronModel->getDataChatCron();
            if($dataChatCron){
                foreach($dataChatCron as $keyChatCron){
                    $idChatCron                 =   $keyChatCron->IDCHATCRON;
                    $idContact                  =   $keyChatCron->IDCONTACT;
                    $idReservation              =   $keyChatCron->IDRESERVATION;
                    $idReservationReconfirmation=   $keyChatCron->IDRESERVATIONRECONFIRMATION;
                    $idChatTemplate             =   $keyChatCron->IDCHATTEMPLATE;
                    $phoneNumber                =   $keyChatCron->PHONENUMBER;
                    $templateCode               =   $keyChatCron->TEMPLATECODE;
                    $templateLanguageCode       =   $keyChatCron->TEMPLATELANGUAGECODE;
                    $parametersHeader           =   $keyChatCron->PARAMETERSHEADER;
                    $parametersHeader           =   !is_null($parametersHeader) && $parametersHeader != '' ? json_decode($parametersHeader) : [];
                    $parametersBody             =   $keyChatCron->PARAMETERSBODY;
                    $parametersBody             =   !is_null($parametersBody) && $parametersBody != '' ? json_decode($parametersBody) : [];
                    $detailReservation          =   $cronModel->getDetailReservation($idReservation);

                    if($detailReservation){
                        $arrParametersTemplate      =   [];
                        $arrParametersTemplateHeader=   $this->generateParametersTemplate('header', $parametersHeader, $detailReservation);
                        $arrParametersTemplateBody  =   $this->generateParametersTemplate('body', $parametersBody, $detailReservation);
                        $arrUpdateReconfirmation    =   ['STATUS'    =>  -1];

                        if (!is_null($arrParametersTemplateHeader)) array_push($arrParametersTemplate, $arrParametersTemplateHeader);
                        if (!is_null($arrParametersTemplateBody)) array_push($arrParametersTemplate, $arrParametersTemplateBody);
                        $sendResult             =   $oneMsgIO->sendMessageTemplate($templateCode, $templateLanguageCode, $phoneNumber, $arrParametersTemplate);

                        if(!$sendResult['isSent']){
                            $errorCode          =   $sendResult['errorCode'];
                            $errorMsg           =   $sendResult['errorMsg'];

                            switch($errorCode){
                                case 'E0001'    :   $mainOperation->updateDataTable('t_contact', ['ISVALIDWHATSAPP' => -1], ['IDCONTACT' => $idContact]); break;
                                default         :   break;
                            }

                            $parametersTemplate =   [
                                "header"    =>  $this->generateParametersTemplate('header', $parametersHeader, $detailReservation, true),
                                "body"      =>  $this->generateParametersTemplate('body', $parametersBody, $detailReservation, true)
                            ];
                            $mainOperation->insertLogFailedMessage($idChatTemplate, $idContact, $phoneNumber, json_encode($parametersTemplate), $errorCode, $errorMsg);

                            $arrUpdateCron  =   [
                                "STATUS"        =>  -1,
                                "DATETIMESENT"  =>  $currentDateTime
                            ];
                            $mainOperation->updateDataTable('t_chatcron', $arrUpdateCron, ['IDCHATCRON' => $idChatCron]);
                        } else {
                            $idMessage                  =   $sendResult['idMessage'];
                            $listOfTemplate             =   $oneMsgIO->getListOfTemplates();
                            $messageTemplateGenerated   =   $oneMsgIO->generateMessageFromTemplateAndParam($templateCode, $listOfTemplate, $arrParametersTemplate);

                            if($messageTemplateGenerated) {
                                $idChatThread   =   $mainOperation->insertUpdateChatTable($currentTimeStamp, $idContact, $idMessage, $messageTemplateGenerated, 1, ['forceUpdate' => true]);
                            
                                $arrUpdateCron  =   [
                                    "IDCHATTHREAD"  =>  $idChatThread,
                                    "STATUS"        =>  1,
                                    "DATETIMESENT"  =>  $currentDateTime
                                ];
                                $mainOperation->updateDataTable('t_chatcron', $arrUpdateCron, ['IDCHATCRON' => $idChatCron]);
                            }

                            $arrUpdateReconfirmation    =   [
                                'DATETIMESENT'  =>  $currentDateTime,
                                'STATUS'        =>  1
                            ];
                        }
                        
                        if($idReservationReconfirmation != 0) $mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservationreconfirmation', $arrUpdateReconfirmation, ['IDRESERVATIONRECONFIRMATION' => $idReservationReconfirmation]);
                    }
                }
            }
        } else {
            echo "Inactive period\n";
        }

        echo date('Y-m-d H:i:s')." - Done";
        die();
    }

    private function generateParametersTemplate($parametersTemplateType, $parametersTemplate, $detailReservation, $isReturnArray = false)
    {
        $oneMsgIO       =   new OneMsgIO();
        $arrParameters  =   [];

        if($parametersTemplate){
            foreach($parametersTemplate as $keyParameter => $textParameter){
                switch($keyParameter){
                    case 'SOURCENAME':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->SOURCENAME), '-');
                        break;
                    case 'BOOKINGCODE':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->BOOKINGCODE), '-');
                        break;
                    case 'CUSTOMERNAME':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->CUSTOMERNAME), '-');
                        break;
                    case 'RESERVATIONTITLE':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONTITLE), '-');
                        break;
                    case 'RESERVATIONDATE':
                        $durationDay            =   issetAndNotNull(replaceNewLine($detailReservation->SOURCENAME), '-');
                        $reservationDateStart   =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONDATESTART), '-');
                        $reservationDateEnd     =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONDATEEND), '-');
                        $reservationDateStr     =   $reservationDateStart;
                        if($durationDay > 1) $reservationDateStart." - ".$reservationDateEnd;
                        $arrParameters[]        =   $reservationDateStr;
                        break;
                    case 'RESERVATIONDATESTART':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONDATESTART), '-');
                        break;
                    case 'RESERVATIONDATEEND':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONDATEEND), '-');
                        break;
                    case 'PICKUPTIME':
                    case 'RESERVATIONTIMESTART':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONTIMESTART), '-');
                        break;
                    case 'RESERVATIONTIMEEND':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->RESERVATIONTIMEEND), '-');
                        break;
                    case 'DURATIONOFDAY':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->DURATIONOFDAY), '-');
                        break;
                    case 'NUMBEROFADULT':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->NUMBEROFADULT), '-');
                        break;
                    case 'NUMBEROFCHILD':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->NUMBEROFCHILD), '-');
                        break;
                    case 'NUMBEROFINFANT':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->NUMBEROFINFANT), '-');
                        break;
                    case 'DETAILPAX':
                        $paxDetail      =   '';
                        $paxDetail      .=  $detailReservation->NUMBEROFADULT > 0 ? $detailReservation->NUMBEROFADULT." Adult " : '';
                        $paxDetail      .=  $detailReservation->NUMBEROFCHILD > 0 ? $detailReservation->NUMBEROFCHILD." Child " : '';
                        $paxDetail      .=  $detailReservation->NUMBEROFINFANT > 0 ? $detailReservation->NUMBEROFINFANT." Infant " : '';
                        $arrParameters[]=   $paxDetail == '' ? '-' : $paxDetail;
                        break;
                    case 'PICKUPLOCATION':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->PICKUPLOCATION), '-');
                        break;
                    case 'HOTELNAME':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->HOTELNAME), '-');
                        break;
                    case 'REMARK':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->REMARK), '-');
                        break;
                    case 'SPECIALREQUEST':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->SPECIALREQUEST), '-');
                        break;
                    case 'TOURPLAN':
                        $arrParameters[]    =   issetAndNotNull(replaceNewLine($detailReservation->TOURPLAN), '-');
                        break;
                    default:
                        $arrParameters[]    =   '-';
                        break;
                }
            }
        }

        if($isReturnArray) return is_array($arrParameters) && count($arrParameters) > 0 ? $arrParameters : null;
        $returnArrayParameters  =   [
            "type"      =>  $parametersTemplateType,
            "parameters"=>  $oneMsgIO->generateParametersTemplate($arrParameters)
        ];
        return is_array($arrParameters) && count($arrParameters) > 0 ? $returnArrayParameters : null;
    }
}