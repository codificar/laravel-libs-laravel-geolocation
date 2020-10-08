<?php

namespace Codificar\Geolocation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PlacesResource
 *
 * @package UberClone
 *
 * @OA\Schema(
 *         schema="PlacesResource",
 *         type="object",
 *         description="Biblioteca de places para Uber Clone",
 *         title="Places Resource",
 *         allOf={
 *           @OA\Schema(ref="#/components/schemas/PlacesResource"),
 *           @OA\Schema(
 *              required={"success"},
 *              @OA\Property(property="success", format="boolean", type="boolean")
 *           )
 *         }
 * )
 */
class PlacesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this["response"];
    }
}