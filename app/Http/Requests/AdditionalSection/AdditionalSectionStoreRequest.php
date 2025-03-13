<?php

namespace App\Http\Requests\AdditionalSection;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class AdditionalSectionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $ruleArray = [
            'order' => 'required',
            'serial_number' => 'required|numeric'
        ];

        $defaultLanguage = Language::where('is_default', 1)->first();
        // Default language fields should always be required
        $ruleArray[$defaultLanguage->code . '_name'] = 'required|max:255|unique:custom_section_contents,section_name';
        $ruleArray[$defaultLanguage->code . '_content'] = 'required';


        $languages = Language::all();
        foreach ($languages as $language) {
            $code = $language->code;

            // Skip the default language as it's always required
            if ($language->id == $defaultLanguage->id) {
                continue;
            }

            // Check if any field for this language is filled
            if (
                $this->filled($code . '_name') ||
                $this->filled($code . '_content')
            ) {
                $ruleArray[$code . '_name'] = 'required|max:255|unique:custom_section_contents,section_name';
                $ruleArray[$code . '_content'] = 'min:15';
            }
        }

        return $ruleArray;
    }

    public function messages()
    {
        $messageArray = [];

        $languages = Language::all();

        foreach ($languages as $language) {
            $messageArray[$language->code . '_name.required'] = __('The name field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$language->code . '_name.max'] = __('The name field cannot contain more than 255 characters for') . ' '  . $language->name . ' ' . __('language') . '.';

            $messageArray[$language->code . '_name.unique'] = __('The name field must be unique for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$language->code . '_content.required'] = __('The content field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
        }

        return $messageArray;
    }
}
