<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class RoomActivityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [];
        // if (request()->isMethod('post')) {
        //     // $rules['id'] = 'required|min:3|max:255|regex:/^\S*$/|unique:\App\Model\RoomActivity,id';
        //     $rules['id'] = 'required|regex:/^\S*$/|unique:\App\Model\RoomActivity,id';
        // }
        // if (request()->isMethod('get')) {
        //     $rules['id'] = 'required|regex:/^\S*$/|exists:\App\Model\RoomActivity,id';
        // }
        // if (request()->isMethod('put')) {
        //     // $rules['id'] = 'required|exists:App\Model\RoomActivity,id';
        //     $rules['id_old'] = 'required|regex:/^\S*$/|exists:App\Model\RoomActivity,id';
        //     $rules['id'] = 'required|regex:/^\S*$/|unique:\App\Model\RoomActivity,id,'.request()->id_old;
        //     // $rules['code_old'] = 'required|min:3|max:255|regex:/^\S*$/|exists:App\Model\RoomActivity,code';
        //     // $rules['code'] = 'required|min:3|max:255|regex:/^\S*$/|unique:\App\Model\RoomActivity,code,'.request()->code_old;
        // }
        if(request()->isMethod('post') || request()->isMethod('put')){
            // $rules['created_at'] = 'required';
            // $rules['wwtp_id'] = 'required_without:tenant_id|exists:App\Model\Wwtp,id';
            // $rules['tenant_id'] = 'required_without:wwtp_id|exists:App\Model\Tenant,id';
            // $rules['real_time_val'] = 'required|numeric';
            // $rules['total_val'] = 'required|numeric';
            // $rules['description'] = 'sometimes|max:255';            
        }
        return $rules;
    }

    public function messages()
    {
        return [
            // 'created_at.required' => 'Tanggal dan waktu dibutuhkan',

            // 'wwtp_id.required_without' => 'WWTP ID harus diisi',
            // 'wwtp_id.exists' => 'WWTP ID tidak terdaftar',

            // 'tenant_id.required_without' => 'Tenant ID harus diisi',
            // 'tenant_id.exists' => 'Tenant ID tidak terdaftar',

            // 'real_time_val.required' => 'Nilai Real time tidak boleh kosong',
            // 'real_time_val.numeric' => 'Nilai Real time harus berupa angka',
            
            // 'total_val.required' => 'Nilai Total tidak boleh kosong',
            // 'total_val.numeric' => 'Nilai Total harus berupa angka',
        ];
    }
}
