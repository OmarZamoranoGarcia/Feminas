<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $table = 'envios';
    protected $primaryKey = 'id_envio';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_orden',
        'transportadora',
        'numero_guia',
        'fecha_envio',
        'fecha_entrega',
        'estado_envio',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio'    => 'datetime',
            'fecha_entrega'  => 'datetime',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'id_orden', 'id_orden');
    }
}
