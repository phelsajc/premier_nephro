<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Copay extends Model
{
    protected $table = "co_pay";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'date_session',
        'patient_id',
        'created_by',
        'created_dt',
        'doctor',
        'status',
    ];
}
