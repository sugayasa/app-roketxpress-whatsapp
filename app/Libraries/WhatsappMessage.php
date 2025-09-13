<?php
namespace App\Libraries;
use App\Models\MainOperation;
use App\Models\CronModel;

class WhatsappMessage
{
    public function saveWebhookMessage($message, $dateTimeNow)
    {
        $mainOperation  =   new MainOperation();
        $cronModel      =   new CronModel();
        $logger         =   \Config\Services::logger();
        
        try {
            if(is_array($message)) $message   =   (object) $message;

            $author             =   $message->author ?? null;
            $chatId             =   $message->chatId ?? null;
            $messageId          =   $message->id ?? null;
            $messageType        =   $message->type ?? null;
            $messageBody        =   $message->body ?? null;
            $senderName         =   $message->senderName ?? null;
            $fromMe             =   $message->fromMe ?? null;
            $caption            =   $message->caption ?? null;
            $quotedMsgId        =   $message->quotedMsgId ?? null;
            $isForwarded        =   $message->isForwarded ?? null;
            $timeStamp          =   $message->time ?? null;
            $phoneNumber        =   getPhoneNumberFromWhatsappAuthor($chatId);
            $phoneNumber        =	preg_replace('/[^0-9]/', '', $phoneNumber);
            $phoneNumberBase    =   $this->getDataPhoneNumberBase($phoneNumber);
            $idCountry          =   $phoneNumberBase['idCountry'] ?? 0;
            $phoneNumberBase    =   $phoneNumberBase['phoneNumberBase'] ?? $phoneNumber;
            $isZeroPrefixNumber =   $phoneNumberBase['isZeroPrefixNumber'] ?? false;
            $detailChatList     =   $mainOperation->getDetailChatListByPhoneNumber($idCountry, $phoneNumberBase);
            $idContact          =   $detailChatList['IDCONTACT'] ?? null;
            $idChatThreadType   =   1;
            
            switch($messageType){
                case 'image'    :   $idChatThreadType   =   2; break;
                case 'document' :   $idChatThreadType   =   3; break;
                case 'audio'    :   $idChatThreadType   =   4; break;
                case 'video'    :   $idChatThreadType   =   5; break;
                case 'location' :   $idChatThreadType   =   6; break;
                default         :   break;
            }

            $arrAdditionalThread =   [
                'idChatThreadType'  =>  $idChatThreadType,
                'quotedMsgId'       =>  $quotedMsgId,
                'caption'           =>  $caption,
                'isForwarded'       =>  $isForwarded
            ];

            if(!$fromMe){
                if(!$detailChatList || is_null($idContact)){
                    $arrInsertContact   =   [
                        'IDCOUNTRY'             =>  $idCountry,
                        'IDNAMETITLE'           =>  0,
                        'NAMEFULL'              =>  $senderName,
                        'PHONENUMBER'           =>  $phoneNumber,
                        'PHONENUMBERBASE'       =>  $phoneNumberBase,
                        'PHONENUMBERZEROPREFIX' =>  $isZeroPrefixNumber,
                        'EMAILS'                =>  '',
                        'ISVALIDWHATSAPP'       =>  1,
                        'DATETIMEINSERT'        =>  $dateTimeNow
                    ];
                    $procInsertContact   =   $mainOperation->insertDataTable('t_contact', $arrInsertContact);
                    if($procInsertContact['status']) $idContact = $procInsertContact['insertID'];
                }

                if(!is_null($quotedMsgId) && $quotedMsgId != ''){
                    $detailChatThreadQuoted =   $cronModel->getDetailChatThreadQuoted($quotedMsgId);
                    $isQuotedTemplate       =   $detailChatThreadQuoted['ISTEMPLATE'];

                    if($isQuotedTemplate){
                        $detailChatCron =   $cronModel->getDetailChatCron($quotedMsgId);
                        $idReservationRC=   $detailChatCron['IDRESERVATIONRECONFIRMATION'];

                        if($idReservationRC > 0){
                            $statusReconfirmation       =   $messageBody == 'Confirm Reservation' ? 2 : 3;
                            $arrUpdateReconfirmation    =   [
                                'DATETIMERESPONSE'  =>  $dateTimeNow,
                                'STATUS'            =>  $statusReconfirmation
                            ];
                            $mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservationreconfirmation', $arrUpdateReconfirmation, ['IDRESERVATIONRECONFIRMATION' => $idReservationRC]);
                        }
                    }
                }

                if(!is_null($idContact)) $mainOperation->insertUpdateChatTable($timeStamp, $idContact, $messageId, $messageBody, 0, $arrAdditionalThread);
            } else {
                $isMessageIdExist =   $cronModel->isMessageIdExist($messageId);
                if(!$isMessageIdExist) {
                    $arrAdditionalThread['isBOT']   =   true;
                    $mainOperation->insertUpdateChatTable($timeStamp, $idContact, $messageId, $messageBody, 1, $arrAdditionalThread);
                }
            }

            if(!is_null($idContact)) $mainOperation->updateDataTable('t_contact', ['PHONENUMBERZEROPREFIX' => $isZeroPrefixNumber], ['IDCONTACT' => $idContact]);

            return true;
        } catch (\Exception $e) {
            $logger->error('Error saving webhook message: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getDataPhoneNumberBase($phoneNumber)
    {   
        $mainOperation          =   new MainOperation();
        $dataCountryPhoneNumber =   $mainOperation->getDataCountryCodeByPhoneNumber($phoneNumber);
        $idCountry              =   $dataCountryPhoneNumber['idCountry'] ?? 0;
        $countryPhoneCode	    =   $dataCountryPhoneNumber['countryPhoneCode'] ?? '';
		$phoneNumberBase	    =	substr($phoneNumber, strlen($countryPhoneCode)) * 1;
        $isZeroPrefixNumber     =   substr($phoneNumberBase, 0, 1) == '0' ? true : false;
		
		return [
            'idCountry'         =>  $idCountry,
            'phoneNumberBase'   =>  $phoneNumberBase,
            'isZeroPrefixNumber'=>  $isZeroPrefixNumber
        ];
	}
}