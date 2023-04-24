<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = "schedule";

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
