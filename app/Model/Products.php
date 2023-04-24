<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = "products";

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
    ];
}
