<?php
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\AccessModel;

class Index extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function response404()
    {
        return $this->failNotFound('[E-AUTH-404] Not Found');
    }

    public function main()
    {
        return view('main');
    }

    public function loginPage()
    {
        return view('login');
    }

    public function mainPage()
    {
        helper(['form']);

        $hardwareID     =   strtoupper($this->request->getVar('hardwareID'));
        $lastPageAlias  =   strtoupper($this->request->getVar('lastPageAlias'));
        $header         =   $this->request->getServer('HTTP_AUTHORIZATION');
        $explodeHeader  =   $header != "" ? explode(' ', $header) : [];
        $token          =   is_array($explodeHeader) && isset($explodeHeader[1]) && $explodeHeader[1] != "" ? $explodeHeader[1] : "";

        if(isset($token) && $token != ""){
            try {
                $dataDecode         =   decodeJWTToken($token);
                $idUserAdmin        =   intval($dataDecode->idUserAdmin);
                $idUserAdminLevel   =   intval($dataDecode->idUserAdminLevel);
                $hardwareIDToken    =   $dataDecode->hardwareID;

                if($idUserAdmin != 0){
                    if(isset($idUserAdminLevel) && $idUserAdminLevel != "" && $idUserAdminLevel != 0){
                        $accessModel    =   new AccessModel();
                        $userAdminDataDB=   $accessModel->getUserAdminDetail($idUserAdmin);

                        if(!$userAdminDataDB || is_null($userAdminDataDB)) return $this->failUnauthorized('[E-AUTH-001.1.0] Invalid token - Not registered');

                        $hardwareIDDB       =   $userAdminDataDB['HARDWAREID'];
                        $idUserAdminLevel   =   $userAdminDataDB['IDUSERADMINLEVEL'];

                        if($hardwareID == $hardwareIDDB && $hardwareID == $hardwareIDToken){
                            $userAdminData  =   array(
                                "name"      =>   $userAdminDataDB['NAME'],
                                "email"     =>   $userAdminDataDB['EMAIL'],
                                "levelName" =>   $userAdminDataDB['LEVELNAME']
                            );

                            try {
                                $listMenuDB         =   $accessModel->getUserAdminMenu($idUserAdminLevel);
                                $menuElement	    =	$this->menuBuilder($listMenuDB, $lastPageAlias);
                                $firebaseScript     =   view('firebase', [], ['debug' => false]);
                                $htmlRes            =   view(
                                                            'mainPage',
                                                            array(
                                                                "userAdminData"         => $userAdminData,
                                                                "menuElement"           => $menuElement,
                                                                "allowNotifList"        => [],
                                                                "firebaseScript"        => $firebaseScript
                                                            ),
                                                            ['debug' => false]
                                                        );
                                return $this->setResponseFormat('json')
                                ->respond([
                                    'htmlRes'   =>  $htmlRes
                                ]);
                            } catch (\Throwable $th) {
                                var_dump($th); die();
                                return $this->failUnauthorized('[E-AUTH-001.1.1] Internal error. Failed to respond');
                            }
                        } else {
                            return $this->failUnauthorized('[E-AUTH-001.1.2] Invalid token - Hardware ID');
                        }
                    } else {
                        return $this->failUnauthorized('[E-AUTH-001.1.3] Invalid token - Level');
                    }
                } else {
                    return $this->failUnauthorized('[E-AUTH-001.1.4] Invalid token - User ID');
                }
            } catch (\Throwable $th) {
                return $this->failUnauthorized('[E-AUTH-001.2.0] Invalid token');
            }
        } else {
            return $this->failUnauthorized('[E-AUTH-001.2.0] Invalid token');
        }
    }

    public function menuBuilder($listMenuDB, $lastPageAlias)
    {
        if($listMenuDB == "" || !is_array($listMenuDB) || empty($listMenuDB)){
			return "";
		} else {			
			$menuElement	=	"";
				
			foreach($listMenuDB as $indexMenu => $keyMenu){
                $active			=	$lastPageAlias != '' && $lastPageAlias == $keyMenu->MENUALIAS ? "active" : "";
                $active			=	$active	== '' && $indexMenu == 0 ? 'active' : '';
                $menuElement    .=  '<li id="menu'.$keyMenu->MENUALIAS.'" class="menu-item nav-item" data-bs-toggle="tooltip" data-bs-placement="top" title="'.$keyMenu->MENUNAME.'"  data-alias="'.$keyMenu->MENUALIAS.'" data-url="'.$keyMenu->URL.'">
                                        <a class="nav-link '.$active.'" id="pills-user-tab" data-bs-toggle="pill" href="#pills-'.$keyMenu->URL.'" role="tab">
                                            <i class="'.$keyMenu->ICON.'"></i>
                                        </a>
                                    </li>';
			}
			
			return $menuElement;
		}
    }
}
