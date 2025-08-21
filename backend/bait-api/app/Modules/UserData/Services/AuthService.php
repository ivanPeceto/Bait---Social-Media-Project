<?php
namespace App\Modules\UserData\Services;

use App\Modules\UserData\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService {
  public function register(array $data): User {
    return User::create([
      'username' => $data['username'],
      'name'   => $data['name'],
      'email'  => $data['email'],
      'password' => Hash::make($data['password']),
      'role_id'  => $data['role_id'] ?? $this->defaultRoleId(),
      'state_id' => $this->activeStateId(),
    ]);
  }

  protected function defaultRoleId(): int{
     return \DB::table('user_roles')->where('name','user')->value('id'); 
  }
  protected function activeStateId(): int{
     return \DB::table('user_states')->where('name','active')->value('id'); 
  }
}
