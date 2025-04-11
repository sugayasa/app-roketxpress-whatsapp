<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Libraries\OneMsgIO;
use App\Models\MainOperation;
use App\Models\ContactModel;

class Contact extends ResourceController
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

    public function getDataContact()
    {
        helper(['form']);
        $rules      =   [
            'contactType'   =>  ['label' => 'Contact Type', 'rules' => 'required|in_list[1,2,3,4,5]']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());
        
        $mainOperation      =   new MainOperation();
        $contactModel       =   new ContactModel();
        $page               =   $this->request->getVar('page');
        $contactType        =   $this->request->getVar('contactType');
        $searchKeyword      =   $this->request->getVar('searchKeyword');
        $date               =   new Time('now');
        $dataContact        =   [];
        
        switch($contactType){
            case 1      :
            case "1"    :
                $dataContact    =   $contactModel->getDataContactRecentlyAdd($page, $searchKeyword, true);
                break;
            case 2      :
            case "2"    :
                $dateTomorrow   =   $date->modify('+1 day');
                $dateTomorrow   =   $dateTomorrow->format('Y-m-d');
                $dataContact    =   $contactModel->getDataContactReservation($dateTomorrow, $searchKeyword);
                break;
            case 3      :
            case "3"    :
                $dateToday      =   $date->format('Y-m-d');
                $dataContact    =   $contactModel->getDataContactReservation($dateToday, $searchKeyword);
                break;
            case 4      :
            case "4"    :
                $dateYesterday  =   $date->modify('-1 day');
                $dateYesterday  =   $dateYesterday->format('Y-m-d');
                $dataContact    =   $contactModel->getDataContactReservation($dateYesterday, $searchKeyword);
                break;
            case 5      :
            case "5"    :
                $dataContact    =   $contactModel->getDataContactRecentlyAdd($page, $searchKeyword);
                break;
        }

        $askQuestionTemplate    =   $mainOperation->getDataChatTemplate(4);
        if($dataContact && count($dataContact) > 0) $dataContact        =   encodeDatabaseObjectResultKey($dataContact, 'IDCONTACT');
        if($askQuestionTemplate) $askQuestionTemplate['IDCHATTEMPLATE'] =   hashidEncode($askQuestionTemplate['IDCHATTEMPLATE']);
        return $this->setResponseFormat('json')
                    ->respond([
                        "dataContact"           =>  $dataContact,
                        "askQuestionTemplate"   =>  $askQuestionTemplate
                     ]);
    }
    
    public function getDetailContact()
    {
        helper(['form']);
        $rules      =   [
            'idContact' => ['label' => 'Id contact', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idContact'    => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $contactModel           =   new ContactModel();
        $idContact              =   $this->request->getVar('idContact');
        $idContact              =   hashidDecode($idContact);
        $detailContact          =   $contactModel->getDetailContact($idContact);
        $listDetailReservation  =   $contactModel->getListDetailReservation($idContact);
        $lastReplyDateTime      =   $detailContact['LASTREPLYDATETIME'];
        $isChatSessionActive    =   false;

        if($lastReplyDateTime != ''){
            $lastReplyDateTimeIntervalMinutes   =   getDateTimeIntervalMinutes($lastReplyDateTime);
            $detailContact['LASTREPLYDATETIME'] =   getDateTimeIntervalStringInfo($lastReplyDateTime, 24);
            if($lastReplyDateTimeIntervalMinutes <= (23.5 * 60)) $isChatSessionActive   =   true;
        }

        if($listDetailReservation){
            foreach($listDetailReservation as $keyDetailReservation){
                $idReservartion         =   $keyDetailReservation->IDRESERVATION;
                $listTemplateMessage    =   $contactModel->getListTemplateMessage($idReservartion);
                $reservationDateStart   =   $keyDetailReservation->RESERVATIONDATESTART;
                $reservationDateStartDT =   new Time($reservationDateStart);
                $currentDateDT          =   new Time();
                $minimumDateDT          =   $currentDateDT->modify('-30 days');

                if ($reservationDateStartDT > $minimumDateDT) {
                    $keyDetailReservation->ALLOWASKQUESTION =   true;
                } else {
                    $keyDetailReservation->ALLOWASKQUESTION =   false;
                }
                $listTemplateMessage                        =   encodeDatabaseObjectResultKey($listTemplateMessage, 'IDCHATTEMPLATE');
                $keyDetailReservation->LISTTEMPLATEMESSAGE  =   json_encode($listTemplateMessage);
            }
            $listDetailReservation  =   encodeDatabaseObjectResultKey($listDetailReservation, 'IDRESERVATION');
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "detailContact"         =>  $detailContact,
                        "isChatSessionActive"   =>  $isChatSessionActive,
                        "listDetailReservation" =>  $listDetailReservation
                     ]);
    }
    
    public function sendTemplateMessage()
    {
        helper(['form']);
        $rules      =   [
            'idContact'         =>  ['label' => 'Contact Data', 'rules' => 'required|alpha_numeric'],
            'phoneNumber'       =>  ['label' => 'Contact Data', 'rules' => 'required|numeric'],
            'templateData'      =>  ['label' => 'Template Data', 'rules' => 'required|is_array'],
            'templateParameters'=>  ['label' => 'Template Data', 'rules' => 'required|is_array']
        ];

        $messages   =   [
            'idContact'     => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ],
            'phoneNumber'   => [
                'required'  => 'Phone number is required',
                'numeric'   => 'Invalid phone number. The {field} must contain only numbers'
            ],
            'templateData'  => [
                'required'  => 'Invalid data sent',
                'is_array'  => 'Invalid data sent'
            ],
            'templateParameters'    => [
                'required'  => 'Invalid data sent',
                'is_array'  => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $mainOperation              =   new MainOperation();
        $oneMsgIO                   =   new OneMsgIO();
        $currentDateTime            =   $this->currentDateTime;
        $idContact                  =   $this->request->getVar('idContact');
        $phoneNumber                =   $this->request->getVar('phoneNumber');
        $templateData               =   $this->request->getVar('templateData');
        $templateParameters         =   $this->request->getVar('templateParameters');
        $idContact                  =   hashidDecode($idContact);
        $idChatTemplate             =   hashidDecode($templateData->IDCHATTEMPLATE);
        $templateName               =   $templateData->TEMPLATECODE;
        $templateLanguageCode       =   $templateData->TEMPLATELANGUAGECODE;
        $templateParametersHeader   =   $templateParameters->parametersHeader;
        $templateParametersBody     =   $templateParameters->parametersBody;
        $arrTemplateParameters      =   [];

        if(isset($templateParametersHeader) && is_array($templateParametersHeader) && count($templateParametersHeader) > 0){
            $arrTemplateParameters[]    =   [
                "type"      =>  "header",
                "parameters"=>  $oneMsgIO->generateParametersTemplate($templateParametersHeader)
            ];
        }

        if(isset($templateParametersBody) && is_array($templateParametersBody) && count($templateParametersBody) > 0){
            $arrTemplateParameters[]    =   [
                "type"      =>  "body",
                "parameters"=>  $oneMsgIO->generateParametersTemplate($templateParametersBody)
            ];
        }

        $sendResult =   $oneMsgIO->sendMessageTemplate($templateName, $templateLanguageCode, $phoneNumber, $arrTemplateParameters);

        if(!$sendResult['isSent']){
            $errorCode  =   $sendResult['errorCode'];
            $errorMsg   =   $sendResult['errorMsg'];
            $mainOperation->insertLogFailedMessage($idChatTemplate, $idContact, $phoneNumber, $templateParameters, $errorCode, $errorMsg);
            switch($errorCode){
                case 'E0001'    :   $mainOperation->updateDataTable('t_contact', ['ISVALIDWHATSAPP' => -1], ['IDCONTACT' => $idContact]);
                                    return throwResponseInternalServerError('Message delivery failed. The recipient`s number (+'.$phoneNumber.') is not registered as a valid WhatsApp user.', $sendResult);
                case 'E1012'    :   return throwResponseInternalServerError('Invalid message sent. Please remove tab, new line and more than 4 consecutive spaces in the message', $sendResult);
                default         :   return throwResponseInternalServerError('Failed to send message. Please try again later', $sendResult);
            }
        } else {
            $idMessage                  =   $sendResult['idMessage'];
            $listOfTemplate             =   $oneMsgIO->getListOfTemplates();
            $messageTemplateGenerated   =   $oneMsgIO->generateMessageFromTemplateAndParam($templateName, $listOfTemplate, $arrTemplateParameters);

            if($messageTemplateGenerated) $mainOperation->insertUpdateChatTable($currentDateTime, $idContact, $idMessage, $messageTemplateGenerated);
            $mainOperation->updateDataTable('t_contact', ['ISVALIDWHATSAPP' => 1], ['IDCONTACT' => $idContact]);
            return throwResponseOK('Message has been sent');
        }
    }
}