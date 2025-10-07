<?php

namespace App\Docs;

/**
 * @OA\Schema(
 * schema="ChatSchema",
 * type="object",
 * title="Chat Schema",
 * description="Represents a chat conversation between users.",
 * @OA\Property(property="id", type="integer", description="The chat's unique identifier.", example=1),
 * @OA\Property(
 * property="participants",
 * type="array",
 * description="List of users participating in the chat.",
 * @OA\Items(ref="#/components/schemas/UserSchema")
 * ),
 * @OA\Property(property="last_message", ref="#/components/schemas/MessageSchema", description="The most recent message in the chat.", nullable=true),
 * @OA\Property(
 * property="created_at",
 * type="string",
 * format="date-time",
 * description="Timestamp when the chat was created.",
 * example="2025-10-05T10:00:00.000000Z"
 * )
 * )
 */
class ChatSchema
{
}