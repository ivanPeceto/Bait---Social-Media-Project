<?php

namespace App\Modules\UserData\Http\Requests\Avatar;

use Illuminate\Foundation\Http\FormRequest;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class BannerUploadRequest extends FormRequest
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
            'banner' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096', // 2MB
                'dimensions:min_width=300,min_height=100,max_width=3000,max_height=1000',
            ],
        ];
    }
}
