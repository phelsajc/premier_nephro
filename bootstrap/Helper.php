<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;
use App\Model\Patients;
use App\Model\Doctors;
use App\User;

class Helper
{
    public static function patientDetail($id)
    {
        $patient_detail = Patients::where(["id"=>$id])->first();
        return $patient_detail;
    }

    public static function doctorzDetail($id)
    {
        $Doctors_detail = Doctors::where(["id"=>$id])->first();
        return $Doctors_detail;
    }

    
    public static function userDetail($id)
    {
        $user_detail = User::where(["id"=>$id])->first();
        return $user_detail;
    }
}