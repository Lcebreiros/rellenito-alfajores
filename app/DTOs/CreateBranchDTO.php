<?php

namespace App\DTOs;

class CreateBranchDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $contact_email = null,
        public readonly ?int $user_limit = null,
        public readonly bool $is_active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            contact_email: $data['contact_email'] ?? null,
            user_limit: $data['user_limit'] ?? null,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'address' => $this->address,
            'phone' => $this->phone,
            'contact_email' => $this->contact_email,
            'user_limit' => $this->user_limit,
            'is_active' => $this->is_active,
        ];
    }
}

class UpdateBranchDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly ?string $address = null,
        public readonly ?string $phone = null,
        public readonly ?string $contact_email = null,
        public readonly ?int $user_limit = null,
        public readonly bool $is_active = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            contact_email: $data['contact_email'] ?? null,
            user_limit: $data['user_limit'] ?? null,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
            'contact_email' => $this->contact_email,
            'user_limit' => $this->user_limit,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}