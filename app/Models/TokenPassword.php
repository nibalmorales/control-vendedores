<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPassword extends Model
{
    protected $table = 'tb_tokens_password';

    protected $primaryKey = 'id_token';

    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'token',
        'tipo',
        'fecha_expiracion',
        'usado',
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'usado' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }
}
