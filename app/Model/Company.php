<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = "company";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'company',
        'description',
        'address',
    ];
}
