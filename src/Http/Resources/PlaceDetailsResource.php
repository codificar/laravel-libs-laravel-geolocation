<?php

namespace Codificar\Geolocation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PlaceDetailsResource
 *
 * @package UberClone
 *
 * @OA\Schema(
 *      schema="PlaceDetailsResource",
 *      type="object",
 *      description="Retorno de detalhes do Google place_id.",
 *      title="Place Details Resource",
 *      allOf={
 *          @OA\Schema(
 *				required={"success","data"},
 *              @OA\Property(property="success", format="boolean", type="boolean", description=""),
 *              @OA\Property(
 *                  property="data",
 *                  format="array",
 *                  type="object",
 *                  description="",
 *                  @OA\Property(property="address", format="string", type="string", description=""),
 *                  @OA\Property(property="place_id", format="string", type="string", description=""),
 *                  @OA\Property(property="latitude", format="double", type="number", description=""),
 *                  @OA\Property(property="longitude", format="double", type="number", description="")
 *              ),
 *              @OA\Property(property="error_message", format="string", type="string", description="")
 *          )
 *      }
 * )
 */
class PlaceDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {      
        $return     =   [
            'success'   =>  $this["response"]["success"],
            'data'      =>  $this["response"]["data"]
        ];

        if(!$this["response"]["success"])
            $return = array_merge($return, array("error_code" => 402, "errors" => [$this["response"]["error_message"]]));

        return $return;
    }
}
