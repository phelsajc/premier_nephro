<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = "inventory";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'product',
        'quantity',
        'free',
        'created_by',
        'created_dt',
        'updated_by',
        'updated_dt',
        'pid',
        'particulars',
        'cost',
        'purchase',
        'bought',
        'amount',
        'amount_balance',
        'company_id',
    ];
}
