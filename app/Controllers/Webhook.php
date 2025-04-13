<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainOperation;

class Webhook extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function whatsappOneMsgIO()
    {
        $mainOperation  =   new MainOperation();
        $params         =   $this->request->getJSON();
        $arrInsert      =   [
            'PARAMETERDATA' =>  json_encode($params),
            'LOGDATETIME'   =>  date('Y-m-d H:i:s')
        ];
        $mainOperation->insertDataTable('log_webhook', $arrInsert);

        return throwResponseOK('Data saved successfully');
    }
}