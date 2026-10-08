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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'branch' => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
            ] : null,
            'team' => $this->team ? [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ] : null,
            'branch_name' => $this->branch?->name,
            'team_name' => $this->team?->name,
            'permissions' => $this->permissionNames,
            'role' => $this->roleName,
            'token' => $this->token,
        ];
    }
}
