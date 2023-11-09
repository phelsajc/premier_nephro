<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    protected $table = "original_cost";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'product',
        'cost',
        'pid',
        'created_dt'
    ];
}
