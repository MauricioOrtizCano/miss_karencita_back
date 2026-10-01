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
            'caregiverName' => 'required|string|max:255',
            'caregiverWhatsapp' => 'required|string|max:20',
            'caregiverEmail' => 'nullable|email|max:255|unique:guardians,email',
            'caregiverDataAuthorization' => 'required|accepted',
            'children' => 'required|array|min:1',
            'children.*.name' => 'required|string|max:255',
            'children.*.ageRange' => 'required|in:0-6,7-12,13-24,25-36',
            'children.*.gender' => 'nullable|string|max:20',
            'children.*.message' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'caregiverName.required' => 'El nombre del cuidador es obligatorio.',
            'caregiverWhatsapp.required' => 'El número de WhatsApp del cuidador es obligatorio.',
            'caregiverEmail.email' => 'El correo electrónico del cuidador debe ser una dirección de correo válida.',
            'caregiverEmail.unique' => 'Este correo ya está registrado, ya recibimos tus datos',
            'caregiverDataAuthorization.required' => 'La autorización de datos es obligatoria.',
            'children.required' => 'Se requiere al menos un hijo.',
            'children.*.name.required' => 'El nombre del hijo es obligatorio.',
            'children.*.ageRange.required' => 'El rango de edad del hijo es obligatorio.',
        ];
    }

    
}
