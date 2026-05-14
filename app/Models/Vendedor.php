<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedores';
    protected $primaryKey = 'id_vendedor';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_vendedor',
        'razon_social',
        'rfc',
        'descripcion',
        'calificacion_promedio',
        'politicas_devolucion',
        'banco_cuenta',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_vendedor', 'id_usuario');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_vendedor', 'id_vendedor');
    }

    public function splitPagos()
    {
        return $this->hasMany(SplitPago::class, 'id_vendedor', 'id_vendedor');
    }
}
