<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = "failed_scheduled";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'action',
        'created_by',
        'created_dt',
        'schedule'
    ];
}
