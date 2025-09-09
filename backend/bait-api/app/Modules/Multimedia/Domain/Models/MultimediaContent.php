<?php

namespace App\Modules\Multimedia\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\MultimediaContentFactory;

class MultimediaContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'url_multimedia_contents',
        'type_multimedia_contents',
        'post_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    protected static function newFactory()
    {
        return MultimediaContentFactory::new();
    }
}