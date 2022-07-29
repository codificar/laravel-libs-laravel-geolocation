<?php

namespace Codificar\Geolocation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetStaticMapResource extends JsonResource
{

    /**
     * Class GetLatLangFromAddressResource
     *
     * @package GeolocationLib.
     *
     *
     * @OA\Schema(
     *      schema="GetStaticMapResource",
     *      type="object",
     *      description="Response for GetStaticMapResource api",
     *      title="GetStaticMapResource Resource",
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/GetStaticMapResource"),
     *          @OA\Schema(
     *              required={"url"},
     *              @OA\Property(property="url", format="url", type="string"),
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
        if ($this['success']) {
            return [
                "url" => $this['data'],
                'sucess' => $this['success']
            ];
        }
        return [
            "sucess" => false,
            "error" => array_key_exists('error', $this) ? $this['error'] : ''
        ];
    }
}