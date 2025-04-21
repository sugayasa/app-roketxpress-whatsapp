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
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
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
                        CONTENTFOOTER, CONTENTBUTTONS, PARAMETERSHEADER, PARAMETERSBODY");
        $this->from('t_chattemplate', true);
        if($templateType != 0) $this->where($columnConditionType, true);
        if($templateName != '') $this->where('TEMPLATENAME', $templateName);
        $this->limit(1);

        $result =   $this->first();

        if(is_null($result)) false;
        return $result;
    }

    public function insertUpdateChatTable($currentTimeStamp, $idContact, $idMessage, $messageTemplateGenerated, $idUserAdmin = 1) : void
    {
        $idChatList             =   $this->getIdChatList($idContact);
        $messageHeader          =   isset($messageTemplateGenerated['header']) ? $messageTemplateGenerated['header'] : '';
        $messageBody            =   isset($messageTemplateGenerated['body']) ? $messageTemplateGenerated['body'] : $messageTemplateGenerated;
        $messageFooter          =   isset($messageTemplateGenerated['footer']) ? $messageTemplateGenerated['footer'] : '';
        $isTemplateMessage      =   isset($messageTemplateGenerated['body']) ? 1 : 0;
        $arrInsertUpdateChatList=   [
            "IDCONTACT"             =>  $idContact,
            "TOTALUNREADMESSAGE"    =>  0,
            "LASTMESSAGE"           =>  $messageBody,
            "DATETIMELASTMESSAGE"   =>  $currentTimeStamp
        ];

        if($idChatList) {
            $arrInsertUpdateChatList['TOTALUNREADMESSAGE']  =   $this->getTotalUnreadMessageChat($idChatList);
            $this->updateDataTable('t_chatlist', $arrInsertUpdateChatList, ['IDCHATLIST' => $idChatList]);
        } else {
            $procInsertChatList =   $this->insertDataTable('t_chatlist', $arrInsertUpdateChatList);
            if($procInsertChatList['status']) $idChatList = $procInsertChatList['insertID'];
        }

        if($idChatList){
            $statusRead             =   $idUserAdmin == 0 ? 0 : 1;
            $isUpdateTableChatList  =   $idUserAdmin == 0 ? true : false;
            $arrInsertChatThread    =   [
                "IDCHATLIST"        =>  $idChatList,
                "IDUSERADMIN"       =>  $idUserAdmin,
                "IDMESSAGE"         =>  $idMessage,
                "CHATCONTENTHEADER" =>  $messageHeader,
                "CHATCONTENTBODY"   =>  $messageBody,
                "CHATCONTENTFOOTER" =>  $messageFooter,
                "DATETIMECHAT"      =>  $currentTimeStamp,
                "STATUSREAD"        =>  $statusRead,
                "ISTEMPLATE"        =>  $isTemplateMessage
            ];

            $this->insertDataTable('t_chatthread', $arrInsertChatThread);
            $this->updateChatListAndRTDBStats($idChatList, true, $isUpdateTableChatList);
        }
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

    public function getDetailChatListByPhoneNumber($phoneNumber)
    {	
        $this->select("A.IDCHATLIST, A.IDCONTACT");
        $this->from('t_chatlist A', true);
        $this->join('t_contact AS B', 'A.IDCONTACT = B.IDCONTACT', 'LEFT');
        $this->where('B.PHONENUMBER', $phoneNumber);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
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

    public function updateChatListAndRTDBStats($idChatList, $isNewMessage = false, $isUpdateTableChatList = false) : void
    {
        $firebaseRTDB       =   new FirebaseRTDB();
        $detailChatListStats=   $this->getDetailChatListStats($idChatList);

        if(is_array($detailChatListStats) && !empty($detailChatListStats)){
            $totalUnreadMessage     =   $detailChatListStats['TOTALUNREADMESSAGE'];
            $lastMessage            =   $detailChatListStats['LASTMESSAGE'];
            $dateTimeLastMessage    =   $detailChatListStats['DATETIMELASTMESSAGE'];
            $dateTimeLastReply      =   $detailChatListStats['DATETIMELASTREPLY'];
            $contactName            =   $detailChatListStats['NAMEFULL'];
            $contactInitial         =   $contactName[0];
            $senderName             =   $detailChatListStats['SENDERNAME'];
            $chatThreadPosition     =   $detailChatListStats['CHATTHREADPOSITION'];

            if($isUpdateTableChatList){
                $arrUpdateChatList      =   [
                    "TOTALUNREADMESSAGE"    =>  $totalUnreadMessage,
                    "LASTMESSAGE"           =>  $lastMessage,
                    "DATETIMELASTMESSAGE"   =>  $dateTimeLastMessage,
                    "DATETIMELASTREPLY"     =>  $dateTimeLastReply
                ];
                $this->updateDataTable('t_chatlist', $arrUpdateChatList, ['IDCHATLIST' => $idChatList]);
            }

            $idChatListEncoded      =   hashidEncode($idChatList, true);
            $timeStampRTDB          =   $dateTimeLastMessage > $dateTimeLastReply ? $dateTimeLastMessage : $dateTimeLastReply;
            $lastMessageTrim        =   strlen($lastMessage) > 30 ? substr($lastMessage, 0, 30)."..." : $lastMessage;
            $arrUpdateReferenceRTDB =   [
                'contactInitial'    =>  $contactInitial,
                'contactName'       =>  $contactName,
                'idChatList'        =>  $idChatListEncoded,
                'isNewMessage'      =>  $isNewMessage,
                'messageBodyTrim'   =>  $lastMessageTrim,
                'timestamp'         =>  $timeStampRTDB,
                'totalUnreadMessage'=>  $totalUnreadMessage,
                'messageDetail'     =>  [
                    'senderName'        =>  $senderName,
                    'chatThreadPosition'=>  $chatThreadPosition,
                    'arrayChatThread'   =>  [
                        'IDMESSAGE'         =>  $detailChatListStats['IDMESSAGE'],
                        'IDCHATTHREAD'      =>  hashidEncode($detailChatListStats['IDCHATTHREAD'], true),
                        'CHATCONTENTHEADER' =>  $detailChatListStats['CHATCONTENTHEADER'],
                        'CHATCONTENTBODY'   =>  $detailChatListStats['LASTMESSAGE'],
                        'CHATCONTENTFOOTER' =>  $detailChatListStats['CHATCONTENTFOOTER'],
                        'DATETIMESENT'      =>  null,
                        'DATETIMEDELIVERED' =>  null,
                        'DATETIMEREAD'      =>  null,
                        'ISTEMPLATE'        =>  $detailChatListStats['ISTEMPLATE']
                    ]
                ]
            ];

            $firebaseRTDB->updateRealtimeDatabaseValue('lastUpdateChat', $arrUpdateReferenceRTDB);
        }        
    }

    private function getDetailChatListStats($idChatList) : array
    {
        $this->select("SUM(IF(A.STATUSREAD = 0, 1, 0)) AS TOTALUNREADMESSAGE, AA.CHATCONTENTHEADER, AA.CHATCONTENTBODY AS LASTMESSAGE, AA. CHATCONTENTFOOTER,
                MAX(A.DATETIMECHAT) AS DATETIMELASTMESSAGE, MAX(IF(A.IDUSERADMIN = 0, A.DATETIMECHAT, NULL)) AS DATETIMELASTREPLY, C.NAMEFULL,
                IF(AA.IDUSERADMIN = 0, C.NAMEFULL, D.NAME) AS SENDERNAME, IF(AA.IDUSERADMIN = 0, 'L', 'R') AS CHATTHREADPOSITION, AA.IDMESSAGE, AA.IDCHATTHREAD,
                AA.ISTEMPLATE");
        $this->from('t_chatthread A', true);
        $this->join("(SELECT IDCHATLIST, IDMESSAGE, IDCHATTHREAD, IDUSERADMIN, CHATCONTENTHEADER, CHATCONTENTBODY, CHATCONTENTFOOTER, ISTEMPLATE
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
        if(is_null($row)) return [];
        return $row;
    }

    public function getCountryCodeByPhoneNumber($phoneNumber) : int
    {
        $arrDataCountryCode =	$this->getDataCountryCode();
		$phoneNumber	    =	preg_replace('/[^0-9]/', '', $phoneNumber);
		$whileProcess	    =	true;
		$idCountryReturn    =	0;
		
        foreach($arrDataCountryCode as $keyDataCountryCode){
            $idCountry		=	$keyDataCountryCode->IDCOUNTRY;
            $countryCode	=	$keyDataCountryCode->COUNTRYPHONECODE;
            $countryCodeLen	=	strlen($countryCode);
            
            if(substr($phoneNumber, 0, $countryCodeLen) == $countryCode){
                if($whileProcess == true){
                    $idCountryReturn=	$idCountry;
                    $whileProcess	=	false;
                    break;
                }
            }
            
            if(!$whileProcess) break;
        }
		
		return $idCountryReturn;
	}

    function getDataCountryCode() : array
    {
        $this->select("IDCOUNTRY, COUNTRYPHONECODE");
        $this->from('m_country', true);
        $this->orderBy('LENGTH(COUNTRYPHONECODE) DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return [];
        return $result;
    }
}