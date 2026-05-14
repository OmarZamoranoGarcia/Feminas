<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SplitPago extends Model
{
    protected $table = 'split_pagos';
    protected $primaryKey = 'id_split';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_pago',
        'id_vendedor',
        'monto_vendedor',
        'monto_comision',
        'estado_liberacion',
    ];

    protected function casts(): array
    {
        return [
            'monto_vendedor' => 'decimal:2',
            'monto_comision' => 'decimal:2',
        ];
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }

    /**
     * The seller receiving this split.
     */
    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'id_vendedor', 'id_usuario');
    }
}
