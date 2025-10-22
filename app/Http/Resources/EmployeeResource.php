<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'full_name'  => "{$this->first_name} {$this->last_name}",
            'email'      => $this->email,
            'dni'        => $this->dni,
            'role'       => $this->role,
            'branch'     => $this->whenLoaded('branch', function () {
                return [
                    'id' => $this->branch->id,
                    'name' => $this->branch->name,
                ];
            }),
            'photo_url'  => $this->photo_url, // accessor en el modelo
            'start_date' => optional($this->start_date)?->toDateString(),
            'has_computer' => (bool) $this->has_computer,
        ];
    }
}
