<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'tb_asignaciones';

    protected $primaryKey = 'id_asignacion';

    public $timestamps = true;

    protected $fillable = [
        'id_vendedor',
        'id_punto_venta',
        'id_horario',
        'fecha_inicio',
        'fecha_fin',
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado',
        'domingo',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'lunes' => 'boolean',
        'martes' => 'boolean',
        'miercoles' => 'boolean',
        'jueves' => 'boolean',
        'viernes' => 'boolean',
        'sabado' => 'boolean',
        'domingo' => 'boolean',
        'activo' => 'boolean',
    ];

    public function vendedor()
    {
        return $this->belongsTo(
            Vendedor::class,
            'id_vendedor',
            'id_vendedor'
        );
    }

    public function puntoVenta()
    {
        return $this->belongsTo(
            PuntoVenta::class,
            'id_punto_venta',
            'id_punto_venta'
        );
    }

    public function horario()
    {
        return $this->belongsTo(
            Horario::class,
            'id_horario',
            'id_horario'
        );
    }


    public function asistencias()
    {
        return $this->hasMany(
            Asistencia::class,
            'id_asignacion',
            'id_asignacion'
        );
    }
}
