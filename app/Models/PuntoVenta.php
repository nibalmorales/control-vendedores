<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuntoVenta extends Model
{
    protected $table = 'tb_puntos_venta';

    protected $primaryKey = 'id_punto_venta';

    public $timestamps = true;

    protected $fillable = [
        'id_supervisor',
        'nombre',
        'direccion',
        'departamento',
        'municipio',
        'latitud',
        'longitud',
        'radio_permitido_metros',
        'activo',
    ];

    protected $casts = [
        'id_supervisor' => 'integer',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'radio_permitido_metros' => 'integer',
        'activo' => 'boolean',
    ];

    public function supervisor()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_supervisor',
            'id_usuario'
        );
    }

    public function asignaciones()
    {
        return $this->hasMany(
            Asignacion::class,
            'id_punto_venta',
            'id_punto_venta'
        );
    }
}
