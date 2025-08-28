<?php

namespace Tests\Traits;

use App\Modules\UserData\Domain\Models\User;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

trait WithUser
{
    private $guard;

    public function __construct() {
         /** @var JWTGuard $guard */ //Eliminates ugly intelephense problem >:(
        $this->guard = auth('api');
    }

    /**
     * Creates an user and returns [User, JWT Headers].
     *
     * @param array $overrides Overwrites factory data
     * @return array
     */
    protected function actingAsUser(array $overrides = []): array
    {
        /** @var User $user */
        $user = User::factory()->create($overrides);

        $token = $this->guard->login($user);

        $headers = ['Authorization' => "Bearer $token"];

        return [$user, $headers];
    }
}
