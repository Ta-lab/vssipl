<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRawMaterialRequest extends FormRequest
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
            'raw_material_category_id'=> 'required',
            'name'=>'required|unique:raw_materials,name',
            'material_code'=>'required|unique:raw_materials,material_code',
            'minimum_stock'=>'required|min:0'
        ];
    }
}
