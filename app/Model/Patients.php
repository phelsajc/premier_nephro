<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Patients extends Model
{
    protected $table = "patients";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'birthdate',
        'sex',
        'address',
        'contact_no',
        'attending_doctor',
        'status',
    ];
}
