<?php

namespace App\Models\Settings;
use CodeIgniter\Model;

class UserLevelMenuModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'm_useradminlevel';
    protected $primaryKey       = 'IDUSERADMINLEVEL';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDUSERADMINLEVEL', 'LEVELNAME', 'DESCRIPTION'];

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
    
    public function isLevelAdminExist($userLevelName)
    {
        $this->select("IDUSERADMINLEVEL");
        $this->from('m_useradminlevel', true);
        $this->where('LEVELNAME', $userLevelName);
        $this->limit(1);

        $result =   $this->get()->getRowArray();
        if(is_null($result)) return false;
        return true;
    }
    
    public function getDataUserLevel($searchKeyword)
    {	
        $this->select("IDUSERADMINLEVEL, LEVELNAME, DESCRIPTION");
        $this->from('m_useradminlevel', true);
        $this->where('ISSUPERADMIN', 0);
        if(isset($searchKeyword) && !is_null($searchKeyword)){
            $this->groupStart();
            $this->like('LEVELNAME', $searchKeyword, 'both')
            ->orLike('DESCRIPTION', $searchKeyword, 'both');
            $this->groupEnd();
        }
        $this->orderBy('LEVELNAME');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getMenuLevelAdmin($idUserLevel)
    {	
        $subQuery   =   $this->db->table('m_menuleveladmin B')
                        ->select('IDMENULEVELADMIN, IDMENUADMIN, ALLOWPERMISSION1, ALLOWPERMISSION2, ALLOWPERMISSION3')
                        ->where('IDUSERADMINLEVEL', $idUserLevel)
                        ->getCompiledSelect();
                        
         $builder   =   $this->db->table('m_menuadmin A')
                        ->select("A.IDMENUADMIN, IFNULL(B.IDMENULEVELADMIN, 0) AS IDMENULEVELADMIN, A.MENUNAME, A.DESCRIPTION, IF(B.IDMENULEVELADMIN IS NULL, 0, 1) AS ISMENUOPEN,
                                A.PERMISSION1, A.PERMISSION2, A.PERMISSION3, IFNULL(B.ALLOWPERMISSION1, 0) AS ALLOWPERMISSION1, IFNULL(B.ALLOWPERMISSION2, 0) AS ALLOWPERMISSION2,
                                IFNULL(B.ALLOWPERMISSION3, 0) AS ALLOWPERMISSION3")
                        ->join("($subQuery) B", 'A.IDMENUADMIN = B.IDMENUADMIN', 'LEFT')
                        ->orderBy("ORDERGROUP, ORDERMENU, MENUNAME");
        $query      =   $builder->get();
        $result     =   $query->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}
}