<?php

namespace App\Models;
use CodeIgniter\Model;

class ContactModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 't_contact';
    protected $primaryKey       = 'IDCONTACT';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDCONTACT', 'IDCOUNTRY', 'IDNAMETITLE', 'NAMEFULL', 'PHONENUMBER', 'EMAILS', 'DATETIMEINSERT'];

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

    public function getDataContactRecentlyAdd($page, $searchKeyword, $recentlyAdded = false)
    {	
        $dataPerPage=   100;
        $pageOffset =   ($page - 1) * $dataPerPage;
        $this->select("A.IDCONTACT, A.NAMEFULL, LEFT(A.NAMEFULL, 1) AS NAMEALPHASEPARATOR, A.PHONENUMBER, A.EMAILS,
                    IFNULL(B.DATETIMELASTREPLY, 0) AS DATETIMELASTREPLY, B.IDCHATLIST");
        $this->from('t_contact AS A', true);
        $this->join('t_chatlist AS B', 'A.IDCONTACT = B.IDCONTACT', 'LEFT');
        if(isset($searchKeyword) && !is_null($searchKeyword) && $searchKeyword != '') {
            $this->groupStart();
            $this->like('A.NAMEFULL', $searchKeyword, 'both')
            ->orLike('A.PHONENUMBER', $searchKeyword, 'both')
            ->orLike('A.EMAILS', $searchKeyword, 'both');
            $this->groupEnd();
        }
        $this->groupBy('A.IDCONTACT');
        if($recentlyAdded) $this->orderBy('A.DATETIMEINSERT DESC, A.IDCONTACT');
        $this->limit($dataPerPage, $pageOffset);

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getDataContactReservation($dateReservation, $searchKeyword)
    {	
        $this->select("A.IDCONTACT, A.NAMEFULL, LEFT(A.NAMEFULL, 1) AS NAMEALPHASEPARATOR, A.PHONENUMBER, A.EMAILS,
                    IFNULL(C.DATETIMELASTREPLY, 0) AS DATETIMELASTREPLY, C.IDCHATLIST");
        $this->from('t_contact A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.t_reservation AS B', 'A.IDCONTACT = B.IDCONTACT', 'LEFT');
        $this->join('t_chatlist AS C', 'A.IDCONTACT = C.IDCONTACT', 'LEFT');
        $this->where("'".$dateReservation."' BETWEEN B.RESERVATIONDATESTART AND B.RESERVATIONDATEEND");
        if(isset($searchKeyword) && !is_null($searchKeyword) && $searchKeyword != '') {
            $this->groupStart();
            $this->like('A.NAMEFULL', $searchKeyword, 'both')
            ->orLike('A.PHONENUMBER', $searchKeyword, 'both')
            ->orLike('A.EMAILS', $searchKeyword, 'both')
            ->orLike('B.RESERVATIONTITLE', $searchKeyword, 'both')
            ->orLike('B.CUSTOMERNAME', $searchKeyword, 'both')
            ->orLike('B.HOTELNAME', $searchKeyword, 'both')
            ->orLike('B.PICKUPLOCATION', $searchKeyword, 'both')
            ->orLike('B.BOOKINGCODE', $searchKeyword, 'both');
            $this->groupEnd();
        }
        $this->groupBy('A.IDCONTACT');
        $this->orderBy('A.NAMEFULL ASC');
        $this->limit(999);

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getDetailContact($idContact)
    {	
        $this->select(
            "A.NAMEFULL, A.PHONENUMBER, A.PHONENUMBERBASE, IFNULL(B.COUNTRYNAME, '-') AS COUNTRYNAME, IFNULL(C.CONTINENTNAME, '-') AS CONTINENTNAME,
            IF(A.EMAILS = '' OR A.EMAILS IS NULL, '-', A.EMAILS) AS EMAILS, IFNULL(F.DATETIMELASTREPLY, '') AS DATETIMELASTREPLY, '' AS DATETIMEINTERVALINFO,
            COUNT(DISTINCT(D.IDRESERVATION)) AS TOTALRESERVATION,
            CONCAT(
                '[',
                    GROUP_CONCAT(
                        JSON_OBJECT(
                            'sourceName', E.SOURCENAME,
                            'badgeClass', E.BADGECLASS
                        )
                    ),
                ']'
            ) AS ARRAYSOURCE, A.ISVALIDWHATSAPP, F.DATETIMELASTREPLY AS TIMESTAMPLASTREPLY, F.IDCHATLIST, A.IDCOUNTRY, A.IDNAMETITLE"
        );
        $this->from('t_contact A', true);
        $this->join('m_country AS B', 'A.IDCOUNTRY = B.IDCOUNTRY', 'LEFT');
        $this->join('m_continent AS C', 'B.IDCONTINENT = C.IDCONTINENT', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.t_reservation AS D', 'A.IDCONTACT = D.IDCONTACT', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.m_source AS E', 'D.IDSOURCE = E.IDSOURCE', 'LEFT');
        $this->join('t_chatlist AS F', 'A.IDCONTACT = F.IDCONTACT', 'LEFT');
        $this->where('A.IDCONTACT', $idContact);
        $this->groupBy('A.IDCONTACT');
        $this->limit(1);

        $result =   $this->get()->getRowArray();

        if(!is_null($result)) return $result;
        return [
            'NAMEFULL'              =>  '-',
            'PHONENUMBER'           =>  '-',
            'PHONENUMBERBASE'       =>  '',
            'COUNTRYNAME'           =>  '-',
            'CONTINENTNAME'         =>  '-',
            'EMAILS'                =>  '-',
            'DATETIMELASTREPLY'     =>  '',
            'DATETIMEINTERVALINFO'  =>  '',
            'TOTALRESERVATION'      =>  0,
            'ARRAYSOURCE'           =>  '[]',
            'ISVALIDWHATSAPP'       =>  -1,
            'TIMESTAMPLASTREPLY'    =>  0,
            'IDCHATLIST'            =>  '',
            'IDCOUNTRY'             =>  '',
            'IDNAMETITLE'           =>  ''
        ];
    }

    public function getListDetailReservation($idContact)
    {	
        $this->select("B.SOURCENAME, A.RESERVATIONTITLE, A.DURATIONOFDAY, DATE_FORMAT(A.RESERVATIONDATESTART, '%a, %d %b %Y') AS RESERVATIONDATESTARTSTR,
                    DATE_FORMAT(A.RESERVATIONDATEEND, '%a, %d %b %Y') AS RESERVATIONDATEENDSTR, LEFT(A.RESERVATIONTIMESTART, 5) AS RESERVATIONTIMESTARTSTR,
                    LEFT(A.RESERVATIONTIMEEND, 5) AS RESERVATIONTIMEEND, A.HOTELNAME, IFNULL(A.PICKUPLOCATION, '-') AS PICKUPLOCATION,
                    IFNULL(A.DROPOFFLOCATION, '-') AS DROPOFFLOCATION, A.NUMBEROFADULT, A.NUMBEROFCHILD, A.NUMBEROFINFANT, A.BOOKINGCODE, A.REMARK, A.TOURPLAN,
                    IF(A.IDAREA = -1, 'Without Transfer', IFNULL(CONCAT(C.AREANAME, ' (', C.AREATAGS, ')'), '-')) AS AREANAME, A.RESERVATIONDATESTART,
                    A.SPECIALREQUEST, '' AS ALLOWASKQUESTION, '[]' AS LISTTEMPLATEMESSAGE, A.STATUS, A.REFUNDTYPE, A.IDRESERVATION");
        $this->from(APP_MAIN_DATABASE_NAME.'.t_reservation A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.m_source AS B', 'A.IDSOURCE = B.IDSOURCE', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.m_area AS C', 'A.IDAREA = C.IDAREA', 'LEFT');
        $this->where("A.IDCONTACT", $idContact);
        $this->orderBy('A.RESERVATIONDATESTART DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }

    public function getListTemplateMessage($idReservation)
    {
        $this->select("A.IDCHATTEMPLATE, A.IDONEMSGIO, A.TEMPLATECODE, A.TEMPLATENAME, A.TEMPLATELANGUAGECODE, A.CONTENTHEADER, A.CONTENTBODY, A.CONTENTFOOTER, A.CONTENTBUTTONS,
                    A.PARAMETERSHEADER, A.PARAMETERSBODY, IFNULL(B.STATUS, -2) AS STATUS, IFNULL(B.DATETIMESCHEDULE, '-') AS DATETIMESCHEDULE, IFNULL(B.DATETIMESENT, '-') AS DATETIMESENT,
                    A.ISCRONGREETING, A.ISCRONRECONFIRMATION, A.ISCRONREVIEWREQUEST, A.ISQUESTION");
        $this->from("t_chattemplate A", true);
        $this->join("(SELECT IDCHATTEMPLATE, STATUS, IF(DATETIMESCHEDULE = '0000-00-00 00:00:00', '-', DATE_FORMAT(DATETIMESCHEDULE, '%d %b %Y %H:%i')) AS DATETIMESCHEDULE,
                    IF(DATETIMESENT = '0000-00-00 00:00:00', '-', DATE_FORMAT(DATETIMESENT, '%d %b %Y %H:%i')) AS DATETIMESENT
                    FROM t_chatcron WHERE IDRESERVATION = ".$idReservation.") AS B",
                    "A.IDCHATTEMPLATE = B.IDCHATTEMPLATE", "LEFT");
        $this->where("ISCRONGREETING", true);
        $this->orWhere("ISCRONRECONFIRMATION", true);
        $this->orWhere("ISCRONREVIEWREQUEST", true);
        $this->orderBy("ISCRONGREETING DESC, ISCRONRECONFIRMATION DESC, ISCRONREVIEWREQUEST DESC");

        $result = $this->get()->getResultObject();

        if (is_null($result)) return [];
        return $result;
    }
}
