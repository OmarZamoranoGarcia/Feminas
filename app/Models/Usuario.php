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
        'is_admin',
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
            'is_admin'              => 'boolean',
        ];
    }

    /** Every user can sell — only admins get elevated privileges. */
    public function esAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * All users can sell products.
     * Kept for compatibility — always returns true.
     */
    public function esVendedor(): bool
    {
        return true;
    }

    /** Display name for the seller storefront (falls back to nombre). */
    public function nombreComercial(): string
    {
        return $this->razon_social ?? $this->nombre;
    }

    /** Scope: only admin accounts. */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    // Relationships

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
