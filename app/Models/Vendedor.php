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
<<<<<<< HEAD
        'razon_social',
        'rfc',
        'descripcion',
        'calificacion_promedio',
        'politicas_devolucion',
        'banco_cuenta',
=======
<<<<<<< HEAD
        'id_usuario',
        'nombre',
        'tienda',
>>>>>>> 1c5b382d14f04f7ae204d4e452d26cb63044b802
    ];
=======
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
>>>>>>> 9aa5508 (Refactorized database)
}
