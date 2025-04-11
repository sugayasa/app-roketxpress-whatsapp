<?php

namespace App\Controllers\Settings;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\ChartOfAccountModel;
use App\Models\MainOperation;
use App\Models\Settings\SystemSettingModel;

class SystemSetting extends ResourceController
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

    public function getDataSystemSetting()
    {
        helper(['form']);
        $rules      =   [
            'arrIdSystemSetting'    =>  ['label' => 'Data Setting', 'rules' => 'required|is_array']
        ];

        $messages   =   [
            'arrIdSystemSetting'  =>  [
                'required'      =>  'Invalid data sent',
                'alpha_numeric' =>  'Invalid data sent'
            ]
        ];
        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());
        $systemSettingModel =   new SystemSettingModel();
        $chartOfAccountModel=   new ChartOfAccountModel();
        $arrIdSystemSetting =   $this->request->getVar('arrIdSystemSetting');
        $dataSystemSetting  =	$systemSettingModel->whereIn('IDSYSTEMSETTINGS', $arrIdSystemSetting)->findAll();

        if($dataSystemSetting){
            foreach($dataSystemSetting as $indexSystemSetting => $keySystemSetting){
                $idSystemSetting    =   $keySystemSetting['IDSYSTEMSETTINGS'];
                switch($idSystemSetting){
                    case 1: break;
                    case "2":
                    case 2:
                        $idAccount          =   $keySystemSetting['DATASETTING'];
                        $detailAccountBasic =   $chartOfAccountModel->getBasicDetailAccount($idAccount);
                        $levelAccount       =   $detailAccountBasic['LEVEL'];
                        $detailAccount      =   $chartOfAccountModel->getDetailAccount($levelAccount, $idAccount);
                        $dataSystemSetting[$indexSystemSetting]['DATASETTING']    =   [
                            "IDACCOUNT"         =>  hashidEncode($idAccount),
                            "IDACCOUNTPARENT"   =>  hashidEncode($detailAccount['IDACCOUNTPARENT']),
                            "ACCOUNTLEVEL"      =>  $levelAccount,
                            "ACCOUNTNAME"       =>  $detailAccount['ACCOUNTNAME'],
                            "ACCOUNTCODE"       =>  $detailAccount['ACCOUNTCODE']
                        ];
                        break;
                }
            }

            return $this->setResponseFormat('json')
                        ->respond([
                            "dataSystemSetting" =>  $dataSystemSetting
                        ]);
        } else {
            return throwResponseNotFound('No data found based on the data setting sent');
        }
    }

    public function updateSystemSettings()
    {
        helper(['form']);
        $rules          =   [
            'arrJsonSend'   =>  ['label' => 'Data Settings', 'rules' => 'required|is_array']
        ];

        $messages   =   [
            'arrJsonSend'   =>  [
                'required' => 'This email address is already registered, please enter another email address',
                'is_array' => 'Invalid data send format'
            ]
        ];
        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $arrJsonSend    =   $this->request->getVar('arrJsonSend');

        if(!is_null($arrJsonSend) && count($arrJsonSend) > 0){
            $systemSettingModel =   new SystemSettingModel();
            foreach($arrJsonSend as $arrData){
                $idSystemSetting    =   $arrData[0];
                $systemSettingValue =   $arrData[1];

                switch($idSystemSetting){
                    case 1:
                    case "1":
                        $systemSettingValueCheck    =   detectDateFormat($systemSettingValue);
                        if($systemSettingValueCheck){
                            $systemSettingValue     =   $systemSettingValueCheck->format('Y-m-d');
                        }
                        break;
                    case 2:
                    case "2":
                        $systemSettingValue =   hashidDecode($systemSettingValue);
                        break;
                    default:
                        break;
                }

                $arrUpdateData      =   [
                    'DATASETTING'   =>  $systemSettingValue
                ];
                $systemSettingModel->update($idSystemSetting, $arrUpdateData);
            }
        }

        return throwResponseOK('Data settings has been updated');
    }
}