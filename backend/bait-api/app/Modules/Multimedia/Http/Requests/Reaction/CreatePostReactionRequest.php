<?php

namespace App\Modules\Multimedia\Http\Requests\Reaction;

use Illuminate\Foundation\Http\FormRequest;
use App\Modules\Multimedia\Domain\Models\PostReaction;

class CreatePostReactionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'post_id' => 'required|exists:posts,id',
            'reaction_type_id' => 'required|exists:reaction_types,id',
        ];
    }
    
    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user_id = auth()->id();
            $post_id = $this->post_id;
            
            // Verifica si el usuario ya ha reaccionado a este post
            $existingReaction = PostReaction::where('user_id', $user_id)
                                          ->where('post_id', $post_id)
                                          ->first();

            if ($existingReaction) {
                // Si la reaccion es del mismo tipo, se permite eliminarla
                if ($existingReaction->reaction_type_id == $this->reaction_type_id) {
                    $this->action = 'delete';
                } else {
                    // Si la reaccion es de otro tipo, se permite actualizarla
                    $this->action = 'update';
                }
            } else {
                $this->action = 'create';
            }
        });
    }
}