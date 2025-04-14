<?php
namespace App\Libraries;
use Kreait\Firebase\Factory;

class FirebaseRTDB
{
    public function updateRealtimeDatabaseValue($parentReferece, $arrayUpdateValue)
    {
        try {
			$factory            =	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
            $database           =	$factory->createDatabase();
            $referenceParent    =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$parentReferece);
            $referenceParentGet =	$referenceParent->getValue();
            if($referenceParentGet != null && !is_null($referenceParentGet)){
                $referenceParent->set($arrayUpdateValue);
            }
		} catch (\Throwable $th) {
			return $th->getMessage();
		}
        return true;
    }
}