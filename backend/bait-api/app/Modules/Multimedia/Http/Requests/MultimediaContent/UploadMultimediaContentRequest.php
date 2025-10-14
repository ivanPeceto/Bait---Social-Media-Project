<?php

namespace App\Modules\Multimedia\Http\Requests\MultimediaContent;

use Illuminate\Foundation\Http\FormRequest;

class UploadMultimediaContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480', // Max 20MB
            'post_id' => 'required|integer|exists:posts,id',
        ];
    }
}