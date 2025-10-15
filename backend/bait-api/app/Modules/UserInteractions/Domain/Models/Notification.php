<?php

namespace App\Modules\UserInteractions\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\UserData\Domain\Models\User;
use Database\Factories\NotificationFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_notifications',
        'content_notifications',
        'is_read_notifications',
        'user_id',
    ];

    protected $casts = [
        'is_read_notifications' => 'boolean',
    ];

    public function setTypeAttribute($value)
    {
        $this->attributes['type_notifications'] = $value;
    }

    /**
     * Mutator to map the 'data' attribute from DatabaseChannel
     * to the 'content_notifications' column in this model.
     */
    public function setDataAttribute($value)
    {
        $this->attributes['content_notifications'] = json_encode($value);
    }
    
    /**
     * Override the fill method to map Laravel's notification attributes
     * to our custom database columns.
     */
    public function fill(array $attributes)
    {
        if (isset($attributes['type'])) {
            $this->setAttribute('type_notifications', $attributes['type']);
            unset($attributes['type']);
        }
        if (isset($attributes['data'])) {
            $this->setAttribute('content_notifications', json_encode($attributes['data']));
            unset($attributes['data']);
        }
        
        return parent::fill($attributes);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    protected static function newFactory()
    {
        return NotificationFactory::new();
    }
}