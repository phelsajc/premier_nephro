<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction_details extends Model
{
    protected $table = "transaction_details";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'product_id',
        'product',
        'transaction_details',
        'qty',
        'total',
        'price',
    ];
}
