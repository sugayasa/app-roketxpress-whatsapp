<?php

namespace App\Models;

use CodeIgniter\Model;
use PHPUnit\Framework\Constraint\IsNull;

class AccessModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'm_useradmin';
    protected $primaryKey       = 'IDUSERADMIN';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDUSERADMINLEVEL', 'NAME', 'EMAIL', 'USERNAME', 'PASSWORD', 'HARDWAREID', 'REDIRECTTOKEN', 'DATETIMELOGIN', 'DATETIMEACTIVITY', 'DATETIMEEXPIRED', 'STATUS'];

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

    public function checkHardwareIDUserAdmin($idUserAdmin, $hardwareID)
    {
        $this->select('IDUSERADMIN');
        $this->from('m_useradmin', true);
        $this->where('IDUSERADMIN', $idUserAdmin);
        $this->where('HARDWAREID', $hardwareID);

        if(is_null($this->get()->getRowArray())) return false;
        return true;
    }

    public function getUserAdminDetail($idUserAdmin)
    {
        $this->select('A.HARDWAREID, A.IDUSERADMINLEVEL, A.NAME, A.USERNAME, A.EMAIL, B.LEVELNAME');
        $this->from('m_useradmin AS A', true);
        $this->join('m_useradminlevel AS B', 'A.IDUSERADMINLEVEL = B.IDUSERADMINLEVEL', 'LEFT');
        $this->where('A.IDUSERADMIN', $idUserAdmin);

        return $this->get()->getRowArray();
    }

    public function getUserAdminMenu($idUserAdminLevel)
    {
        $this->select('B.GROUPNAME, B.MENUNAME, B.MENUALIAS, B.URL, B.ICON');
        $this->from('m_menuleveladmin AS A', true);
        $this->join('m_menuadmin AS B', 'A.IDMENUADMIN = B.IDMENUADMIN', 'LEFT');
        $this->where('A.IDUSERADMINLEVEL', $idUserAdminLevel);
        $this->orderBy('B.ORDERGROUP, B.ORDERMENU');

        return $this->get()->getResultObject();
    }

    public function getUserAdminGroupMenu($idUserAdminLevel)
    {
        $this->select('B.GROUPNAME');
        $this->from('m_menuleveladmin AS A', true);
        $this->join('m_menuadmin AS B', 'A.IDMENUADMIN = B.IDMENUADMIN', 'LEFT');
        $this->where('A.IDUSERADMINLEVEL', $idUserAdminLevel);
        $this->groupBy('B.GROUPNAME');
        $this->having('COUNT(B.IDMENUADMIN) > ', 1);
        $this->orderBy('B.ORDERGROUP');

        return $this->get()->getResultObject();
    }

    public function getDataUserAdminLevel()
    {
        $this->select('IDUSERADMINLEVEL AS ID, LEVELNAME AS VALUE');
        $this->from('m_useradminlevel', true);
        $this->orderBy('LEVELNAME');

        return $this->get()->getResultObject();
    }

    public function getDataUserAdminLevelMenu()
    {
        $this->select('A.IDUSERADMINLEVEL AS ID, C.MENUNAME AS VALUE');
        $this->from('m_menuleveladmin AS A', true);
        $this->join('m_useradminlevel AS B', 'A.IDUSERADMINLEVEL = B.IDUSERADMINLEVEL', 'LEFT');
        $this->join('m_menuadmin AS C', 'A.IDMENUADMIN = C.IDMENUADMIN', 'LEFT');
        $this->orderBy('A.IDUSERADMINLEVEL, C.ORDERGROUP, C.ORDERMENU');

        return $this->get()->getResultObject();
    }

    public function setLastActivityUserAdmin($idUserAdmin, $datetimeActivity)
    {
        $this->set('DATETIMEACTIVITY', $datetimeActivity);
        $this->where('IDUSERADMIN', $idUserAdmin);
        $this->update();
    }
}
