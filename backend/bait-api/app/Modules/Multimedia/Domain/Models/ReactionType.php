<?php

namespace App\Modules\Multimedia\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ReactionTypeFactory;

class ReactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_reaction_types'
    ];

    protected static function newFactory()
    {
        return ReactionTypeFactory::new();
    }
}