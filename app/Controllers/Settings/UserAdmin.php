<?php

namespace App\Controllers\Settings;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\MainOperation;
use App\Models\Settings\UserAdminModel;

class UserAdmin extends ResourceController
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

    public function getDataUserAdmin()
    {
        $userAdminModel         =   new UserAdminModel();
        $idLevelUserAdmin       =   $this->request->getVar('idLevelUserAdmin');
        $idLevelUserAdmin       =   isset($idLevelUserAdmin) && $idLevelUserAdmin != '' ? hashidDecode($idLevelUserAdmin) : $idLevelUserAdmin;
        $searchKeyword          =   $this->request->getVar('searchKeyword');
        $dataUserAdmin          =	$userAdminModel->getDataUserAdmin($idLevelUserAdmin, $searchKeyword);
        $dataUserAdminUnlinked  =	$userAdminModel->getDataUserAdminUnlinked();
        $dataMenu               =	$userAdminModel->getDataMenu();
        $dataLevelMenu          =	$userAdminModel->getDataLevelMenu();

        if($dataUserAdmin){
            $dataUserAdmin          =   encodeDatabaseObjectResultKey($dataUserAdmin, ['IDUSERADMIN', 'IDUSERADMINLEVEL', 'IDUSERADMININTERNAL']);
            $dataMenu               =   encodeDatabaseObjectResultKey($dataMenu, 'IDMENUADMIN');
            $dataUserAdminUnlinked  =   encodeDatabaseObjectResultKey($dataUserAdminUnlinked, 'IDUSERADMIN');
            $dataLevelMenu          =   encodeDatabaseObjectResultKey($dataLevelMenu, ['IDUSERADMINLEVEL', 'IDMENUADMIN']);
            return $this->setResponseFormat('json')
                        ->respond([
                            "dataUserAdmin"         =>  $dataUserAdmin,
                            "dataUserAdminUnlinked" =>  $dataUserAdminUnlinked,
                            "dataMenu"              =>  $dataMenu,
                            "dataLevelMenu"         =>  $dataLevelMenu
                        ]);
        } else {
            return throwResponseNotFound('No data found based on the applied filter');
        }
    }

    public function saveUserAdmin()
    {
        helper(['form']);
        $idUserAdmin    =   $this->request->getVar('idUserAdmin');
        $idUserAdmin    =   $idUserAdmin != "" ? hashidDecode($idUserAdmin) : 0;

        return $idUserAdmin == 0 ? $this->insertDataUserAdmin() : $this->updateDataUserAdmin($idUserAdmin);
    }

    private function insertDataUserAdmin()
    {
        $rules          =   [
            'idLevelUserAdmin'  =>  ['label' => 'User Admin Level', 'rules' => 'required|alpha_numeric'],
            'name'              =>  ['label' => 'Name', 'rules' => 'required|alpha_numeric_space'],
            'email'             =>  ['label' => 'Email', 'rules' => 'required|valid_email|is_unique[m_useradmin.EMAIL]'],
            'username'          =>  ['label' => 'username', 'rules' => 'required|alpha_numeric|min_length[5]|is_unique[m_useradmin.USERNAME]'],
            'newPassword'       =>  ['label' => 'New Password', 'rules' => 'required|alpha_numeric|min_length[6]'],
            'repeatPassword'    =>  ['label' => 'Repeat Password', 'rules' => 'required|alpha_numeric|min_length[6]']
        ];

        $messages   =   [
            'email'     =>  ['is_unique' => 'This email address is already registered, please enter another email address'],
            'username'  =>  ['is_unique' => 'This username is already taken, please choose different username']
        ];
        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idLevelUserAdmin   =   $this->request->getVar('idLevelUserAdmin');
        $idLevelUserAdmin   =   hashidDecode($idLevelUserAdmin);
        $idUserAdminInternal=   $this->request->getVar('idUserAdminInternal');
        $idUserAdminInternal=   $idUserAdminInternal != "" ? hashidDecode($idUserAdminInternal) : null;
        $name               =   $this->request->getVar('name');
        $email              =   $this->request->getVar('email');
        $username           =   $this->request->getVar('username');
        $newPassword        =   $this->request->getVar('newPassword');
        $repeatPassword     =   $this->request->getVar('repeatPassword');

        if($newPassword != $repeatPassword) return throwResponseNotAcceptable("The repetition of the password you entered is not match");

        $arrInsertData      =   [
            'IDUSERADMININTERNAL'   =>  $idUserAdminInternal,
            'IDUSERADMINLEVEL'      =>  $idLevelUserAdmin,
            'NAME'                  =>  $name,
            'EMAIL'                 =>  $email,
            'USERNAME'              =>  $username,
            'PASSWORD'              =>  password_hash($newPassword, PASSWORD_DEFAULT),
            'STATUS'                =>  1
        ];

        $mainOperation  =   new MainOperation();
        $procInsertData =   $mainOperation->insertDataTable('m_useradmin', $arrInsertData);

        if(!$procInsertData['status']) return switchMySQLErrorCode($procInsertData['errCode']);
        return throwResponseOK(
            'New user admin data has been added',
            ['idUserAdmin'  =>  hashidEncode($procInsertData['insertID'])]
        );
    }

    private function updateDataUserAdmin($idUserAdmin)
    {
        helper(['form']);
        $rules          =   [
            'idUserAdmin'       =>  ['label' => 'ID User Admin', 'rules' => 'required|alpha_numeric'],
            'idLevelUserAdmin'  =>  ['label' => 'User Admin Level', 'rules' => 'required|alpha_numeric'],
            'name'              =>  ['label' => 'Name', 'rules' => 'required|alpha_numeric_space'],
            'email'             =>  ['label' => 'Email', 'rules' => 'required|valid_email|is_unique[m_useradmin.EMAIL, IDUSERADMIN, '.$idUserAdmin.']'],
            'username'          =>  ['label' => 'username', 'rules' => 'required|alpha_numeric|min_length[5]|is_unique[m_useradmin.USERNAME, IDUSERADMIN, '.$idUserAdmin.']']
        ];

        $messages   =   [
            'idUserAdmin'   => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ],
            'email'     =>  ['is_unique' => 'This email address is already registered, please enter another email address'],
            'username'  =>  ['is_unique' => 'This username is already taken, please choose different username']
        ];
        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idLevelUserAdmin   =   $this->request->getVar('idLevelUserAdmin');
        $idLevelUserAdmin   =   hashidDecode($idLevelUserAdmin);
        $idUserAdminInternal=   $this->request->getVar('idUserAdminInternal');
        $idUserAdminInternal=   $idUserAdminInternal != "" ? hashidDecode($idUserAdminInternal) : null;
        $name               =   $this->request->getVar('name');
        $email              =   $this->request->getVar('email');
        $username           =   $this->request->getVar('username');
        $currentPassword    =   $this->request->getVar('currentPassword');
        $newPassword        =   $this->request->getVar('newPassword');
        $repeatPassword     =   $this->request->getVar('repeatPassword');

        $arrUpdateUserAdmin =   [
            'IDUSERADMININTERNAL'   =>  $idUserAdminInternal,
            'IDUSERADMINLEVEL'      =>  $idLevelUserAdmin,
            'NAME'                  =>  $name,
            'EMAIL'                 =>  $email,
            'USERNAME'              =>  $username
        ];

        if($currentPassword != "" || $newPassword != "" || $repeatPassword != ""){
			if($currentPassword == "") return throwResponseNotAcceptable("Please enter your old password (your current password)");
            if($newPassword == "") return throwResponseNotAcceptable("Please enter a new password");
            if($repeatPassword == "") return throwResponseNotAcceptable("Please enter a new password repeat");
            if($newPassword != $repeatPassword) return throwResponseNotAcceptable("The repetition of the password you entered is not match");

            $userAdminModel =   new UserAdminModel();
            $dataUserAdmin  =   $userAdminModel->where("IDUSERADMIN", $idUserAdmin)->first();
            if(!$dataUserAdmin) return $this->failNotFound('User admin data was not found, please try again later');
            $passwordVerify =   password_verify($currentPassword, $dataUserAdmin['PASSWORD']);
            if(!$passwordVerify) return $this->fail('The old password you entered is incorrect');
			
			$arrUpdateUserAdmin['PASSWORD'] =   password_hash($newPassword, PASSWORD_DEFAULT);
        }
        $mainOperation  =   new MainOperation();
        $procUpdateData =   $mainOperation->updateDataTable('m_useradmin', $arrUpdateUserAdmin, ['IDUSERADMIN' => $idUserAdmin]);

        if(!$procUpdateData['status']) return switchMySQLErrorCode($procUpdateData['errCode']);
        return throwResponseOK(
            'User admin data has been updated',
            ['idUserAdmin'  =>  hashidEncode($idUserAdmin)]
        );
    }

    public function updateStatusUserAdmin()
    {
        helper(['form']);
        $rules      =   [
            'idUserAdmin'   =>  ['label' => 'Id User Admin', 'rules' => 'required|alpha_numeric'],
            'status'        =>  ['label' => 'status', 'rules' => 'required|in_list[1, -1]']
        ];

        $messages   =   [
            'idUserAdmin' => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];
        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $userAdminModel =   new UserAdminModel();
        $idUserAdmin    =   $this->request->getVar('idUserAdmin');
        $idUserAdmin    =   hashidDecode($idUserAdmin);
        $status         =   $this->request->getVar('status') * -1;
        $statusStr      =   $status == 1 ? 'activevated' : 'deactivevated';

        try{
            $userAdminModel->update($idUserAdmin, ['STATUS' => $status]);
        } catch (\Throwable $th) {
            return throwResponseNotAcceptable('Internal database script error');
        }

        return throwResponseOK('User admin data has been '.$statusStr);
    }
}