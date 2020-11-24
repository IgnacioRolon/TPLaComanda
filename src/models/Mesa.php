<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model {
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'codigoMesa';
}