<?php

use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

if(!function_exists('throwResponseOK')){
    function throwResponseOK($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "messages"      =>  [
                        "message" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_OK);
    }
}

if(!function_exists('throwResponseUnauthorized')){
    function throwResponseUnauthorized($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  401,
                    "error"     =>  401,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }
}

if(!function_exists('throwResponseForbidden')){
    function throwResponseForbidden($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  403,
                    "error"     =>  403,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
    }
}

if(!function_exists('throwResponseNotModified')){
    function throwResponseNotModified($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  304,
                    "error"     =>  304,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_NOT_MODIFIED);
    }
}

if(!function_exists('throwResponseNotFound')){
    function throwResponseNotFound($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  404,
                    "error"     =>  404,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
    }
}

if(!function_exists('throwResponseNotAcceptable')){
    function throwResponseNotAcceptable($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  406,
                    "error"     =>  406,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_NOT_ACCEPTABLE);
    }
}

if(!function_exists('throwResponseInternalServerError')){
    function throwResponseInternalServerError($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  500,
                    "error"     =>  500,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }
}

if(!function_exists('throwResponseConlflict')){
    function throwResponseConlflict($message, $arrAdditional = [], $throwableData = null){
        $throwableData  =   ENVIRONMENT === 'production' ? null : $throwableData;
        return Services::response()
        ->setJSON(
            array_merge(
                [
                    "status"    =>  409,
                    "error"     =>  409,
                    "messages"  =>  [
                        "error" =>  $message
                    ],
                    'throwableData' =>  $throwableData
                ],
                $arrAdditional
            )
        )
        ->setStatusCode(ResponseInterface::HTTP_CONFLICT);
    }
}