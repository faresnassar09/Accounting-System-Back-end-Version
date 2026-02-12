<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [

            'name' => $this->name,
            'permissions' => $this->permissionNames,
            'role' => $this->roleName,
            'token' => $this->token
        ];
    }
}
