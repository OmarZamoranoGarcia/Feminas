<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
<<<<<<< HEAD
        'id_producto',
=======
>>>>>>> 9aa5508 (Refactorized database)
        'id_vendedor',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'categoria',
<<<<<<< HEAD
        'estado',
        'fecha_creacion',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
    ];
=======
        'imagen_url',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio'         => 'decimal:2',
            'stock'          => 'integer',
            'fecha_creacion' => 'datetime',
        ];
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'id_vendedor', 'id_vendedor');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'id_producto', 'id_producto');
    }

    public function detallesOrden()
    {
        return $this->hasMany(DetalleOrden::class, 'id_producto', 'id_producto');
    }

    public function historialPrecios()
    {
        return $this->hasMany(HistorialPrecio::class, 'id_producto', 'id_producto');
    }

    public function carritoItems()
    {
        return $this->hasMany(Carrito::class, 'id_producto', 'id_producto');
    }

    // Scope: only active products
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Scope: filter by category
    public function scopeCategoria($query, string $categoria)
    {
        return $query->where('categoria', $categoria);
    }
>>>>>>> 9aa5508 (Refactorized database)
}
