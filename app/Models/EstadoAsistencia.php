<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoAsistencia extends Model
{
    protected $table = 'tb_estados_asistencia';

    protected $primaryKey = 'id_estado_asistencia';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function asistencias()
    {
        return $this->hasMany(
            Asistencia::class,
            'id_estado_asistencia',
            'id_estado_asistencia'
        );
    }
}
