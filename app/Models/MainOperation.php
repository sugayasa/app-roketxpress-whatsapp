<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\FirebaseRTDB;

class MainOperation extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ci_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ip_address', 'timestamp', 'data'];

    public function execQueryWithLimit($queryString, $page, $dataPerPage)
    {
		$startid    =	($page * 1 - 1) * $dataPerPage;
        $query      =   $this->query($queryString." LIMIT ".$startid.", ".$dataPerPage);

        return $query->getResult();
    }

    public function generateResultPagination($result, $basequery, $keyfield, $page, $dataperpage)
    {
        $startid	=	($page * 1 - 1) * $dataperpage;
		$datastart	=	$startid + 1;
		$dataend	=	$datastart + $dataperpage - 1;
		$query      =   $this->query("SELECT IFNULL(COUNT(".$keyfield."), 0) AS TOTAL FROM (".$basequery.") AS A");
		
		$row		=	$query->getRow();
		$datatotal	=	$row->TOTAL;
		$pagetotal	=	ceil($datatotal/$dataperpage);
		$datastart	=	$pagetotal == 0 ? 0 : $startid + 1;
		$startnumber=	$pagetotal == 0 ? 0 : ($page-1) * $dataperpage + 1;
		$dataend	=	$dataend > $datatotal ? $datatotal : $dataend;
		
		return array("data"=>$result ,"dataStart"=>$datastart, "dataEnd"=>$dataend, "dataTotal"=>$datatotal, "pageTotal"=>$pagetotal, "startNumber"=>$startnumber);
    }

	public function generateEmptyResult()
    {
		return array("data"=>[], "datastart"=>0, "dataend"=>0, "datatotal"=>0, "pagetotal"=>0);
	}

    public function insertDataTable($tableName, $arrInsert)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);
            foreach($arrInsert as $field => $value){
                $table->set($field, $value);
            }
            $table->insert();

            $insertID       =   $db->insertID();
            $affectedRows   =   $db->affectedRows();

            if($insertID > 0 || $affectedRows > 0) return ["status"=>true, "errCode"=>false, "insertID"=>$insertID];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
        }
    }

    public function insertIgnoreDataTable($tableName, $arrInsert)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);
            foreach($arrInsert as $field => $value){
                $table->set($field, $value);
            }
            $queryString    = $table->getCompiledInsert();
            $queryString    = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $queryString);

            $db->query($queryString);
            $insertID       =   $db->insertID();
            $affectedRows   =   $db->affectedRows();

            if($insertID > 0 || $affectedRows > 0) return ["status"=>true, "errCode"=>false, "insertID"=>$insertID];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
        }
    }

    public function insertDataBatchTable($tableName, $arrInsert)
    {
        $db     =   \Config\Database::connect();
        try {
            $table          =   $db->table($tableName);
            $table->insertBatch($arrInsert);
            $affectedRows   =   $db->affectedRows();

            if($affectedRows > 0) return ["status"=>true, "errCode"=>false];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th->getMessage()];
        }
    }

    public function updateDataTable($tableName, $arrUpdate, $arrWhere)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);
            foreach($arrUpdate as $field => $value){
                $table->set($field, $value);
            }

            foreach($arrWhere as $field => $value){
                if(is_array($value)){
                    $table->whereIn($field, $value);
                } else {
                    $table->where($field, $value);
                }
            }
            $table->update();

            $affectedRows   =   $db->affectedRows();
            if($affectedRows > 0) return ["status"=>true, "errCode"=>false];
            return ["status"=>false, "errCode"=>1329, "queryString"=>$db->getLastQuery()];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "error"=>$error, "errCode"=>$errorCode, "errorMessages"=>$th, "queryString"=>$db->getLastQuery()];
        }
        return ["status"=>false, "errCode"=>false];
    }

    public function deleteDataTable($tableName, $arrWhere)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);

            foreach($arrWhere as $field => $value){
                if(is_array($value)){
                    $table->whereIn($field, $value);
                } else {
                    $table->where($field, $value);
                }
            }
            $table->delete();

            $affectedRows   =   $db->affectedRows();
            if($affectedRows > 0) return ["status"=>true, "affectedRows"=>$affectedRows];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
        }
    }

    public function getDataSystemSetting($idSystemSetting)
    {	
        $this->select("DATASETTING");
        $this->from('a_systemsettings', true);
        $this->where('IDSYSTEMSETTINGS', $idSystemSetting);
        $this->limit(1);

        $result =   $this->first();

        if(is_null($result)) return '[]';
        return $result['DATASETTING'];
    }

    public function getDataChatTemplate($templateType = 0, $templateName = '')
    {	
        $columnConditionType  =   'ISCRONGREETING';

        switch($templateType){
            case 1      :
            case "1"    :   $columnConditionType  =   'ISCRONGREETING'; break;
            case 2      :
            case "2"    :   $columnConditionType  =   'ISCRONRECONFIRMATION'; break;
            case 3      :
            case "3"    :   $columnConditionType  =   'ISCRONREVIEWREQUEST'; break;
            case 4      :
            case "4"    :   $columnConditionType  =   'ISQUESTION'; break;
            default     :   $columnConditionType  =   'ISCRONGREETING'; break;
        }

        $this->select("IDCHATTEMPLATE, IDONEMSGIO, TEMPLATECODE, TEMPLATENAME, TEMPLATELANGUAGECODE, CONTENTHEADER, CONTENTBODY,
                        CONTENTFOOTER, CONTENTBUTTONS, PARAMETERSHEADER, PARAMETERSBODY, ISCRONGREETING, ISCRONRECONFIRMATION,
                        ISCRONREVIEWREQUEST, ISQUESTION");
        $this->from('t_chattemplate', true);
        if($templateType != 0) $this->where($columnConditionType, true);
        if($templateName != '') $this->where('TEMPLATENAME', $templateName);
        $this->limit(1);

        $result =   $this->first();

        if(is_null($result)) false;
        return $result;
    }

    public function insertUpdateChatTable($currentTimeStamp, $idContact, $idMessage, $messageGenerated, $idUserAdmin = 1, $arrAdditionalThread = null) : int
    {
        $idChatList         =   $this->getIdChatList($idContact);
        $idChatThreadType   =   isset($arrAdditionalThread['idChatThreadType']) && is_numeric($arrAdditionalThread['idChatThreadType']) ? $arrAdditionalThread['idChatThreadType'] : 1;
        $forceUpdate        =   isset($arrAdditionalThread['forceUpdate']) ? $arrAdditionalThread['forceUpdate'] : false;
        $handleStatus       =   isset($arrAdditionalThread['handleStatus']) ? $arrAdditionalThread['handleStatus'] : -1;
        $messageHeader      =   isset($messageGenerated['header']) ? $messageGenerated['header'] : '';
        $messageBody        =   isset($messageGenerated['body']) ? $messageGenerated['body'] : $messageGenerated;
        $messageFooter      =   isset($messageGenerated['footer']) ? $messageGenerated['footer'] : '';
        $isTemplateMessage  =   isset($messageGenerated['body']) ? 1 : 0;

        switch($idChatThreadType){
            case 2  : $lastMessageChatList = '<i class="ri-file-image-line"></i> Image'; break;
            case 3  : $lastMessageChatList = '<i class="ri-article-line"></i> Document'; break;
            case 4  : $lastMessageChatList = '<i class="ri-mic-2-line"></i> Audio'; break;
            case 5  : $lastMessageChatList = '<i class="ri-film-line"></i> Video'; break;
            case 6  : $lastMessageChatList = '<i class="ri-map-pin-line"></i> Location'; break;
            default : $lastMessageChatList = $messageBody; break;
        }

        $arrInsertUpdateChatList=   [
            "IDCONTACT"             =>  $idContact,
            "TOTALUNREADMESSAGE"    =>  0,
            "LASTMESSAGE"           =>  $lastMessageChatList,
            "DATETIMELASTMESSAGE"   =>  $currentTimeStamp
        ];

        if($handleStatus != -1) $arrInsertUpdateChatList['HANDLESTATUS'] = $handleStatus;
        if($idChatList) {
            $arrInsertUpdateChatList['TOTALUNREADMESSAGE']  =   $this->getTotalUnreadMessageChat($idChatList);
            $this->updateDataTable('t_chatlist', $arrInsertUpdateChatList, ['IDCHATLIST' => $idChatList]);
        } else {
            $procInsertChatList =   $this->insertDataTable('t_chatlist', $arrInsertUpdateChatList);
            if($procInsertChatList['status']) $idChatList = $procInsertChatList['insertID'];
        }

        if($idChatList){
            $statusRead             =   $idUserAdmin == 0 ? 0 : 1;
            $arrInsertChatThread    =   [
                "IDCHATLIST"        =>  $idChatList,
                "IDUSERADMIN"       =>  $idUserAdmin,
                "IDCHATTHREADTYPE"  =>  $idChatThreadType,
                "IDMESSAGE"         =>  $idMessage,
                "CHATCONTENTHEADER" =>  $messageHeader,
                "CHATCONTENTBODY"   =>  $messageBody,
                "CHATCONTENTFOOTER" =>  $messageFooter,
                "DATETIMECHAT"      =>  $currentTimeStamp,
                "STATUSREAD"        =>  $statusRead,
                "ISTEMPLATE"        =>  $isTemplateMessage
            ];

            if(!is_null($arrAdditionalThread) && is_array($arrAdditionalThread)){
                if(isset($arrAdditionalThread['quotedMsgId'])) $arrInsertChatThread['IDMESSAGEQUOTED'] = $arrAdditionalThread['quotedMsgId'];
                if(isset($arrAdditionalThread['caption'])) $arrInsertChatThread['CHATCAPTION'] = $arrAdditionalThread['caption'];
                if(isset($arrAdditionalThread['isForwarded'])) $arrInsertChatThread['ISFORWARDED'] = $arrAdditionalThread['isForwarded'];
                if(isset($arrAdditionalThread['isBOT'])) $arrInsertChatThread['ISBOT'] = $arrAdditionalThread['isBOT'];
            }

            $detailChatThread   =   $this->getDetailThreadByMessageId($idMessage);
            if(!$detailChatThread) {
                $procInsertChatThread   =   $this->insertDataTable('t_chatthread', $arrInsertChatThread);
                if($procInsertChatThread['status']) {
                    $this->updateChatListAndRTDBStats($idChatList, true);
                    return $procInsertChatThread['insertID'];
                }
            } else {
                $idChatThread   =   $detailChatThread['IDCHATTHREAD'];
                if($forceUpdate) $this->updateDataTable('t_chatthread', $arrInsertChatThread, ['IDCHATTHREAD' => $idChatThread]);

                $this->updateChatListAndRTDBStats($idChatList, true);
                return $idChatThread;
            }
        }

        return 0;
    }

    public function getIdChatList($idContact)
    {	
        $this->select("IDCHATLIST");
        $this->from('t_chatlist', true);
        $this->where('IDCONTACT', $idContact);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row['IDCHATLIST'];
    }

    public function getDetailThreadByMessageId($idMessage)
    {	
        $this->select("IDCHATTHREAD");
        $this->from('t_chatthread', true);
        $this->where('IDMESSAGE', $idMessage);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
    }

    public function getDetailChatListByPhoneNumber($idCountry, $phoneNumberBase)
    {	
        $this->select("B.IDCHATLIST, A.IDCONTACT");
        $this->from('t_contact A', true);
        $this->join('t_chatlist AS B', 'A.IDCONTACT = B.IDCONTACT', 'LEFT');
        $this->where('A.IDCOUNTRY', $idCountry);
        $this->where('A.PHONENUMBERBASE', $phoneNumberBase);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
    }

    public function getActivePhoneNumber($idContact) : mixed
    {	
        $this->select("IF(A.PHONENUMBERZEROPREFIX = 1, CONCAT(B.COUNTRYPHONECODE, '0', A.PHONENUMBERBASE), CONCAT(B.COUNTRYPHONECODE, A.PHONENUMBERBASE)) AS PHONENUMBER");
        $this->from('t_contact A', true);
        $this->join('m_country AS B', 'A.IDCOUNTRY = B.IDCOUNTRY', 'LEFT');
        $this->where('A.IDCONTACT', $idContact);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();
        if(is_null($row)) return null;
        return $row['PHONENUMBER'];
    }

    public function getTotalUnreadMessageChat($idChatList)
    {	
        $this->select("COUNT(IDCHATTHREAD) AS TOTALUNREADMESSAGECHAT");
        $this->from('t_chatthread', true);
        $this->where('IDCHATLIST', $idChatList);
        $this->where('IDUSERADMIN', 0);
        $this->where('STATUSREAD', 0);
        $this->groupBy('IDCHATLIST');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return 0;
        return $row['TOTALUNREADMESSAGECHAT'];
    }

    public function insertLogFailedMessage($idChatTemplate, $idContact, $phoneNumber, $templateParameters, $errorCode, $errorMsg) : void
    {
        $arrInsertLogFailedMessage  =   [
            "IDCHATTEMPLATE"    =>  $idChatTemplate,
            "IDCONTACT"         =>  $idContact,
            "PHONENUMBER"       =>  $phoneNumber,
            "PARAMETERDATA"     =>  json_encode($templateParameters),
            "ERRORCODE"         =>  $errorCode,
            "ERRORMESSAGE"      =>  $errorMsg,
            "LOGDATETIME"       =>  date('Y-m-d H:i:s')
        ];

        $this->insertDataTable('log_failedmessage', $arrInsertLogFailedMessage);
    }

    public function updateChatListAndRTDBStats($idChatList, $isNewMessage = false) : void
    {
        $firebaseRTDB       =   new FirebaseRTDB();
        $detailChatListStats=   $this->getDetailChatListStats($idChatList);

        if(is_array($detailChatListStats) && !empty($detailChatListStats)){
            $idMessageQuoted        =   $detailChatListStats['IDMESSAGEQUOTED'];
            $totalUnreadMessage     =   $detailChatListStats['TOTALUNREADMESSAGE'];
            $lastMessage            =   $detailChatListStats['LASTMESSAGE'];
            $dateTimeLastMessage    =   $detailChatListStats['DATETIMELASTMESSAGE'];
            $dateTimeLastReply      =   $detailChatListStats['DATETIMELASTREPLY'];
            $contactName            =   $detailChatListStats['NAMEFULL'];
            $contactInitial         =   $contactName[0];
            $senderFirstName        =   $detailChatListStats['SENDERFIRSTNAME'];
            $senderName             =   $detailChatListStats['SENDERNAME'];
            $chatThreadPosition     =   $detailChatListStats['CHATTHREADPOSITION'];
            $idUserAdmin            =   $detailChatListStats['IDUSERADMIN'];
            $idChatThreadType       =   $detailChatListStats['IDCHATTHREADTYPE'];
            $arrReservationTypeStr  =   $detailChatListStats['ARRIDRESERVATIONTYPE'];
            $handleStatus           =   $detailChatListStats['HANDLESTATUS'];
            $handleForce            =   $detailChatListStats['HANDLEFORCE'];
            $lastMessageChatList    =   $lastMessage;
            $messageQuoted          =   $messageQuotedSender    =  '';

            switch($idChatThreadType){
                case 2  : $lastMessageChatList = '<i class="ri-file-image-line"></i> Image'; break;
                case 3  : $lastMessageChatList = '<i class="ri-article-line"></i> Document'; break;
                case 4  : $lastMessageChatList = '<i class="ri-mic-2-line"></i> Audio'; break;
                case 5  : $lastMessageChatList = '<i class="ri-film-line"></i> Video'; break;
                case 6  : $lastMessageChatList = '<i class="ri-map-pin-line"></i> Location'; break;
                default : $lastMessageChatList = $lastMessage; break;
            }

            $arrUpdateChatList      =   [
                "LASTSENDERFIRSTNAME"   =>  $senderFirstName,
                "TOTALUNREADMESSAGE"    =>  $totalUnreadMessage,
                "LASTMESSAGE"           =>  $lastMessageChatList,
                "DATETIMELASTMESSAGE"   =>  $dateTimeLastMessage,
                "DATETIMELASTREPLY"     =>  $dateTimeLastReply
            ];
            $this->updateDataTable('t_chatlist', $arrUpdateChatList, ['IDCHATLIST' => $idChatList]);

            $idChatListEncoded      =   hashidEncode($idChatList, true);
            $idUserAdminEncoded     =   hashidEncode($idUserAdmin, true);
            $timeStampRTDB          =   $dateTimeLastMessage > $dateTimeLastReply ? $dateTimeLastMessage : $dateTimeLastReply;

            if(substr($lastMessageChatList, 0, 2)  != '<i'){
                $lastMessageTrim    =   strlen($lastMessage) > 30 ? substr($lastMessage, 0, 30)."..." : $lastMessage;
                $lastMessageTrim    =   mb_convert_encoding($lastMessageTrim, 'UTF-8', 'UTF-8');
            } else {
                $lastMessageTrim    =   $lastMessageChatList;
            }

            if($arrReservationTypeStr != ''){
                $arrReservationType =   json_decode($arrReservationTypeStr, true);
                $arrReservationType =   array_filter(array_map(function($idReservationType) {
                    return hashidEncode($idReservationType, true);
                }, $arrReservationType));
            }

            if($idMessageQuoted != ""){
                $messageQuotedDetail=   $this->getMessageQuotedDetail($idMessageQuoted);
                $messageQuotedDB    =   $messageQuotedDetail['MESSAGEQUOTED'] ?? '';

                if($messageQuotedDB != ""){
                    $messageQuotedArr   =   explode("\n", $messageQuotedDB);
                    $messageQuoted      =   strlen($messageQuotedArr[0]) > 50 ? substr($messageQuotedArr[0], 0, 50)."..." : $messageQuotedArr[0];
                }
                $messageQuotedSender    =   $messageQuotedDetail['MESSAGEQUOTEDSENDER'] ?? '-';
            }

            $arrUpdateReferenceRTDB =   [
                'contactInitial'    =>  $contactInitial,
                'contactName'       =>  $contactName,
                'idChatList'        =>  $idChatListEncoded,
                'idUserAdmin'       =>  $idUserAdminEncoded,
                'arrReservationType'=>  $arrReservationType,
                'isNewMessage'      =>  $isNewMessage,
                'handleStatus'      =>  $handleStatus,
                'handleForce'       =>  $handleForce,
                'messageBodyTrim'   =>  $lastMessageTrim,
                'timestamp'         =>  $timeStampRTDB,
                'dateTimeLastReply' =>  is_null($dateTimeLastReply) ? 0 : $dateTimeLastReply,
                'totalUnreadMessage'=>  $totalUnreadMessage,
                'messageDetail'     =>  [
                    'senderName'        =>  $senderName,
                    'senderFirstName'   =>  $senderFirstName,
                    'chatThreadPosition'=>  $chatThreadPosition,
                    'arrayChatThread'   =>  [
                        'IDMESSAGE'             =>  $detailChatListStats['IDMESSAGE'],
                        'IDCHATTHREAD'          =>  hashidEncode($detailChatListStats['IDCHATTHREAD'], true),
                        'IDCHATTHREADTYPE'      =>  $detailChatListStats['IDCHATTHREADTYPE'],
                        'IDMESSAGEQUOTED'       =>  $idMessageQuoted,
                        'MESSAGEQUOTEDSENDER'   =>  $messageQuotedSender,
                        'MESSAGEQUOTED'         =>  $messageQuoted,
                        'CHATCONTENTHEADER'     =>  $detailChatListStats['CHATCONTENTHEADER'],
                        'CHATCONTENTBODY'       =>  $detailChatListStats['LASTMESSAGE'],
                        'CHATCONTENTFOOTER'     =>  $detailChatListStats['CHATCONTENTFOOTER'],
                        'CHATCAPTION'           =>  $detailChatListStats['CHATCAPTION'],
                        'DATETIMESENT'          =>  null,
                        'DATETIMEDELIVERED'     =>  null,
                        'DATETIMEREAD'          =>  null,
                        'ISFORWARDED'           =>  $detailChatListStats['ISFORWARDED'],
                        'ISTEMPLATE'            =>  $detailChatListStats['ISTEMPLATE'],
                        'ISBOT'                 =>  $detailChatListStats['ISBOT']
                    ]
                ]
            ];

            $firebaseRTDB->updateRealtimeDatabaseMultiValue(
                [
                    'lastUpdateChat'        =>  $arrUpdateReferenceRTDB,
                    'unreadChatNumber'      =>  $this->getTotalUnreadChat(),
                    'forceHandleNumber'     =>  $this->getTotalForceHandle()
                ]
            );
        }
    }

    private function getDetailChatListStats($idChatList) : array
    {
        $this->select("SUM(IF(A.STATUSREAD = 0, 1, 0)) AS TOTALUNREADMESSAGE, AA.CHATCONTENTHEADER, AA.CHATCONTENTBODY AS LASTMESSAGE, AA. CHATCONTENTFOOTER,
                AA.CHATCAPTION, MAX(A.DATETIMECHAT) AS DATETIMELASTMESSAGE, MAX(IF(A.IDUSERADMIN = 0, A.DATETIMECHAT, NULL)) AS DATETIMELASTREPLY, C.NAMEFULL,
                IF(AA.IDUSERADMIN = 0, C.NAMEFULL, IF(AA.ISBOT = 0, D.NAME, CONCAT(D.NAME, ' (Bot)'))) AS SENDERNAME, IF(AA.IDUSERADMIN = 0, 'L', 'R') AS CHATTHREADPOSITION,
                IF(AA.IDUSERADMIN = 0, SUBSTRING_INDEX(C.NAMEFULL, ' ', 1), SUBSTRING_INDEX(D.NAME, ' ', 1)) AS SENDERFIRSTNAME, AA.IDMESSAGE, AA.IDCHATTHREAD,
                AA.IDCHATTHREADTYPE, AA.IDMESSAGEQUOTED, AA.ISFORWARDED, AA.ISTEMPLATE, AA.ISBOT, AA.IDUSERADMIN, C.ARRIDRESERVATIONTYPE, B.HANDLESTATUS, B.HANDLEFORCE");
        $this->from('t_chatthread A', true);
        $this->join("(SELECT IDCHATLIST, IDMESSAGE, IDCHATTHREAD, IDCHATTHREADTYPE, IDMESSAGEQUOTED, IDUSERADMIN, CHATCONTENTHEADER, CHATCONTENTBODY, CHATCONTENTFOOTER,
                        CHATCAPTION, ISFORWARDED, ISTEMPLATE, ISBOT
                      FROM t_chatthread 
                      WHERE IDCHATLIST = '".$idChatList."' 
                      ORDER BY DATETIMECHAT DESC 
                      LIMIT 1) AS AA", 'A.IDCHATLIST = AA.IDCHATLIST', 'LEFT');
        $this->join('t_chatlist AS B', 'A.IDCHATLIST = B.IDCHATLIST', 'LEFT');
        $this->join('t_contact AS C', 'B.IDCONTACT = C.IDCONTACT', 'LEFT');
        $this->join('m_useradmin AS D', 'AA.IDUSERADMIN = D.IDUSERADMIN', 'LEFT');
        $this->where('A.IDCHATLIST', $idChatList);
        $this->groupBy('A.IDCHATLIST');
        $this->orderBy('A.DATETIMECHAT DESC');

        $row    =   $this->get()->getRowArray();
        log_message('debug', $this->getLastQuery());
        if(is_null($row)) return [];
        return $row;
    }

    public function getTotalUnreadChat() : int
    {
        $this->select("COUNT(IDCHATLIST) AS TOTALUNREADCHAT");
        $this->from('t_chatlist', true);
        $this->where('TOTALUNREADMESSAGE > ', 0);

        $row    =   $this->get()->getRowArray();
        if(is_null($row)) return 0;
		return $row['TOTALUNREADCHAT'];
	}

    public function getTotalForceHandle() : int
    {
        $this->select("COUNT(IDCHATLIST) AS TOTALFORCEHANDLE");
        $this->from('t_chatlist', true);
        $this->where('HANDLEFORCE = ', 1);

        $row    =   $this->get()->getRowArray();
        if(is_null($row)) return 0;
		return $row['TOTALFORCEHANDLE'];
	}
    
    public function getDataCountryCodeByPhoneNumber($phoneNumber) : array
    {
        $arrDataCountryCode =	$this->getDataCountryCode();
		$phoneNumber	    =	preg_replace('/[^0-9]/', '', $phoneNumber);
		$whileProcess	    =	true;
		$idCountryReturn    =	0;
		$countryPhoneCode   =	'';
		
        foreach($arrDataCountryCode as $keyDataCountryCode){
            $idCountry		=	$keyDataCountryCode->IDCOUNTRY;
            $countryCode	=	$keyDataCountryCode->COUNTRYPHONECODE;
            $countryCodeLen	=	strlen($countryCode);
            
            if(substr($phoneNumber, 0, $countryCodeLen) == $countryCode){
                if($whileProcess == true){
                    $idCountryReturn    =	$idCountry;
                    $countryPhoneCode   =	$countryCode;
                    $whileProcess	    =	false;
                    break;
                }
            }
            
            if(!$whileProcess) break;
        }
		
		return [
            'idCountry'         =>  $idCountryReturn,
            'countryPhoneCode'  =>  $countryPhoneCode
        ];
	}

    public function getDataCountryCode($idCountry = false) : array
    {
        $this->select("IDCOUNTRY, COUNTRYPHONECODE");
        $this->from('m_country', true);
        if($idCountry) $this->where('IDCOUNTRY', $idCountry);
        $this->orderBy('LENGTH(COUNTRYPHONECODE) DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return [];
        return $result;
    }

    public function getCurrencyExchangeByDate($incomeCurrency, $date) : int
    {
        $this->select("EXCHANGEVALUE");
        $this->from(APP_MAIN_DATABASE_NAME.'.t_currencyexchange', true);
        $this->where('CURRENCY', $incomeCurrency);
        $this->where('DATESTART < ', $date);

        $row    =   $this->get()->getRowArray();
        if(is_null($row)) return 1;
		return $row['EXCHANGEVALUE'];
	}

    public function getDetailReservation($idReservation) : array
    {
        $this->select("A.RESERVATIONDATESTART, A.RESERVATIONDATEEND, COUNT(B.IDRESERVATIONDETAILS) AS TOTALDETAILS, A.IDAREA, C.UPSELLINGTYPE");
        $this->from(APP_MAIN_DATABASE_NAME.'.t_reservation A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.t_reservationdetails AS B', 'A.IDRESERVATION = B.IDRESERVATION AND B.STATUS = 1', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.m_source AS C', 'A.IDSOURCE = C.IDSOURCE', 'LEFT');
        $this->where('A.IDRESERVATION', $idReservation);
        $this->groupBy('A.IDRESERVATION');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();
        if(is_null($row)) return [
            'RESERVATIONDATESTART'  => '',
            'RESERVATIONDATEEND'    => '',
            'TOTALDETAILS'          => 0,
            'IDAREA'                => 0,
            'UPSELLINGTYPE'         => 0
        ];
		return $row;
	}

    public function getMessageQuotedDetail($idMessageQuoted)
    {	
        $this->select("A.CHATCONTENTBODY AS MESSAGEQUOTED, IF(A.IDUSERADMIN = 0, D.NAMEFULL, B.NAME) AS MESSAGEQUOTEDSENDER");
        $this->from('t_chatthread A', true);
        $this->join('m_useradmin AS B', 'A.IDUSERADMIN = B.IDUSERADMIN', 'LEFT');
        $this->join('t_chatlist AS C', 'A.IDCHATLIST = C.IDCHATLIST', 'LEFT');
        $this->join('t_contact AS D', 'C.IDCONTACT = D.IDCONTACT', 'LEFT');
        $this->where('A.IDMESSAGE', $idMessageQuoted);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();
        if(is_null($row)) return ["MESSAGEQUOTED" => '', "MESSAGEQUOTEDSENDER" => ''];
        return $row;
    }
}