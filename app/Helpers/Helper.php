<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;
use App\Model\Patients;
use App\Model\Doctors;

class Helper
{
    public static function patientDetail($id)
    {
        $patient_detail = Patients::where(["id"=>$id])->first();
        return $patient_detail;
    }

    public static function doctorDetail($id)
    {
        $doctor_detail = Doctors::where(["id"=>$id])->first();
        return $doctor_detail;
    }
}