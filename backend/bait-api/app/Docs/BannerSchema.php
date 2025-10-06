<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="BannerSchema",
 *     type="object",
 *     title="Banner Schema",
 *     required={"id", "url_banners"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(
 *         property="url_banners",
 *         type="string",
 *         format="url",
 *         example="https://example.com/storage/banners/banner1.jpg"
 *     )
 * )
 */
class BannerSchema
{

}
