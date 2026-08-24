<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UbicacionSupervisor extends Model
{
    protected $table = 'tb_ubicaciones_supervisor';

    protected $primaryKey = 'id_ubicacion';

    public $timestamps = true;

    protected $fillable = [
        'id_supervisor',
        'latitud',
        'longitud',
        'precision_metros',
        'fecha_hora',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'precision_metros' => 'decimal:2',
        'fecha_hora' => 'datetime',
    ];

    public function supervisor()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_supervisor',
            'id_usuario'
        );
    }
}
