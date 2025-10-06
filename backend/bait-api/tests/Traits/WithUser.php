<?php

namespace Tests\Traits;

use App\Modules\UserData\Domain\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

trait WithUser
{
    /**
     * Creates an user and returns [User, JWT Headers].
     *
     * @param array $overrides Overwrites factory data
     * @return array
     */
    public function actingAsUser()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $headers = ['Authorization' => "Bearer $token"];
        
        return [$user, $headers];
    }
    
}