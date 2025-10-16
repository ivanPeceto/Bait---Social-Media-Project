<?php

namespace App\Docs;

/**
 * @OA\Schema(
 *     schema="PaginationSchema",
 *     type="object",
 *     description="Pagination metadata",
 *     @OA\Property(property="total", type="integer", example=45, description="Total number of items"),
 *     @OA\Property(property="per_page", type="integer", example=10, description="Number of items per page"),
 *     @OA\Property(property="current_page", type="integer", example=1, description="Current page number"),
 *     @OA\Property(property="last_page", type="integer", example=5, description="Last page number"),
 *     @OA\Property(property="from", type="integer", example=1, description="Index of the first item on the current page"),
 *     @OA\Property(property="to", type="integer", example=10, description="Index of the last item on the current page")
 * )
 */
class PaginationSchema
{

}
