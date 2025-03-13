<?php

namespace App\Http\Requests\Hotel;

use App\Models\Amenitie;
use App\Models\HotelContent;
use App\Models\HotelImage;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Language;
use App\Models\Location\Country;
use App\Models\Location\State;
use Illuminate\Http\Request;
use App\Rules\ImageMimeTypeRule;

class HotelUpdateRequest extends FormRequest
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
    public function rules(Request $request)
    {
        if ($request->vendor_id == 0) {

            $rules = [
                'logo' => [
                    'sometimes',
                    new ImageMimeTypeRule(),
                    'dimensions:width=300,height=300'
                ],
                'status' => 'required',
                'stars' => 'required',
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ];

            $languages = Language::all();

            foreach ($languages as $language) {

                $hasExistingContent = HotelContent::where('language_id', $language->id)->where('hotel_id', $this->id)->exists();
                $code = $language->code;

                $property = $code . '_country_id';

                if ($request->$property) {
                    $Statess = State::where('country_id', $request->$property)->count();
                    if ($Statess != 0) {
                        $State = true;
                    } else {
                        $State = false;
                    }
                } else {
                    $States = State::where('language_id', $language->id)->count();
                    if ($States != 0) {
                        $State = true;
                    } else {
                        $State = false;
                    }
                }

                $countries = Country::where('language_id', $language->id)->count();
                if ($countries != 0) {
                    $country = true;
                } else {
                    $country = false;
                }
                $amenitiesCount = Amenitie::where('language_id', $language->id)->count();


                // Check if any field for this language is filled
                if (
                    $hasExistingContent ||
                    $language->is_default == 1 ||
                    $this->filled($code . '_title') ||
                    $this->filled($code . '_category_id') ||
                    $this->filled($code . '_state_id') ||
                    $this->filled($code . '_country_id') ||
                    $this->filled($code . '_city_id') ||
                    $this->filled($code . '_address') ||
                    $this->filled($code . '_aminities') ||
                    $this->filled($code . '_meta_keyword') ||
                    $this->filled($code . '_meta_description') ||
                    $this->filled($code . '_description')
                ) {

                    $rules[$code . '_title'] = 'required|max:255';
                    $rules[$code . '_category_id'] = 'required';
                    $rules[$code . '_state_id'] = $State ? 'required' : '';
                    $rules[$code . '_country_id'] = $country ? 'required' : '';
                    $rules[$code . '_city_id'] = 'required';
                    $rules[$code . '_address'] = 'required';
                    $rules[$code . '_description'] = 'required|min:15';
                    $rules[$code . '_aminities'] = ($amenitiesCount > 0 ? 'required' : 'sometimes') . '|array';
                }
            }

            return $rules;
        } else {

            $vendorId = $request->vendor_id;
            $hotelImageLimit = packageTotalHotelImage($vendorId);
            $siderImageCount = HotelImage::where('hotel_id', $request->hotel_id)->count();
            $siders = $hotelImageLimit - $siderImageCount;
            $amenitiesLimit = packageTotalHotelAmenities($vendorId);


            $rules = [
                'slider_images' => 'sometimes|array|max:' . $siders,
                'logo' => [
                    'sometimes',
                    new ImageMimeTypeRule(),
                    'dimensions:width=300,height=300'
                ],
                'status' => 'required',
                'stars' => 'required',
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ];

            $languages = Language::all();

            foreach ($languages as $language) {

                $hasExistingContent = HotelContent::where('language_id', $language->id)->where('hotel_id', $this->id)->exists();
                $code = $language->code;

                $property = $code . '_country_id';

                if ($request->$property) {
                    $Statess = State::where('country_id', $request->$property)->count();
                    if ($Statess != 0) {
                        $State = true;
                    } else {
                        $State = false;
                    }
                } else {
                    $States = State::where('language_id', $language->id)->count();
                    if ($States != 0) {
                        $State = true;
                    } else {
                        $State = false;
                    }
                }

                $countries = Country::where('language_id', $language->id)->count();
                if ($countries != 0) {
                    $country = true;
                } else {
                    $country = false;
                }
                $amenitiesCount = Amenitie::where('language_id', $language->id)->count();


                // Check if any field for this language is filled
                if (
                    $hasExistingContent ||
                    $language->is_default == 1 ||
                    $this->filled($code . '_title') ||
                    $this->filled($code . '_category_id') ||
                    $this->filled($code . '_state_id') ||
                    $this->filled($code . '_country_id') ||
                    $this->filled($code . '_city_id') ||
                    $this->filled($code . '_address') ||
                    $this->filled($code . '_aminities') ||
                    $this->filled($code . '_meta_keyword') ||
                    $this->filled($code . '_meta_description') ||
                    $this->filled($code . '_description')
                ) {

                    $rules[$code . '_title'] = 'required|max:255';
                    $rules[$code . '_category_id'] = 'required';
                    $rules[$code . '_state_id'] = $State ? 'required' : '';
                    $rules[$code . '_country_id'] = $country ? 'required' : '';
                    $rules[$code . '_city_id'] = 'required';
                    $rules[$code . '_address'] = 'required';
                    $rules[$code . '_description'] = 'required|min:15';
                    $rules[$code . '_aminities'] = ($amenitiesCount > 0 ? 'required' : 'sometimes') . '|array|max:' . $amenitiesLimit;
                }
            }

            return $rules;
        }
    }
    public function messages()
    {
        $messageArray = [];

        $messageArray['logo.dimensions'] = __('The logo must be exactly 300x300 pixels.');

        $languages = Language::all();

        foreach ($languages as $language) {

            $code = $language->code;

            $messageArray[$code . '_title.required'] =
                __('The title field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_title.max'] = __('The title field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_category_id.required'] = __('The category field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_city_id.required'] = __('The city field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_address.required'] = __('The address field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_state_id.required'] = __('The state field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_country_id.required'] = __('The country field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_description.required'] = __('The description field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_description.min'] = __('The description field must have at least 15 characters for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_aminities.required'] = __('The amenities field is required for') . ' ' . $language->name . ' ' . __('language') . '.';

            $messageArray[$code . '_aminities.max'] = __('The') . ' ' . $language->name . ' ' . __('amenities must not have more than') . ' ' . $this->amenitiesLimit() . ' ' . __('items') . '.';
        }

        return $messageArray;
    }
    private function amenitiesLimit()
    {
        $vendorId = $this->vendor_id;
        if ($vendorId == 0) {
            return PHP_INT_MAX;
        } else {
            return  packageTotalHotelAmenities($vendorId);
        }
    }
}
