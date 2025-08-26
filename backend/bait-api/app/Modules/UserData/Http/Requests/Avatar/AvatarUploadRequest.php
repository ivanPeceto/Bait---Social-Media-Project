<?php

namespace App\Modules\UserData\Http\Requests\Avatar;

use Illuminate\Foundation\Http\FormRequest;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AvatarUploadRequest extends FormRequest
{
    private $guard;

    public function __construct()
    {
        /** @var JWTGuard $guard */
        $this->guard = auth('api');
    }

    public function authorize(): bool
    {
        //To add: Is user logged in?
        return $this->guard->check();
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2MB
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
        ];
    }
}
