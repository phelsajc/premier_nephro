<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Phic extends Model
{
    protected $table = "phic";

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
        'state',
        'acpn_no',
    ];
}
