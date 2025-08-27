<?php

namespace App\Modules\UserData\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'url_banner',
    ];

    public function users()
    {
        return $this->hasOne(User::class, 'banner_id');
    }
}
