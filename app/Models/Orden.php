<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $table = 'ordenes';
    protected $primaryKey = 'id_orden';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_comprador',
        'estado',
        'total',
        'metodo_pago',
        'direccion_envio',
        'id_transaccion_pago',
    ];

    protected function casts(): array
    {
        return [
            'fecha_orden' => 'datetime',
            'total'       => 'decimal:2',
        ];
    }

    public function comprador()
    {
        return $this->belongsTo(Usuario::class, 'id_comprador', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'id_orden', 'id_orden');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'id_orden', 'id_orden');
    }

    public function envio()
    {
        return $this->hasOne(Envio::class, 'id_orden', 'id_orden');
    }
}
