<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class RoomRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $request = request();
        $rules = [];
        if (request()->isMethod('get')) {
            $rules['id'] = 'required|exists:\App\Model\Room,id';
        }
        if (request()->isMethod('post')) {
            // $rules['id'] = 'required|min:3|max:255|regex:/^\S*$/|unique:\App\Model\Room,id';
            $rules['number'] = [
                'required',
                'numeric',
                'min:1',
                // 'max:2',
                'regex:/^\S*$/',
                // 'unique:\App\Model\Room,number,'.request()->id
                Rule::unique('rooms')->where(function ($query) use($request) {
                    return $query->where('group',$request->group);
                }),
            ];;
        }
        if (request()->isMethod('put')) {
            $rules['id'] = 'required|exists:App\Model\Room,id';
            // $rules['code_old'] = 'required|min:3|max:255|regex:/^\S*$/|exists:App\Model\Room,code';
            $rules['number'] = [
                'required',
                'numeric',
                'min:1',
                // 'max:2',
                'regex:/^\S*$/',
                // 'unique:\App\Model\Room,number,'.request()->id
                Rule::unique('rooms')->where(function ($query) use($request) {
                    return $query->where('id',"!=" ,$request->id)
                    ->where('group', $request->group);
                }),
            ];
        }
        if(request()->isMethod('post') || request()->isMethod('put')){
            // $rules['location_id'] = 'nullable|exists:App\Model\Location,id';
            $rules['title'] = 'nullable|max:255';
            $rules['group'] = 'required|in:Apartment,Villa';
            // $rules['number'] = 'required|exists:App\Model\Room,number';

            // $rules['grup'] = 'required|max:10';            
            // $rules['rates'] = 'sometimes|required|numeric';

        }
        return $rules;
    }

    public function messages()
    {
        return [
            'group.required' => 'Group tidak boleh kosong',
            'group.in' => 'Group harus di pilih',

            'id.required' => 'ID Lama tidak boleh kosong',
            // 'id.min' => 'Kode Lama tidak boleh kurang dari 3 karakter',
            // 'id.max' => 'Kode Lama tidak boleh lebih dari 255 karakter',
            // 'id.regex' => 'Kode Lama tidak boleh ada spasi',
            // 'id.unique' => 'Kode Lama sudah digunakan',
            'id.exists' => 'ID Lama tidak terdaftar',

            // 'code.required' => 'Kode tidak boleh kosong',
            // 'code.min' => 'Kode tidak boleh kurang dari 3 karakter',
            // 'code.max' => 'Kode tidak boleh lebih dari 255 karakter',
            // 'code.regex' => 'Kode tidak boleh ada spasi',
            // 'code.unique' => 'Kode sudah digunakan',
            // 'code.exists' => 'Kode tidak terdaftar',

            'number.required' => 'Nomor tidak boleh kosong',
            'number.min' => 'Nomor tidak boleh kurang dari 1 karakter',
            'number.max' => 'Nomor tidak boleh lebih dari 2 karakter',
            'number.unique' => 'Nomor sudah digunakan',
            'number.numeric' => 'Nomor  harus berupa angka',

            // 'number.exists' => 'Nomor tidak terdaftar',
            
            // 'rates.numeric' => 'Rates harus berupa angka',

            // 'title.required' => 'Title tidak boleh kosong',
            'title.max' => 'Title tidak boleh lebih dari 255 karakter',
        ];
    }
}
