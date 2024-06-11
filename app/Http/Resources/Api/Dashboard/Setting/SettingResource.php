<?php

namespace App\Http\Resources\Api\Dashboard\Setting;

use App\Http\Resources\Api\App\Client\Order\Restaurant\RestaurantCategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($this->key == 'how_it_works') {
            $value = $this->how_it_works;
        } elseif ($this->key == 'logo_image') {
            $value = $this->media()->exists() ? url("storage/images/settings/". $this->media()->first()->media) : 'images/avatar.jpg';
        } else {
            $value = (string)$this->value;
        }


        return [
            'id' => (int)$this->id,
            'key' => (string)$this->key,
//            'value' => ($this->key == "how_it_works") ? $this->how_it_works : (string)$this->value,
            'value' => $value
        ];
    }
}
