<?php

namespace App\Modules\UserData\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class BannerUploadRequest extends FormRequest
{
    /** @var JWTGuard $guard */
    private $guard;

    public function __construct()
    {
        $this->guard = auth('api');
    }

    public function authorize(): bool
    {
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
                'max:4096', // Corregido: El comentario ahora es correcto (4MB)
                'dimensions:min_width=300,min_height=100,max_width=3000,max_height=1000',
            ],
        ];
    }
}