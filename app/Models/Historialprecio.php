<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPrecio extends Model
{
    protected $table = 'historial_precios';
    protected $primaryKey = 'id_historial';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'precio_anterior',
        'precio_nuevo',
    ];

    protected function casts(): array
    {
        return [
            'precio_anterior' => 'decimal:2',
            'precio_nuevo'    => 'decimal:2',
            'fecha_cambio'    => 'datetime',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}
