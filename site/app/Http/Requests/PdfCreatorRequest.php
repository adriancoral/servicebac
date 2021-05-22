<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PdfCreatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->isJson();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content' =>  ['required', 'array', 'min:1'],
            "content.*"  => ['required'],
            'templates' =>  ['required', 'array', 'min:1'],
            "templates.*"  => ['required', 'url', 'distinct', 'min:1'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'url', 'distinct', 'min:1'],
            'callback' => ['nullable', 'url']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'content.required' => __('A content field is required'),
            'content.array' => __('A content field must by an array'),
            'templates.required' => __('A templates field is required'),
            'templates.array' => __('A templates field must by an array'),
            'templates.*.url' => __('A templates values must by an url'),
            'templates.*.distinct' => __('A templates values must by unique'),
            'attachments.array' => __('A attachments field must by an array'),
            'attachments.*.url' => __('A attachments values must by an url'),
            'callback.url' => 'A callback field must by an url'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    /*public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
    }*/
}
