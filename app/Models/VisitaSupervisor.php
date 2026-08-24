<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaSupervisor extends Model
{
    protected $table = 'tb_visitas_supervisor';

    protected $primaryKey = 'id_visita';

    public $timestamps = true;


    /*
    |--------------------------------------------------------------------------
    | CAMPOS ASIGNABLES
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'id_supervisor',

        'id_punto_venta',

        'id_asistencia',

        'fecha',

        'hora_llegada',

        'hora_salida',

        'latitud_llegada',

        'longitud_llegada',

        'precision_llegada_metros',

        'distancia_llegada_metros',

        'latitud_salida',

        'longitud_salida',

        'precision_salida_metros',

        'distancia_salida_metros',

        'observaciones',

    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'fecha' => 'date',

        'hora_llegada' => 'datetime:H:i:s',

        'hora_salida' => 'datetime:H:i:s',

        'latitud_llegada' => 'decimal:7',

        'longitud_llegada' => 'decimal:7',

        'precision_llegada_metros' => 'decimal:2',

        'distancia_llegada_metros' => 'decimal:2',

        'latitud_salida' => 'decimal:7',

        'longitud_salida' => 'decimal:7',

        'precision_salida_metros' => 'decimal:2',

        'distancia_salida_metros' => 'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | SUPERVISOR
    |--------------------------------------------------------------------------
    |
    | id_supervisor guarda el id_usuario del supervisor.
    |
    */

    public function supervisor()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_supervisor',
            'id_usuario'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PUNTO DE VENTA
    |--------------------------------------------------------------------------
    */

    public function puntoVenta()
    {
        return $this->belongsTo(
            PuntoVenta::class,
            'id_punto_venta',
            'id_punto_venta'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ASISTENCIA
    |--------------------------------------------------------------------------
    */

    public function asistencia()
    {
        return $this->belongsTo(
            Asistencia::class,
            'id_asistencia',
            'id_asistencia'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VISITA ABIERTA
    |--------------------------------------------------------------------------
    */

    public function scopeAbierta($query)
    {
        return $query->whereNull('hora_salida');
    }


    /*
    |--------------------------------------------------------------------------
    | VISITAS DE HOY
    |--------------------------------------------------------------------------
    */

    public function scopeDeHoy($query)
    {
        return $query->whereDate(
            'fecha',
            now()->toDateString()
        );
    }
}
