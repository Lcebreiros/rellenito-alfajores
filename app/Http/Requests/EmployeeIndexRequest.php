<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cambiá según tu política: por ejemplo, solo company/master
        return $this->user() && ($this->user()->isCompany() || $this->user()->isMaster());
    }

    public function rules(): array
    {
        return [
            'q'         => ['nullable','string','max:255'],
            'branch_id' => ['nullable','integer','exists:branches,id'],
            'role'      => ['nullable','string','max:100'],
            'has_computer' => ['nullable','boolean'],
            'start_from' => ['nullable','date'],
            'start_to'   => ['nullable','date'],
            'per_page'  => ['nullable','integer','in:25,50,100'],
            'cursor'    => ['nullable','string'],
            'order'     => ['nullable','in:asc,desc'],
        ];
    }

    /** Normalizar filtros y valores por defecto */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => $this->input('per_page', 50),
            'order'    => $this->input('order', 'desc'),
        ]);
    }
}
