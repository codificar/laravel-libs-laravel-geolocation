<?php

namespace Codificar\Geolocation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PolylineResource
 *
 * @package MotoboyApp
 *
 * @OA\Schema(
 *         schema="PolylineResource",
 *         type="object",
 *         description="Retorno da rota e dados entre N pontos",
 *         title="Polyline Resource",
 *         allOf={
 *           @OA\Schema(ref="#/components/schemas/PolylineResource"),
 *           @OA\Schema(
 *               required={"success"},
 *               @OA\Property(property="success", format="boolean", type="boolean"),
 *               @OA\Property(property="points", format="array", type="object"),
 *               @OA\Property(property="distance_text", format="string", type="string"),
 *               @OA\Property(property="duration_text", format="string", type="string"),
 *               @OA\Property(property="distance_value", format="float", type="number"),
 *               @OA\Property(property="duration_value", format="integer", type="integer"),
 *               @OA\Property(property="partial_distances", format="array", type="object"),
 *               @OA\Property(property="partial_durations", format="array", type="object"),
 *               @OA\Property(property="error", format="string", type="string")
 *           )
 *       }
 * )
 */
class PolylineResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return $this['data'];
    }

}