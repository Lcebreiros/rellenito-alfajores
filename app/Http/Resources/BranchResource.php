<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'phone' => $this->phone,
            'contact_email' => $this->contact_email,
            'logo_url' => $this->logoUrl(),
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Información del usuario representante
            'login_email' => $this->login_email,
            'user_limit' => $this->user_limit,
            'users_count' => $this->users_count,
            'can_create_users' => $this->canCreateUsers(),
            
            // Relaciones
            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                ];
            }),
            
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'is_active' => $this->user->is_active,
                    'user_limit' => $this->user->user_limit,
                    'hierarchy_level' => $this->user->hierarchy_level,
                ];
            }),
            
            // Enlaces útiles (HATEOAS)
            'links' => [
                'self' => route('api.v1.branches.show', $this->id),
                'users' => route('api.v1.branches.users', $this->id),
                'edit' => route('api.v1.branches.update', $this->id),
                'delete' => route('api.v1.branches.destroy', $this->id),
            ],
        ];
    }
}