<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginCredentials extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'name' => $this->name,
            'permissions' => $this->permissionNames,
            'roles' => $this->roleNames,
        ];
    }
}
