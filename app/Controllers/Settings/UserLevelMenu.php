<?php

namespace App\Controllers\Settings;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\MainOperation;
use App\Models\Settings\UserLevelMenuModel;
use Google\Service\AlertCenter\User;

class UserLevelMenu extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $userData, $currentDateTime;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function getDataLevel()
    {
        $userLevelMenuModel =   new UserLevelMenuModel();
        $searchKeyword      =   $this->request->getVar('searchKeyword');
        $dataUserLevel      =	$userLevelMenuModel->getDataUserLevel($searchKeyword);

        if($dataUserLevel){
            $result =   encodeDatabaseObjectResultKey($dataUserLevel, 'IDUSERADMINLEVEL');
            return $this->setResponseFormat('json')
                        ->respond([
                            "dataLevelUser"    =>  $result
                        ]);
        } else {
            return throwResponseNotFound('No data found based on the applied filter');
        }
    }

    public function getMenuLevelAdmin()
    {
        helper(['form']);
        $rules  =   [
            'idUserLevel' =>    ['label' => 'Id user level', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idUserLevel'    => [
                'required'      =>  'Invalid data sent',
                'alpha_numeric' =>  'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $userLevelMenuModel =   new UserLevelMenuModel();
        $idUserLevel        =   $this->request->getVar('idUserLevel');
        $idUserLevel        =   hashidDecode($idUserLevel);
        $dataMenuLevel      =	$userLevelMenuModel->getMenuLevelAdmin($idUserLevel);

        if($dataMenuLevel){
            $dataMenuLevel  =   encodeDatabaseObjectResultKey($dataMenuLevel, 'IDMENUADMIN');
            $dataMenuLevel  =   encodeDatabaseObjectResultKey($dataMenuLevel, 'IDMENULEVELADMIN');
            return $this->setResponseFormat('json')
                        ->respond([
                            "dataMenuLevel"    =>  $dataMenuLevel
                        ]);
        } else {
            return throwResponseNotFound('No data found based on level user selected');
        }
    }

    public function addLevelAdmin()
    {
        helper(['form']);
        $rules      =   [
            'userLevelName' =>  ['label' => 'Level Name', 'rules' => 'required|regex_match[/^[a-zA-Z0-9~!#$%&*_\-\+=|:., ]+$/]|min_length[5]|max_length[50]'],
            'description'   =>  ['label' => 'Description', 'rules' => 'required|regex_match[/^[a-zA-Z0-9~!#$%&*_\-\+=|:., ]+$/]|max_length[255]']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $userLevelMenuModel =   new UserLevelMenuModel();
        $userLevelName      =   $this->request->getVar('userLevelName');
        $description        =   $this->request->getVar('description');
        $isLevelAdminExist  =	$userLevelMenuModel->isLevelAdminExist($userLevelName);

        if(!$isLevelAdminExist){
            $arrInsertData   =   [
                'LEVELNAME'     =>  $userLevelName,
                'DESCRIPTION'   =>  $description,
                'ISSUPERADMIN'  =>  0
            ];

            $mainOperation          =   new MainOperation();
            $procInsertLevelUser    =   $mainOperation->insertDataTable('m_useradminlevel', $arrInsertData);

            if(!$procInsertLevelUser['status']) return switchMySQLErrorCode($procInsertLevelUser['errCode']);
            return throwResponseOK(
                'New user level has been successfully added',
                ['idUserLevel'    =>  hashidEncode($procInsertLevelUser['insertID'])]
            );
        } else {
            return throwResponseForbidden('Admin user level with name `'.$userLevelName.'` already exists, please use another name.');
        }
    }

    public function saveLevelMenu()
    {
        helper(['form']);
        $rules      =   [
            'idUserLevel'   =>  ['label' => 'Id user level', 'rules' => 'required|alpha_numeric'],
            'userLevelName' =>  ['label' => 'Level Name', 'rules' => 'required|regex_match[/^[a-zA-Z0-9~!#$%&*_\-\+=|:., ]+$/]|min_length[5]|max_length[50]'],
            'description'   =>  ['label' => 'Description', 'rules' => 'required|regex_match[/^[a-zA-Z0-9~!#$%&*_\-\+=|:., ]+$/]|max_length[255]'],
            'userLevelMenu' =>  ['label' => 'User level menu', 'rules' => 'required|is_array'],
        ];

        $messages   =   [
            'idUserLevel'   => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ],
            'userLevelMenu' => [
                'required'  => 'Invalid data sent',
                'is_array'  => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $mainOperation      =   new MainOperation();
        $idUserLevel        =   $this->request->getVar('idUserLevel');
        $userLevelName      =   $this->request->getVar('userLevelName');
        $description        =   $this->request->getVar('description');
        $idUserLevel        =   hashidDecode($idUserLevel);
        $userLevelMenu      =   $this->request->getVar('userLevelMenu');
        $arrUpdateLevelUser =   [
            'LEVELNAME'     =>  $userLevelName,
            'DESCRIPTION'   =>  $description
        ];

        $mainOperation->updateDataTable('m_useradminlevel', $arrUpdateLevelUser, ['IDUSERADMINLEVEL' => $idUserLevel]);

        foreach($userLevelMenu as $keyUserLevelMenu) {
            $idMenuAdmin            =   isset($keyUserLevelMenu->idMenuAdmin) && $keyUserLevelMenu->idMenuAdmin != '' ? hashidDecode($keyUserLevelMenu->idMenuAdmin) : 0;
            $idMenuLevelAdmin       =   isset($keyUserLevelMenu->idMenuLevelAdmin) && $keyUserLevelMenu->idMenuLevelAdmin != '' ? hashidDecode($keyUserLevelMenu->idMenuLevelAdmin) : 0;
            $isMenuOpen             =   isset($keyUserLevelMenu->isMenuOpen) && $keyUserLevelMenu->isMenuOpen != '' ? $keyUserLevelMenu->isMenuOpen : 0;

            if($isMenuOpen){
                $arrInsertUpdateMenuLevel   =   [
                    'IDUSERADMINLEVEL'  =>  $idUserLevel,
                    'IDMENUADMIN'       =>  $idMenuAdmin
                ];

                for($i=1; $i <= 3; $i++){
                    $arrInsertUpdateMenuLevel['ALLOWPERMISSION'.$i] =   isset($keyUserLevelMenu->{"allowPermission".$i}) && $keyUserLevelMenu->{"allowPermission".$i} != '' ? $keyUserLevelMenu->{"allowPermission".$i} : 0;
                }

                if($idMenuLevelAdmin != 0) $mainOperation->updateDataTable('m_menuleveladmin', $arrInsertUpdateMenuLevel, ['IDMENULEVELADMIN' => $idMenuLevelAdmin]);
                else $mainOperation->insertDataTable('m_menuleveladmin', $arrInsertUpdateMenuLevel);
            } else {
                if($idMenuLevelAdmin != 0) $mainOperation->deleteDataTable('m_menuleveladmin', 'IDMENULEVELADMIN', $idMenuLevelAdmin);
            }
        }

        return throwResponseOK(
            'User level detail & menu access has been successfully updated',
            ['idUserLevel' => hashidEncode($idUserLevel)]
        );
    }
}