<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
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

        $cronModel      =   new CronModel();
        $mainOperation  =   new MainOperation();
        $oneMsgIO       =   new OneMsgIO();
        $currentDateTime=   date('Y-m-d H:i:s');
        
        $dataChatCron   =   $cronModel->getDataChatCron();

        if($dataChatCron){
            foreach($dataChatCron as $keyChatCron){
                $idChatCron             =   $keyChatCron->IDCHATCRON;
                $idContact              =   $keyChatCron->IDCONTACT;
                $idReservation          =   $keyChatCron->IDRESERVATION;
                $idChatTemplate         =   $keyChatCron->IDCHATTEMPLATE;
                $phoneNumber            =   $keyChatCron->PHONENUMBER;
                $templateCode           =   $keyChatCron->TEMPLATECODE;
                $templateLanguageCode   =   $keyChatCron->TEMPLATELANGUAGECODE;
                $parametersHeader       =   $keyChatCron->PARAMETERSHEADER;
                $parametersHeader       =   !is_null($parametersHeader) && $parametersHeader != '' ? json_decode($parametersHeader) : [];
                $parametersBody         =   $keyChatCron->PARAMETERSBODY;
                $parametersBody         =   !is_null($parametersBody) && $parametersBody != '' ? json_decode($parametersBody) : [];
                $detailReservation      =   $cronModel->getDetailReservation($idReservation);

                if($detailReservation){
                    $arrParametersTemplate      =   [];
                    $arrParametersTemplateHeader=   $this->generateParametersTemplate('header', $parametersHeader, $detailReservation);
                    $arrParametersTemplateBody  =   $this->generateParametersTemplate('body', $parametersBody, $detailReservation);
                    if (!is_null($arrParametersTemplateHeader)) array_push($arrParametersTemplate, $arrParametersTemplateHeader);
                    if (!is_null($arrParametersTemplateBody)) array_push($arrParametersTemplate, $arrParametersTemplateBody);

                    $sendResult                 =   $oneMsgIO->sendMessageTemplate($templateCode, $templateLanguageCode, $phoneNumber, $arrParametersTemplate);

                    if(!$sendResult['isSent']){
                        $errorCode          =   $sendResult['errorCode'];
                        $errorMsg           =   $sendResult['errorMsg'];
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
                        $arrUpdateCron  =   [
                            "STATUS"        =>  1,
                            "DATETIMESENT"  =>  $currentDateTime
                        ];

                        $mainOperation->updateDataTable('t_chatcron', $arrUpdateCron, ['IDCHATCRON' => $idChatCron]);
                        $idMessage                  =   $sendResult['idMessage'];
                        $listOfTemplate             =   $oneMsgIO->getListOfTemplates();
                        $messageTemplateGenerated   =   $oneMsgIO->generateMessageFromTemplateAndParam($templateCode, $listOfTemplate, $arrParametersTemplate);

                        if($messageTemplateGenerated) $mainOperation->insertUpdateChatTable($currentDateTime, $idContact, $idMessage, $messageTemplateGenerated);
                    }                
                }
            }
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
                        $arrParameters[]    =   issetAndNotNull($detailReservation->SOURCENAME, '-');
                        break;
                    case 'BOOKINGCODE':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->BOOKINGCODE, '-');
                        break;
                    case 'CUSTOMERNAME':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->CUSTOMERNAME, '-');
                        break;
                    case 'RESERVATIONTITLE':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->RESERVATIONTITLE, '-');
                        break;
                    case 'RESERVATIONDATE':
                        $durationDay            =   issetAndNotNull($detailReservation->SOURCENAME, '-');
                        $reservationDateStart   =   issetAndNotNull($detailReservation->RESERVATIONDATESTART, '-');
                        $reservationDateEnd     =   issetAndNotNull($detailReservation->RESERVATIONDATEEND, '-');
                        $reservationDateStr     =   $reservationDateStart;
                        if($durationDay > 1) $reservationDateStart." - ".$reservationDateEnd;
                        $arrParameters[]        =   $reservationDateStr;
                        break;
                    case 'RESERVATIONDATESTART':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->RESERVATIONDATESTART, '-');
                        break;
                    case 'RESERVATIONDATEEND':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->RESERVATIONDATEEND, '-');
                        break;
                    case 'PICKUPTIME':
                    case 'RESERVATIONTIMESTART':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->RESERVATIONTIMESTART, '-');
                        break;
                    case 'RESERVATIONTIMEEND':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->RESERVATIONTIMEEND, '-');
                        break;
                    case 'DURATIONOFDAY':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->DURATIONOFDAY, '-');
                        break;
                    case 'NUMBEROFADULT':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->NUMBEROFADULT, '-');
                        break;
                    case 'NUMBEROFCHILD':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->NUMBEROFCHILD, '-');
                        break;
                    case 'NUMBEROFINFANT':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->NUMBEROFINFANT, '-');
                        break;
                    case 'DETAILPAX':
                        $paxDetail      =   '';
                        $paxDetail      .=  $detailReservation->NUMBEROFADULT > 0 ? $detailReservation->NUMBEROFADULT." Adult " : '';
                        $paxDetail      .=  $detailReservation->NUMBEROFCHILD > 0 ? $detailReservation->NUMBEROFCHILD." Child " : '';
                        $paxDetail      .=  $detailReservation->NUMBEROFINFANT > 0 ? $detailReservation->NUMBEROFINFANT." Infant " : '';
                        $arrParameters[]=   $paxDetail == '' ? '-' : $paxDetail;
                        break;
                    case 'PICKUPLOCATION':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->PICKUPLOCATION, '-');
                        break;
                    case 'REMARK':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->REMARK, '-');
                        break;
                    case 'TOURPLAN':
                        $arrParameters[]    =   issetAndNotNull($detailReservation->TOURPLAN, '-');
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