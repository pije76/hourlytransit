<?php

namespace App\Http\Requests\Blog;

use App\Models\Language;
use App\Models\Journal\BlogInformation;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
      'image' => $this->hasFile('image') ? new ImageMimeTypeRule() : '',
      'serial_number' => 'required|numeric'
    ];

    $languages = Language::all();

    foreach ($languages as $language) {

      $hasExistingContent = BlogInformation::where('language_id', $language->id)->where('blog_id', $this->id)->exists();

      $code = $language->code;

      if (
        $hasExistingContent ||
        $language->is_default == 1 ||
        $this->filled($code . '_title') ||
        $this->filled($code . '_category_id') ||
        $this->filled($code . '_author') ||
        $this->filled($code . '_content') ||
        $this->filled($code . '_meta_keywords') ||
        $this->filled($code . '_meta_description')
      ) {

        $ruleArray[$language->code . '_title'] = [
          'required',
          'max:255',
          Rule::unique('blog_informations', 'title')->ignore($this->id, 'blog_id')
        ];
        $ruleArray[$language->code . '_author'] = 'required|max:255';
        $ruleArray[$language->code . '_category_id'] = 'required';
        $ruleArray[$language->code . '_content'] = 'min:30';
      }
    }
    
    return $ruleArray;
  }

  public function messages()
  {
    $messageArray = [];

    $languages = Language::all();

    foreach ($languages as $language) {
      $messageArray[$language->code . '_title.required'] = __('The title field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[$language->code . '_title.max'] = __('The title field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[$language->code . '_title.unique'] = __('The title field must be unique for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[$language->code . '_author.required'] = __('The author field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[$language->code . '_author.max'] = __('The author field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[$language->code . '_category_id.required'] = __('The category field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

      $messageArray[$language->code . '_content.min'] = __('The content must be at least 30 characters for') . ' ' . $language->name . ' ' . __('language') . '.';
    }

    return $messageArray;
  }
}
