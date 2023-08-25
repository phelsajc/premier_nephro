<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;
use App\Model\Patients;

class Helper
{
    public static function patientDetail($id)
    {
        $patient_detail = Patients::where(["id"=>$id])->first();
        return $patient_detail;
    }
}