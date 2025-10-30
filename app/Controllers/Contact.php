<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Libraries\AIBot;
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
    protected $userData, $currentDateTime, $currentTimeStamp;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
            $this->currentTimeStamp =   $request->currentTimeStamp;
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
        if($dataContact && count($dataContact) > 0) {
            $dataContact        =   encodeDatabaseObjectResultKey($dataContact, 'IDCONTACT');
            $dataContact        =   encodeDatabaseObjectResultKey($dataContact, 'IDCHATLIST', true);
        }
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
        $lastReplyDateTime      =   $detailContact['DATETIMELASTREPLY'];
        $lastReplyDateTimeTF    =   $lastReplyDateTime == '' ? '' : Time::createFromTimestamp($lastReplyDateTime);
        $lastReplyDateTimeStr   =   $lastReplyDateTime == '' ? '' : $lastReplyDateTimeTF->toLocalizedString('yyyy-MM-dd HH:mm:ss');
        $isChatSessionActive    =   false;

        if($lastReplyDateTimeStr != ''){
            $lastReplyDateTimeIntervalMinutes   =   getDateTimeIntervalMinutes($lastReplyDateTimeStr);
            if($lastReplyDateTimeIntervalMinutes <= (24 * 60)) $isChatSessionActive   =   true;
        }

        if($listDetailReservation){
            foreach($listDetailReservation as $keyDetailReservation){
                $idReservation          =   $keyDetailReservation->IDRESERVATION;
                $listTemplateMessage    =   $contactModel->getListTemplateMessage($idReservation);
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

        $detailContact['IDCHATLIST']    =   hashidEncode($detailContact['IDCHATLIST'], true);
        $detailContact['IDCOUNTRY']     =   hashidEncode($detailContact['IDCOUNTRY']);
        $detailContact['IDNAMETITLE']   =   hashidEncode($detailContact['IDNAMETITLE']);
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
            'idReservation'     =>  ['label' => 'Contact Data', 'rules' => 'permit_empty|alpha_numeric'],
            'phoneNumber'       =>  ['label' => 'Contact Data', 'rules' => 'required|numeric'],
            'templateData'      =>  ['label' => 'Template Data', 'rules' => 'required|is_array'],
            'templateParameters'=>  ['label' => 'Template Data', 'rules' => 'required|is_array']
        ];

        $messages   =   [
            'idContact'     => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ],
            'idReservation' => [
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

        $aiBot                      =   new AIBot();
        $oneMsgIO                   =   new OneMsgIO();
        $mainOperation              =   new MainOperation();
        $currentTimeStamp           =   $this->currentTimeStamp;
        $idContact                  =   $this->request->getVar('idContact');
        $idReservation              =   $this->request->getVar('idReservation');
        $phoneNumber                =   $this->request->getVar('phoneNumber');
        $templateData               =   $this->request->getVar('templateData');
        $templateParameters         =   $this->request->getVar('templateParameters');
        $idContact                  =   hashidDecode($idContact);
        $idReservation              =   isset($idReservation) && $idReservation != '' ?  hashidDecode($idReservation) : null;
        $idChatTemplate             =   hashidDecode($templateData->IDCHATTEMPLATE);
        $templateName               =   $templateData->TEMPLATECODE;
        $templateLanguageCode       =   $templateData->TEMPLATELANGUAGECODE;
        $isCronGreeting             =   $templateData->ISCRONGREETING;
        $isCronReconfirmation       =   $templateData->ISCRONRECONFIRMATION;
        $isCronReviewRequest        =   $templateData->ISCRONREVIEWREQUEST;
        $isQuestion                 =   $templateData->ISQUESTION;
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
            $idUserAdmin                =   $this->userData->idUserAdmin;
            $listOfTemplate             =   $oneMsgIO->getListOfTemplates();
            $messageTemplateGenerated   =   $oneMsgIO->generateMessageFromTemplateAndParam($templateName, $listOfTemplate, $arrTemplateParameters);
            $handleStatus               =   1;
            
            if($isQuestion || $isQuestion == 1) {
                $handleStatus = 2;
                $aiBot->changeHandleStatus(($handleStatus - 1), $phoneNumber);
            }
            
            if($messageTemplateGenerated) {
                $detailReservationsData =   $mainOperation->getDetailReservationById($idReservation);
                $aiBOTTemplateResponse  =   $aiBot->sendTemplateMessageToBOT($phoneNumber, $messageTemplateGenerated['body'], $detailReservationsData);
                $mainOperation->insertUpdateChatTable($currentTimeStamp, $idContact, $idMessage, $messageTemplateGenerated, $idUserAdmin, ['forceUpdate' => true, 'handleStatus' => $handleStatus]);
                
                if(ENVIRONMENT === 'development') log_message("debug", "aiBOTTemplateResponse :: ". json_encode($aiBOTTemplateResponse));
            }
            else return throwResponseInternalServerError('Failed to generate message from template. Please try again later');
            $mainOperation->updateDataTable('t_contact', ['ISVALIDWHATSAPP' => 1], ['IDCONTACT' => $idContact]);
            return throwResponseOK('Message has been sent');
        }
    }

    public function saveContact()
    {
        helper(['form']);
        $rules      =   [
            'editorContact-nameTitle'   =>  ['label' => 'Name Title', 'rules' => 'required|alpha_numeric'],
            'editorContact-name'        =>  ['label' => 'Name', 'rules' => 'required|alpha_numeric_space|min_length[4]'],
            'editorContact-country'     =>  ['label' => 'Country Code', 'rules' => 'required|alpha_numeric'],
            'editorContact-phoneNumber' =>  ['label' => 'Phone Number', 'rules' => 'required|numeric|min_length[6]'],
            'editorContact-email'       =>  ['label' => 'Email', 'rules' => 'permit_empty|valid_email']
        ];

        $messages   =   [
            'editorContact-nameTitle'   =>  ['alpha_numeric' => 'Invalid data sent'],
            'editorContact-country'     =>  ['alpha_numeric' => 'Invalid data sent']
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());
        $idContact          =   $this->request->getVar('editorContact-idContact');
        $idContact          =   $idContact != "" ? hashidDecode($idContact) : 0;
        $idNameTitle        =   $this->request->getVar('editorContact-nameTitle');
        $idNameTitle        =   hashidDecode($idNameTitle);
        $idCountry          =   $this->request->getVar('editorContact-country');
        $idCountry          =   hashidDecode($idCountry);
        $name               =   $this->request->getVar('editorContact-name');
        $phoneNumber        =   $this->request->getVar('editorContact-phoneNumber');
        $email              =   $this->request->getVar('editorContact-email');
        $dataPhoneNumber    =   $this->getDataPhoneNumber($idCountry, $phoneNumber);
        $phoneNumberBase    =   $dataPhoneNumber['phoneNumberBase'];
        $phoneNumber        =   $dataPhoneNumber['phoneNumber'];
        $messageResponse    =   $idChatList =   '';
        $dateTimeLastReply  =   0;
        $arrInsertUpdateData=   [
            'IDCOUNTRY'         =>  $idCountry,
            'IDNAMETITLE'       =>  $idNameTitle,
            'NAMEFULL'          =>  $name,
            'PHONENUMBER'       =>  $phoneNumber,
            'PHONENUMBERBASE'   =>  $phoneNumberBase,
            'EMAILS'            =>  $email,
            'DATETIMEINSERT'    =>  $this->currentDateTime
        ];

        $mainOperation  =   new MainOperation();
        if($idContact == 0){
            $procInsertData =   $mainOperation->insertDataTable('t_contact', $arrInsertUpdateData);

            if(!$procInsertData['status']) return switchMySQLErrorCode($procInsertData['errCode']);
            $idContact      =   $procInsertData['insertID'];
            $messageResponse=   'New contact data has been added';
        } else {
            try{
                $mainOperation->updateDataTable('t_contact', $arrInsertUpdateData, ['IDCONTACT' => $idContact]);
                $contactModel       =   new ContactModel();
                $detailContact      =   $contactModel->getDetailContact($idContact);
                $idChatList         =   $detailContact['IDCHATLIST'] != '' ? hashidEncode($detailContact['IDCHATLIST']) : '';
                $dateTimeLastReply  =   $detailContact['TIMESTAMPLASTREPLY'];
                $messageResponse    =   'Contact data has been updated';
            } catch (\Throwable $th) {
                return throwResponseNotAcceptable('Internal database script error');
            }
        }

        return throwResponseOK(
            $messageResponse,
            [
                'detailContact' =>  [
                    'IDCONTACT'         =>  hashidEncode($idContact),
                    'IDCHATLIST'        =>  $idChatList,
                    'DATETIMELASTREPLY' =>  $dateTimeLastReply,
                    'NAMEALPHASEPARATOR'=>  substr($name, 0, 1),
                    'NAMEFULL'          =>  $name,
                    'PHONENUMBER'       =>  $phoneNumber,
                    'EMAILS'            =>  $email
                ]
            ]
        );
    }
    
    private function getDataPhoneNumber($idCountry, $phoneNumber)
    {   
        $mainOperation      =   new MainOperation();
        $dataCountryCode    =   $mainOperation->getDataCountryCode($idCountry);
        $phoneNumberBase    =   $phoneNumber;

        if(count($dataCountryCode) > 0){
            $countryPhoneCode   =   $dataCountryCode[0]->COUNTRYPHONECODE;
            $phoneNumberBase    =   substr($phoneNumber, 0, strlen($countryPhoneCode)) == $countryPhoneCode ? substr($phoneNumber, strlen($countryPhoneCode)) * 1 : $phoneNumber * 1;
            $phoneNumber        =   $countryPhoneCode.$phoneNumberBase;
        }
		
		return [
            'phoneNumberBase'   =>  $phoneNumberBase,
            'phoneNumber'       =>  $phoneNumber
        ];
	}
}