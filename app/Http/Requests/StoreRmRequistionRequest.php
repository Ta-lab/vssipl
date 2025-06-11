<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRmRequistionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'part_id'=> 'required',
            'rm_id'=>'required',
            'machine_id'=>'required',
            'group_id'=>'required',
            'req_type_id'=>'required',
            'req_qty'=>'required|min:0'
        ];
    }
}
