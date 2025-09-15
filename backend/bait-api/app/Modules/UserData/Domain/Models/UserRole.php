<?php

namespace App\Modules\UserData\Domain\Models;

use Database\Factories\UserRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
    
    protected static function newFactory()
    {
        return UserRoleFactory::new();
    }
}