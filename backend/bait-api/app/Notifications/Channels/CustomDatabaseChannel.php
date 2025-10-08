<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CustomDatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toCustomDatabase')) {
            return;
        }

        $data = $notification->toCustomDatabase($notifiable);

        $exists = DB::table('notifications')->where([
            ['user_id', $notifiable->id],
            ['type_notifications', $data['type_notifications']],
            ['content_notifications', $data['content_notifications'] ?? null]
        ])->exists();

        if ($exists) {
            return; 
        }

        DB::table('notifications')->insert([
            'type_notifications'     => $data['type_notifications'],
            'content_notifications'  => $data['content_notifications'] ?? null,
            'is_read_notifications'  => false,
            'user_id'                => $notifiable->id,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);
    }
}
