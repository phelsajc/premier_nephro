<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Transaction_log extends Model
{
    protected $table = "transaction_log";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'action',
        'created_by',
        'created_at',
        'module',
        'phic_id',
        'remarks',
    ];
}
