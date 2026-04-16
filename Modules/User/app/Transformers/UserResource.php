<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
protected $token;
public function __construct($resource, $token = null)
    {
        parent::__construct($resource);
        $this->token = $token;
    }
    public function toArray(Request $request): array
    {
        return [

            'name' => $this->name,
            'permissions' => $this->permissionNames,
            'role' => $this->roleName,
            'token' => $this->token,
        ];
    }
}
