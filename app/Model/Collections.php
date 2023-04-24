<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Collections extends Model
{
    protected $table = "collections";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'type',
        'check_date',
        'companyid',
        'si_dr_no',
        'amount',
        'details',
        'with_ewt_deductions',
        'date_deposited',
        'crno',
        'dsno',
        'created_by',
        'created_dt',
    ];
}
