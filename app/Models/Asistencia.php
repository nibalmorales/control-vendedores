<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'tb_asistencias';

    protected $primaryKey = 'id_asistencia';

    public $timestamps = true;

    protected $fillable = [

        'id_asignacion',

        'id_punto_llegada',
        'id_punto_salida',

        'id_estado_asistencia',

        'fecha',

        'hora_llegada',
        'latitud_llegada',
        'longitud_llegada',
        'precision_llegada_metros',
        'distancia_llegada_metros',

        'hora_salida',
        'latitud_salida',
        'longitud_salida',
        'precision_salida_metros',
        'distancia_salida_metros',

        'observaciones',
    ];

    protected $casts = [

        'fecha' => 'date',

        'hora_llegada' => 'datetime',
        'hora_salida' => 'datetime',

        'latitud_llegada' => 'decimal:7',
        'longitud_llegada' => 'decimal:7',

        'latitud_salida' => 'decimal:7',
        'longitud_salida' => 'decimal:7',

        'precision_llegada_metros' => 'decimal:2',
        'distancia_llegada_metros' => 'decimal:2',

        'precision_salida_metros' => 'decimal:2',
        'distancia_salida_metros' => 'decimal:2',

    ];


    public function asignacion()
    {
        return $this->belongsTo(
            Asignacion::class,
            'id_asignacion',
            'id_asignacion'
        );
    }


    public function estado()
    {
        return $this->belongsTo(
            EstadoAsistencia::class,
            'id_estado_asistencia',
            'id_estado_asistencia'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PUNTO REAL DE LLEGADA
    |--------------------------------------------------------------------------
    */

    public function puntoLlegada()
    {
        return $this->belongsTo(
            PuntoVenta::class,
            'id_punto_llegada',
            'id_punto_venta'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PUNTO REAL DE SALIDA
    |--------------------------------------------------------------------------
    */

    public function puntoSalida()
    {
        return $this->belongsTo(
            PuntoVenta::class,
            'id_punto_salida',
            'id_punto_venta'
        );
    }
}
