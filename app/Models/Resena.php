<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';
    protected $primaryKey = 'id_review';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'id_comprador',
        'calificacion',
        'comentario',
        'respuesta_vendedor',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'integer',
            'fecha'        => 'datetime',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    public function comprador()
    {
        return $this->belongsTo(Usuario::class, 'id_comprador', 'id_usuario');
    }
}
