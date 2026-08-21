<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveLeadRequest extends FormRequest
{
    /**
     * Public Endpoint
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'caregivers.name' => 'required|string|max:255',
            'caregivers.whatsapp' => 'required|string|max:20',
            'caregivers.email' => 'nullable|email|max:255',
            'caregivers.status' => 'nullable|string|max:50',
            'caregivers.dataAuthorization' => 'required|boolean',
            'caregivers.authorizationDate' => 'required_if:caregivers.dataAuthorization,true|nullable|date',
            'caregivers.politicalVersion' => 'required|string|max:255',
            'caregivers.children' => 'required|array|min:1',
            'caregivers.children.*.name' => 'required|string|max:255',
            'caregivers.children.*.ageRange' => 'required|string|max:50',
            'caregivers.children.*.gender' => 'nullable|string|max:20',
            'caregivers.children.*.message' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'caregivers.name.required' => 'El nombre del cuidador es obligatorio.',
            'caregivers.whatsapp.required' => 'El número de WhatsApp del cuidador es obligatorio.',
            'caregivers.email.email' => 'El correo electrónico del cuidador debe ser una dirección de correo válida.',
            'caregivers.dataAuthorization.required' => 'La autorización de datos es obligatoria.',
            'caregivers.authorizationDate.required_if' => 'La fecha de autorización es obligatoria si la autorización de datos es verdadera.',
            'caregivers.politicalVersion.required' => 'La versión política es obligatoria.',
            'caregivers.children.required' => 'Se requiere al menos un hijo.',
            'caregivers.children.*.name.required' => 'El nombre del hijo es obligatorio.',
            'caregivers.children.*.ageRange.required' => 'El rango de edad del hijo es obligatorio.',
        ];
    }

    
}
