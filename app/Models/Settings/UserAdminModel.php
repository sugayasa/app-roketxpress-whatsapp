<?php

namespace App\Models\Settings;
use CodeIgniter\Model;

class UserAdminModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'm_useradmin';
    protected $primaryKey       = 'IDUSERADMIN';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDUSERADMINLEVEL', 'NAME', 'EMAIL', 'USERNAME', 'PASSWORD', 'STATUS'];

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
    
    public function getDataUserAdmin($idLevelUserAdmin, $searchKeyword)
    {	
        $this->select("A.IDUSERADMIN, A.IDUSERADMININTERNAL, A.IDUSERADMINLEVEL, B.LEVELNAME, A.NAME, A.EMAIL, A.USERNAME, IF(C.NAME IS NULL, '-', CONCAT(C.NAME, ' - ', D.LEVELNAME)) AS LINKEDACCOUNTNAME, A.STATUS,
                    IF(A.DATETIMELOGIN IS NULL OR A.DATETIMELOGIN = '0000-00-00 00:00:00', 'Not Available', DATE_FORMAT(A.DATETIMELOGIN, '%d %b %Y %H:%i')) AS DATETIMELOGIN,
                    IF(A.DATETIMEACTIVITY IS NULL OR A.DATETIMEACTIVITY = '0000-00-00 00:00:00', 'Not Available', DATE_FORMAT(A.DATETIMEACTIVITY, '%d %b %Y %H:%i')) AS DATETIMEACTIVITY");
        $this->from('m_useradmin AS A', true);
        $this->join('m_useradminlevel AS B', 'A.IDUSERADMINLEVEL = B.IDUSERADMINLEVEL', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.m_useradmin AS C', 'A.IDUSERADMININTERNAL = C.IDUSERADMIN', 'LEFT');
        $this->join(APP_MAIN_DATABASE_NAME.'.m_userlevel AS D', 'C.LEVEL = D.IDUSERLEVEL', 'LEFT');
        $this->where('A.ISPERMANENTUSER', 0);
        if(isset($idLevelUserAdmin) && $idLevelUserAdmin != 0 && $idLevelUserAdmin != '') $this->where('A.IDUSERADMINLEVEL', $idLevelUserAdmin);
        if(isset($searchKeyword) && !is_null($searchKeyword)){
            $this->groupStart();
            $this->like('B.LEVELNAME', $searchKeyword, 'both')
            ->orLike('A.NAME', $searchKeyword, 'both')
            ->orLike('A.EMAIL', $searchKeyword, 'both')
            ->orLike('A.USERNAME', $searchKeyword, 'both');
            $this->groupEnd();
        }
        $this->orderBy('B.LEVELNAME, A.NAME');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDataUserAdminUnlinked()
    {	
        $this->select("A.IDUSERADMIN, A.NAME, B.LEVELNAME");
        $this->from(APP_MAIN_DATABASE_NAME.'.m_useradmin AS A', true);
        $this->join(APP_MAIN_DATABASE_NAME.'.m_userlevel AS B', 'A.LEVEL = B.IDUSERLEVEL', 'LEFT');
        $this->join('m_useradmin AS C', 'A.IDUSERADMIN = C.IDUSERADMININTERNAL', 'LEFT');
        $this->where('A.STATUS', 1);
        $this->where('C.IDUSERADMIN', null);
        $this->orderBy('B.LEVELNAME, A.NAME');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return [];
        return $result;
	}

    public function getDataMenu()
    {	
        $this->select("IDMENUADMIN, MENUNAME");
        $this->from('m_menuadmin', true);
        $this->orderBy('ORDERGROUP, MENUNAME');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return [];
        return $result;
	}

    public function getDataLevelMenu()
    {	
        $this->select("IDUSERADMINLEVEL, IDMENUADMIN");
        $this->from('m_menuleveladmin', true);
        $this->orderBy('IDUSERADMINLEVEL');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return [];
        return $result;
	}
}