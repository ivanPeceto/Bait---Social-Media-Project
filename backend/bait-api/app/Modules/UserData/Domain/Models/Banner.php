<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\BannerFactory;

class Banner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'url_banners', // Corregido: de 'url_banner' a 'url_banners'
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        // Agregado: Apunta al Factory específico del módulo
        return BannerFactory::new();
    }

    public function users()
    {
        return $this->hasOne(User::class, 'banner_id');
    }
}