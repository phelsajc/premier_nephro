<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = "settings";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'value',
    ];
}
