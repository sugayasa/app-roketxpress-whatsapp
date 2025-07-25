<?php
namespace App\Libraries;
use Kreait\Firebase\Factory;

class FirebaseRTDB
{
    public function updateRealtimeDatabaseValue($parentReference, $updateValue)
    {
        try {
			$factory            =	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
            $database           =	$factory->createDatabase();
            $referenceParent    =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$parentReference);
            $referenceParentGet =	$referenceParent->getValue();

            if($referenceParentGet !== null && !is_null($referenceParentGet)){
                $referenceParent->set($updateValue);
            }
		} catch (\Throwable $th) {
			return $th->getMessage();
		}
        return true;
    }
    
    public function updateRealtimeDatabaseMultiValue($arrReferenceValue)
    {
        try {
			$factory            =	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
            $database           =	$factory->createDatabase();

            if(!empty($arrReferenceValue) && is_array($arrReferenceValue) && count($arrReferenceValue) > 0){
                foreach($arrReferenceValue as $parentReference => $updateValue){
                    $referenceParent    =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$parentReference);
                    $referenceParentGet =	$referenceParent->getValue();

                    if($referenceParentGet !== null && !is_null($referenceParentGet)) $referenceParent->set($updateValue);
                }
            }
		} catch (\Throwable $th) {
			return $th->getMessage();
		}
        return true;
    }
}