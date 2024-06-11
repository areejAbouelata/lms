<?php

namespace App\Http\Resources\Api\Dashboard\Client;

use App\Http\Resources\Api\Dashboard\Client\ActivityResource;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserActivities extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            "client" => SimpleClientResource::make($this->client),
            'activity' => ActivityResource::make($this->activity),
            "is_correct" => $this->is_correct,
            "finished_at" => $this->finished_at ? Carbon::parse($this->finished_at)->format("Y-m-d H:i") : null,
            "start_date" => $this->start_date ? Carbon::parse($this->start_date)->format("Y-m-d H:i") : null,
            "end_date" => $this->end_date ? Carbon::parse($this->end_date)->format("Y-m-d H:i") : null,
            "total_answers" => $this->total_answers,
            "status" => $this->status,
            "note" => $this->note,
            "remarks" => $this->remarks,
        ];
    }
}
