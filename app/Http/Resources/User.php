<?php

namespace App\Http\Resources;

use App\Friend;
use App\Http\Resources\Friend as FriendResource;
use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [
                'type' => 'users',
                'user_id' => $this->id,
                'attributes' => [
                    'name' => $this->name,
                    'friendship' => new FriendResource(Friend::friendship($this->id)),
                ],
            ],
            'links' => [
                'self' => url(sprintf('/users/%s', $this->id)),
            ],
        ];
    }
}
