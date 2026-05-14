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
    ];

    protected $hidden = [
        'password_hash',
    ];

    // Map Laravel's auth system to our column name
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
            'password_hash'  => 'hashed',
        ];
    }

    // Relationships
    public function vendedor()
    {
        return $this->hasOne(Vendedor::class, 'id_vendedor', 'id_usuario');
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
}
