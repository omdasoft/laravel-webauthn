<?php 
namespace Omdasoft\LaravelWebauthn\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest{
     /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'challenge_id' => 'required|string',
            'passkey' => 'required|array'
        ];
    }
}