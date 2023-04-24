<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FailedSchedule extends Model
{
    protected $table = "failed_scheduled";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'schedule',
        'patient_id',
        'created_by',
        'created_dt',
        'doctor',
    ];
}
