<?php 
namespace Omdasoft\LaravelWebauthn\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterOptionsRequest extends FormRequest{
     /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user' => 'required',
        ];
    }
}