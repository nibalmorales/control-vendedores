<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'tb_roles';

    protected $primaryKey = 'id_rol';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function usuarios()
    {
        return $this->hasMany(
            Usuario::class,
            'id_rol',
            'id_rol'
        );
    }
}
