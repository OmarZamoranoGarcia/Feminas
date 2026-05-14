<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'email',
        'password_hash',
        'nombre',
        'direccion',
        'telefono',
        'razon_social',
        'rfc',
        'descripcion',
        'calificacion_promedio',
        'politicas_devolucion',
        'banco_cuenta',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'fecha_registro'        => 'datetime',
            'password_hash'         => 'hashed',
            'calificacion_promedio' => 'decimal:1',
        ];
    }


    /** Users that can sell (vendedor or admin). */
    public function scopeVendedores($query)
    {
        return $query->whereIn('tipo', ['vendedor', 'admin']);
    }


    /** True when this user can sell products. */
    public function esVendedor(): bool
    {
        return in_array($this->tipo, ['vendedor', 'admin'], true);
    }

    /** Display name for the seller storefront (falls back to nombre). */
    public function nombreComercial(): string
    {
        return $this->razon_social ?? $this->nombre;
    }


    /** Products this user sells (only meaningful when esVendedor()). */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_vendedor', 'id_usuario');
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'id_comprador', 'id_usuario');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'id_comprador', 'id_usuario');
    }

    public function carrito()
    {
        return $this->hasMany(Carrito::class, 'id_usuario', 'id_usuario');
    }

    public function splitPagos()
    {
        return $this->hasMany(SplitPago::class, 'id_vendedor', 'id_usuario');
    }
}
