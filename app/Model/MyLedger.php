<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MyLedger extends Model
{
    protected $table = "my_ledger";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'product',
        'description',
        'quantity',
        'uom',
        'dop',
        'code',
        'updated_by',
        'updated_dt',
        'date_receive',
    ];
}
