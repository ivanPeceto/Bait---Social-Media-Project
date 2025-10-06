<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="MultimediaContentSchema",
 *     type="object",
 *     title="Multimedia Content",
 *     required={"id", "url_multimedia_contents", "type_multimedia_contents", "post_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="url_multimedia_contents", type="string", example="uploads/posts/photo.jpg"),
 *     @OA\Property(property="type_multimedia_contents", type="string", example="image"),
 *     @OA\Property(property="post_id", type="integer", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-06T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-06T10:00:00Z")
 * )
 */


class MultimediaContentSchema
{

}
