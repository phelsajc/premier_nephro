<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    protected $table = "ledger";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'referenceno',
        'particulars',
        'qty',
        'unit_price',
        'payment',
        'total',
        'created_by',
        'created_dt',
        'checkno',
        'purchased_dt',
    ];
}
