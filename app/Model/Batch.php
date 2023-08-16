<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = "batch";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'batch'
    ];
}
