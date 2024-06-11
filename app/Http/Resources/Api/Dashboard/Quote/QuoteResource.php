<?php

namespace App\Http\Resources\Api\Dashboard\Quote;

use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id ,
            'author'=>$this->author ,
            'author_job'=>$this->author_job ,
            'image'=>$this->image ,
            'title'=>$this->title ,
            'desc'=>$this->desc ,
        ];
    }
}
