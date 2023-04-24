<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Doctors extends Model
{
    protected $table = "doctors";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];
}
