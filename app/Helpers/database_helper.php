<?php

if(!function_exists('switchMySQLErrorCode')){
    function switchMySQLErrorCode($errorCode, $httpResponse = true){
       switch($errorCode){
            case 0		:	$msgError   =   "No data changes";
                            return $httpResponse ? throwResponseNotModified($msgError) : $msgError;
                            break;
            case 1062	:	$msgError   =   "There is duplication in the input data";
                            return $httpResponse ? throwResponseConlflict($msgError) : $msgError;
                            break;
            case 1054	:	$msgError   =   "Database internal script error";
                            return $httpResponse ? throwResponseInternalServerError($msgError) : $msgError;
                            break;
            case 1329	:	$msgError   =   "No data - zero rows fetched, selected, or processed";
                            return $httpResponse ? throwResponseInternalServerError($msgError) : $msgError;
                            break;
            default		:	$msgError   =   "Unknown database internal error";
                            return $httpResponse ? throwResponseInternalServerError($msgError) : $msgError;
                            break;
        }
    }
}