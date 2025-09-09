<?php

namespace App\Modules\UserInteractions\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'is_read_notifications' => ['required', 'boolean'],
        ];
    }
}

//El tipo que inicie sesion y este en la app solo la va a marcar como leida