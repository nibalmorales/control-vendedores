<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'tb_horarios';

    protected $primaryKey = 'id_horario';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'hora_entrada',
        'hora_salida',
        'tolerancia_minutos',
        'activo',
    ];

    protected $casts = [
        'tolerancia_minutos' => 'integer',
        'activo' => 'boolean',
    ];

    public function asignaciones()
    {
        return $this->hasMany(
            Asignacion::class,
            'id_horario',
            'id_horario'
        );
    }
}
