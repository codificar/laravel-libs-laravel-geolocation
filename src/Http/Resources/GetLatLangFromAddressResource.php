<?php

namespace Codificar\Geolocation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetLatLangFromAddressResource extends JsonResource
{

    /**
     * Class GetLatLangFromAddressResource
     *
     * @package GeolocationLib.
     *
     *
     * @OA\Schema(
     *      schema="GetLatLangFromAddressResource",
     *      type="object",
     *      description="Response for GetLatLangFromAddressResource api",
     *      title="GetLatLangFromAddress Resource",
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/GetLatLangFromAddressResource"),
     *          @OA\Schema(
     *              required={"success", "latitude", "longitude"},
     *              @OA\Property(property="success", format="boolean", type="boolean"),
     *              @OA\Property(property="latitude", format="float", type="number"),
     *              @OA\Property(property="longitude", format="float", type="number")
     *          )
     *      }
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        $arraySuccess = array('success' => $this['response']['success']);
        if ($arraySuccess['success']) {
            $content = [
                'latitude' => $this['response']['data']->lat,
                'longitude' => $this['response']['data']->lng,
            ];
        } else {
            $content = ["error_message" => $this['response']['error_message']];
        }
        return array_merge($arraySuccess, $content);
    }
}