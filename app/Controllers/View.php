<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Models\ChartOfAccountModel;
use App\Models\MainOperation;

class View extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $userData, $currentDateTime, $currentDateDT;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
            $this->currentDateDT    =   $request->currentDateDT;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }
    
    public function chat()
    {
        $contentPills   =   view(
                                'ContentPills/chat',
                                [],
                                ['debug' => false]
                            );
        $contentMain    =   view(
                                'ContentMain/chat',
                                ['idUserAdmin' => hashidEncode($this->userData->idUserAdmin, true)],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'contentPills'  =>  $contentPills,
            'contentMain'   =>  $contentMain,
            'isChatContent' =>  true
        ]);
    }
    
    public function contact()
    {
        $contentPills   =   view(
                                'ContentPills/contact',
                                ['defaultCountryCode'=>  hashidEncode(96)],
                                ['debug' => false]
                            );
        $contentMain    =   view(
                                'ContentMain/contact',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'contentPills'  =>  $contentPills,
            'contentMain'   =>  $contentMain,
            'isChatContent' =>  false
        ]);
    }
    
    public function userLevelMenu()
    {
        $contentPills   =   view(
                                'ContentPills/userLevelMenu',
                                [],
                                ['debug' => false]
                            );
        $contentMain    =   view(
                                'ContentMain/userLevelMenu',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'contentPills'  =>  $contentPills,
            'contentMain'   =>  $contentMain,
            'isChatContent' =>  false
        ]);
    }
    
    public function userAdmin()
    {
        $contentPills   =   view(
                                'ContentPills/userAdmin',
                                [],
                                ['debug' => false]
                            );
        $contentMain    =   view(
                                'ContentMain/userAdmin',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'contentPills'  =>  $contentPills,
            'contentMain'   =>  $contentMain,
            'isChatContent' =>  false
        ]);
    }
    
    public function systemSetting()
    {
        $contentPills   =   view(
                                'ContentPills/systemSetting',
                                [],
                                ['debug' => false]
                            );
        $contentMain    =   view(
                                'ContentMain/systemSetting',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'contentPills'  =>  $contentPills,
            'contentMain'   =>  $contentMain,
            'isChatContent' =>  false
        ]);
    }
}