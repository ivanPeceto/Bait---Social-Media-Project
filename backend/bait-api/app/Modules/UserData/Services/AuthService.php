<?php
namespace App\Modules\UserData\Services;

use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserRole;
use App\Modules\UserData\Domain\Models\UserState;
use App\Modules\UserData\Domain\Models\Avatar;
use App\Modules\UserData\Domain\Models\Banner;
use Illuminate\Support\Facades\Hash;

class AuthService {
  public function register(array $data): User
  {
    return User::create([
      'username' => $data['username'],
      'name'     => $data['name'],
      'email'    => $data['email'],
      'password' => Hash::make($data['password']),
      'role_id'  => $this->defaultRoleId(),
      'state_id' => $this->activeStateId(),
      // CORREGIDO: Asignamos el avatar y banner por defecto.
      'avatar_id'=> $this->defaultAvatarId(),
      'banner_id'=> $this->defaultBannerId(),
    ]);
  }

  // MEJORADO: Usamos los modelos de Eloquent en lugar de consultas directas a la BBDD.
  protected function defaultRoleId(): int
  {
     return UserRole::where('name', 'user')->value('id'); 
  }

  protected function activeStateId(): int
  {
     return UserState::where('name', 'active')->value('id'); 
  }

  protected function defaultAvatarId(): int
  {
    return Avatar::value('id');
  }

  protected function defaultBannerId(): int
  {
    return Banner::value('id');
  }
}