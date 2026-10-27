<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    /**
     * Tentukan apakah request diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validasi data untuk membuat order.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
        ];
    }

    /**
     * Pesan error validasi.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Field user_id wajib diisi',
            'user_id.integer' => 'Field user_id harus berupa angka',
        ];
    }

    /**
     * Response ketika validasi gagal.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Data yang dikirim tidak valid',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}