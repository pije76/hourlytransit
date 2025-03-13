<?php

namespace App\Http\Requests\Blog;

use App\Models\Language;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
   * @return array
   */
  public function rules()
  {
    $ruleArray = [
      'image' => [
        'required',
        new ImageMimeTypeRule()
      ],
      'serial_number' => 'required|numeric'
    ];

    $languages = Language::all();

    foreach ($languages as $language) {

      $code = $language->code;

      if (
        $language->is_default == 1 ||
        $this->filled($code . '_title') ||
        $this->filled($code . '_category_id') ||
        $this->filled($code . '_author') ||
        $this->filled($code . '_content') ||
        $this->filled($code . '_meta_keywords') ||
        $this->filled($code . '_meta_description')
      ) {

        $ruleArray[ $code  . '_title'] = 'required|max:255|unique:blog_informations,title';
        $ruleArray[ $code  . '_category_id'] = 'required';
        $ruleArray[ $code  . '_author'] = 'required|max:255';
        $ruleArray[ $code  . '_content'] = 'min:30';
      }
    }

    return $ruleArray;
  }

  public function messages()
  {
    $messageArray = [];

    $languages = Language::all();

    foreach ($languages as $language) {
      $code = $language->code;

      $messageArray[ $code  . '_title.required'] = __('The title field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[ $code  . '_title.max'] = __('The title field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[ $code  . '_title.unique'] = __('The title field must be unique for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[ $code  . '_author.required'] = __('The author field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[ $code  . '_author.max'] = __('The author field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[ $code  . '_category_id.required'] = __('The category field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[ $code  . '_content.min'] = __('The content must be at least 30 characters for') . ' ' . $language->name . ' ' . __('language') . '.';
    }

    return $messageArray;
  }
}
