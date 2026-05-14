<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    protected $table = 'comisiones';
    protected $primaryKey = 'id_comision';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'categoria',
        'porcentaje',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje'   => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
            'activo'       => 'boolean',
        ];
    }

    // Get the active commission rate for a given category
    public static function tasaParaCategoria(?string $categoria): float
    {
        $comision = static::where('activo', true)
            ->where(function ($q) use ($categoria) {
                $q->where('categoria', $categoria)
                  ->orWhereNull('categoria');
            })
            ->orderByRaw('categoria IS NULL ASC') // prefer specific over general
            ->first();

        return $comision ? (float) $comision->porcentaje : 10.0; // default 10%
    }
}
