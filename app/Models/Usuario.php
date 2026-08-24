<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'tb_usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = true;

    protected $fillable = [
        'id_rol',
        'nombre',
        'apellido',
        'correo',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function tokensPassword()
    {
    return $this->hasMany(
        TokenPassword::class,
        'id_usuario',
        'id_usuario'
    );
    }

    public function vendedor()
    {
        return $this->hasOne(
            Vendedor::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function rol()
    {
        return $this->belongsTo(
            Rol::class,
            'id_rol',
            'id_rol'
        );
    }

}
