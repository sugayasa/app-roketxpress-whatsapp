<?php

namespace App\Models;
use CodeIgniter\Model;

class CronModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 't_chatcron';
    protected $primaryKey       = 'IDCHATCRON';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDCHATCRON', 'IDRESERVATION', 'TEMPLATECODE', 'STATUS', 'DATETIMESCHEDULE', 'DATETIMESENT'];

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

    public function getDataChatCron($dataLimit = 10)
    {	
        $this->select("A.IDCHATCRON, A.IDRESERVATION, A.IDCHATTEMPLATE, B.IDCONTACT, C.PHONENUMBER, D.TEMPLATECODE, D.TEMPLATELANGUAGECODE, D.PARAMETERSHEADER, D.PARAMETERSBODY");
        $this->from('t_chatcron A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.t_reservation AS B', 'A.IDRESERVATION = B.IDRESERVATION', 'LEFT');
        $this->join('t_contact AS C', 'B.IDCONTACT = C.IDCONTACT', 'LEFT');
        $this->join('t_chattemplate AS D', 'A.IDCHATTEMPLATE = D.IDCHATTEMPLATE', 'LEFT');
        $this->where("A.STATUS", 0);
        $this->where("A.DATETIMESCHEDULE <= ", date('Y-m-d H:i:s'));
        $this->limit($dataLimit);

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
    }
    
    public function getDetailReservation($idreservation)
    {	
        $this->select("B.SOURCENAME, A.BOOKINGCODE, CONCAT(IF(D.NAMETITLE IS NULL, '', CONCAT(D.NAMETITLE, ' ')), C.NAMEFULL) AS CUSTOMERNAME,
                    A.RESERVATIONTITLE, DATE_FORMAT(A.RESERVATIONDATESTART, '%d %b %Y') AS RESERVATIONDATESTART, DATE_FORMAT(A.RESERVATIONDATEEND, '%d %b %Y') AS RESERVATIONDATEEND,
                    LEFT(A.RESERVATIONTIMESTART, 5) AS RESERVATIONTIMESTART, LEFT(A.RESERVATIONTIMEEND, 5) AS RESERVATIONTIMEEND, A.DURATIONOFDAY, A.NUMBEROFADULT, A.NUMBEROFCHILD,
                    A.NUMBEROFINFANT, IF(A.PICKUPLOCATION IS NULL OR A.PICKUPLOCATION = '', '-', A.PICKUPLOCATION) AS PICKUPLOCATION, IF(A.REMARK IS NULL OR A.REMARK = '', 'None', A.REMARK) AS REMARK");
        $this->from(APP_MAIN_DATABASE_NAME.'.t_reservation A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.m_source AS B', 'A.IDSOURCE = B.IDSOURCE', 'LEFT');
        $this->join('t_contact AS C', 'A.IDCONTACT = C.IDCONTACT', 'LEFT');
        $this->join('m_nametitle AS D', 'C.IDNAMETITLE = D.IDNAMETITLE', 'LEFT');
        $this->where("A.IDRESERVATION", $idreservation);

        $result =   $this->get()->getRowObject();

        if(is_null($result)) return false;
        return $result;
    }
}