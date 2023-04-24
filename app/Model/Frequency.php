<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Frequency extends Model
{
    protected $table = "frequency";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'desc',
        'quantity',
        'frequency',
    ];
}
