<?php

namespace App\Models;
use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 't_chatlist';
    protected $primaryKey       = 'IDCONTACT';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDCHATLIST', 'IDCONTACT', 'TOTALUNREADMESSAGE', 'LASTMESSAGE', 'DATETIMELASTMESSAGE'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getDataChatList($page, $dataPerPage = 25, $searchKeyword, $idContact = null)
    {	
        $pageOffset     =   ($page - 1) * $dataPerPage;
        $this->select("A.IDCHATLIST, LEFT(B.NAMEFULL, 1) AS NAMEALPHASEPARATOR, B.NAMEFULL, A.TOTALUNREADMESSAGE, A.LASTMESSAGE, A.DATETIMELASTMESSAGE,
                '' AS DATETIMELASTMESSAGESTR, IFNULL(A.DATETIMELASTREPLY, 0) AS DATETIMELASTREPLY");
        $this->from('t_chatlist A', true);
        $this->join('t_contact AS B', 'A.IDCONTACT = B.IDCONTACT', 'LEFT');
        if(isset($searchKeyword) && !is_null($searchKeyword) && $searchKeyword != '' && ($idContact == null || $idContact == '')) {
            $this->groupStart();
            $this->like('B.NAMEFULL', $searchKeyword, 'both')
            ->orLike('B.PHONENUMBER', $searchKeyword, 'both')
            ->orLike('B.EMAILS', $searchKeyword, 'both')
            ->orLike('A.LASTMESSAGE', $searchKeyword, 'both');
            $this->groupEnd();
        }
        if(isset($idContact) && !is_null($idContact) && $idContact != '') {
            $this->where('A.IDCONTACT = ', $idContact);
        }
        $this->orderBy('A.DATETIMELASTMESSAGE DESC');
        $this->limit($dataPerPage, $pageOffset);

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getDetailContactChat($idChatList)
    {	
        $this->select("LEFT(B.NAMEFULL, 1) AS NAMEALPHASEPARATOR, B.NAMEFULL, B.PHONENUMBER, C.COUNTRYNAME, D.CONTINENTNAME,
                    IF(B.EMAILS = '' OR B.EMAILS IS NULL, '-', B.EMAILS) AS EMAILS, IFNULL(A.DATETIMELASTREPLY, 0) AS DATETIMELASTREPLY,
                    A.IDCONTACT");
        $this->from('t_chatlist A', true);
        $this->join('t_contact AS B', 'A.IDCONTACT = B.IDCONTACT', 'LEFT');
        $this->join('m_country AS C', 'B.IDCOUNTRY = C.IDCOUNTRY', 'LEFT');
        $this->join('m_continent AS D', 'C.IDCONTINENT = D.IDCONTINENT', 'LEFT');
        $this->where('A.IDCHATLIST', $idChatList);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
    }

    public function getListChatThread($idChatList, $userTimeZoneOffset, $page, $dataPerPage = 25)
    {	
        $pageOffset =   ($page - 1) * $dataPerPage;
        $this->select("A.IDCHATTHREAD, A.IDMESSAGE, IF(A.IDUSERADMIN = 0, LEFT(D.NAMEFULL, 1), LEFT(B.NAME, 1)) AS INITIALNAME, A.CHATCONTENTHEADER, A.CHATCONTENTBODY,
                    A.CHATCONTENTFOOTER, A.DATETIMECHAT, '' AS CHATTIME, '' AS DAYTITLE, A.STATUSREAD, A.DATETIMESENT, A.DATETIMEDELIVERED, A.DATETIMEREAD,
                    IF(A.IDUSERADMIN = 0, D.NAMEFULL, B.NAME) AS USERNAMECHAT, IF(A.IDUSERADMIN = 0, 'L', 'R') AS CHATTHREADPOSITION, A.ISTEMPLATE,
                    IFNULL(CONCAT('[', GROUP_CONCAT(E.IDUSERADMIN), ']'), '[]') AS ARRIDUSERADMINREAD");
        $this->from('t_chatthread A', true);
        $this->join('m_useradmin AS B', 'A.IDUSERADMIN = B.IDUSERADMIN', 'LEFT');
        $this->join('t_chatlist AS C', 'A.IDCHATLIST = C.IDCHATLIST', 'LEFT');
        $this->join('t_contact AS D', 'C.IDCONTACT = D.IDCONTACT', 'LEFT');
        $this->join('t_chatdetailread AS E', 'A.IDCHATTHREAD = E.IDCHATTHREAD', 'LEFT');
        $this->where('A.IDCHATLIST', $idChatList);
        $this->groupBy('A.IDCHATTHREAD');
        $this->orderBy('A.DATETIMECHAT DESC, A.IDUSERADMIN ASC');
        $this->limit($dataPerPage, $pageOffset);

        $result     =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getListActiveReservation($idContact)
    {	
        $dateNow    =   date('Y-m-d');
        $this->select("B.SOURCENAME, A.RESERVATIONTITLE, A.DURATIONOFDAY, DATE_FORMAT(A.RESERVATIONDATESTART, '%a, %d %b %Y') AS RESERVATIONDATESTARTSTR,
                    DATE_FORMAT(A.RESERVATIONDATEEND, '%a, %d %b %Y') AS RESERVATIONDATEENDSTR, LEFT(A.RESERVATIONTIMESTART, 5) AS RESERVATIONTIMESTARTSTR,
                    LEFT(A.RESERVATIONTIMEEND, 5) AS RESERVATIONTIMEEND, IF(A.HOTELNAME IS NULL OR A.HOTELNAME = '', '-', A.HOTELNAME) AS HOTELNAME,
                    IF(A.PICKUPLOCATION IS NULL OR A.PICKUPLOCATION = '', '-', A.PICKUPLOCATION) AS PICKUPLOCATION,
                    IF(A.DROPOFFLOCATION IS NULL OR A.DROPOFFLOCATION = '', '-', A.DROPOFFLOCATION) AS DROPOFFLOCATION,
                    A.NUMBEROFADULT, A.NUMBEROFCHILD, A.NUMBEROFINFANT, A.BOOKINGCODE, A.REMARK, A.TOURPLAN,
                    IF(A.IDAREA = -1, 'Without Transfer', IFNULL(CONCAT(C.AREANAME, ' (', C.AREATAGS, ')'), '-')) AS AREANAME, A.SPECIALREQUEST");
        $this->from(APP_MAIN_DATABASE_NAME.'.t_reservation A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.m_source AS B', 'A.IDSOURCE = B.IDSOURCE', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.m_area AS C', 'A.IDAREA = C.IDAREA', 'LEFT');
        $this->where("A.IDCONTACT", $idContact);
        $this->groupStart();
        $this->where('A.RESERVATIONDATESTART >= ', $dateNow)
        ->orWhere('A.RESERVATIONDATEEND', $dateNow);
        $this->groupEnd();
        $this->orderBy('A.RESERVATIONDATESTART DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getDataThreadACK($idChatThread)
    {	
        $this->select("B.NAME, A.DATETIMEREAD");
        $this->from('t_chatdetailread A', true);
        $this->join('m_useradmin AS B', 'A.IDUSERADMIN = B.IDUSERADMIN', 'LEFT');
        $this->where('A.IDCHATTHREAD', $idChatThread);
        $this->orderBy('A.DATETIMEREAD');

        $result     =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getDataUnreadChatThread($idChatList){
        $this->select("IDCHATTHREAD");
        $this->from('t_chatthread', true);
        $this->where('STATUSREAD', 0);
        $this->orderBy('IDCHATTHREAD');

        $result     =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }
}
