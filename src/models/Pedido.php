<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model {
    public $incrementing = false;
    protected $primaryKey = 'codigoPedido';
}