<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'tb_vendedores';

    protected $primaryKey = 'id_vendedor';

    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'id_supervisor',
        'codigo_empleado',
        'telefono',
        'dpi',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | USUARIO DEL VENDEDOR
    |--------------------------------------------------------------------------
    */

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SUPERVISOR ASIGNADO
    |--------------------------------------------------------------------------
    |
    | id_supervisor apunta al usuario que tiene rol SUPERVISOR.
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
    | ASIGNACIONES
    |--------------------------------------------------------------------------
    */

    public function asignaciones()
    {
        return $this->hasMany(
            Asignacion::class,
            'id_vendedor',
            'id_vendedor'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRAR VENDEDORES POR SUPERVISOR
    |--------------------------------------------------------------------------
    */

    public function scopeDelSupervisor(
        $query,
        int $idSupervisor
    ) {
        return $query->where(
            'id_supervisor',
            $idSupervisor
        );
    }
}
