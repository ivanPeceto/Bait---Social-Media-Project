<?php

namespace App\Docs;

/**
 * @OA\Schema(
 * schema="MessageSchema",
 * type="object",
 * title="Message Schema",
 * description="Represents a message within a chat.",
 * @OA\Property(property="id", type="integer", description="The message's unique identifier.", example=101),
 * @OA\Property(property="content_messages", type="string", description="The text content of the message.", example="Hello, how are you?"),
 * @OA\Property(property="user", ref="#/components/schemas/UserSchema", description="The user who sent the message."),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the message was sent.",
 * example="2025-10-05T12:30:00.000000Z"
 * )
 * )
 */
class MessageSchema
{
}