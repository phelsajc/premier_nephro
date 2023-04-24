<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReceivedProducts extends Model
{
    protected $table = "received_products";

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
