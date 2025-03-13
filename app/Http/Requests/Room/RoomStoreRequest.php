<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Amenitie;
use App\Models\BookingHour;
use App\Models\Language;
use App\Models\RoomContent;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;

class RoomStoreRequest extends FormRequest
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
    public function rules(Request  $request)
    {
        if ($request->vendor_id == 0) {

            $rules = [
                'slider_images' => 'required',
                'feature_image' => [
                    'required',
                    new ImageMimeTypeRule()
                ],
                'status' => 'required',
                'adult' => 'required|integer|min:1',
                'area' => 'nullable|integer',
                'children' => 'required|integer|min:0',
                'bed' => 'required|integer|min:0',
                'bathroom' =>   'required|integer|min:0',
                'number_of_rooms_of_this_same_type' => 'required|integer',
                'preparation_time' => 'required|integer',
                'hotel_id' => 'required',
                'prices' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (collect($value)->filter()->isEmpty()) {
                            $fail(__('At least one price must be provided.'));
                        }
                    },
                ],
                'prices.*' => ['nullable', 'numeric'],

            ];


            $languages = Language::all();

            foreach ($languages as $language) {

                $hasExistingContent = RoomContent::where('language_id', $language->id)->where('room_id', $this->id)->exists();
                $code = $language->code;

                if (
                    $hasExistingContent ||
                    $language->is_default == 1 ||
                    $this->filled($code . '_title') ||
                    $this->filled($code . '_room_category') ||
                    $this->filled($code . '_description') ||
                    $this->filled($code . '_amenities') ||
                    $this->filled($code . '_meta_keyword') ||
                    $this->filled($code . '_meta_description')
                ) {

                    $amenitiesCount = Amenitie::where('language_id', $language->id)->count();

                    $rules[$code . '_title'] = 'required|max:255';
                    $rules[$code . '_room_category'] = 'required';
                    $rules[$code . '_description'] = 'required|min:15';
                    $rules[$code . '_amenities'] = ($amenitiesCount > 0 ? 'required' : 'sometimes') . '|array';
                }
            }

            return $rules;
        } else {
            $vendorId = $request->vendor_id;

            $packagePermission = VendorPermissionHelper::packagePermission($vendorId);
            if ($packagePermission != []) {

                $roomImageLimit = packageTotalRoomImage($vendorId);
                $permissions = currentPackageFeatures($vendorId);
                $amenitiesLimit = packageTotalRoomAmenities($vendorId);


                if (!empty(currentPackageFeatures($vendorId))) {
                    $permissions = json_decode($permissions, true);
                }

                $rules = [
                    'slider_images' => 'required|array|max:' . $roomImageLimit,
                    'feature_image' => [
                        'required',
                        new ImageMimeTypeRule()
                    ],
                    'status' => 'required',
                    'adult' => 'required|integer|min:1',
                    'area' => 'nullable|integer',
                    'children' => 'required|integer|min:0',
                    'bed' => 'required|integer|min:0',
                    'bathroom' =>   'required|integer|min:0',
                    'number_of_rooms_of_this_same_type' => 'required|integer',
                    'preparation_time' => 'required|integer',
                    'hotel_id' => 'required',
                    'prices' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            if (collect($value)->filter()->isEmpty()) {
                                $fail(__('At least one price must be provided.'));
                            }
                        },
                    ],
                    'prices.*' => ['nullable', 'numeric'],
                ];

                $languages = Language::all();

                foreach ($languages as $language) {
                    $hasExistingContent = RoomContent::where('language_id', $language->id)->where('room_id', $this->id)->exists();
                    $code = $language->code;

                    if (
                        $hasExistingContent ||
                        $language->is_default == 1 ||
                        $this->filled($code . '_title') ||
                        $this->filled($code . '_room_category') ||
                        $this->filled($code . '_description') ||
                        $this->filled($code . '_amenities') ||
                        $this->filled($code . '_meta_keyword') ||
                        $this->filled($code . '_meta_description')
                    ) {

                        $amenitiesCount = Amenitie::where('language_id', $language->id)->count();

                        $rules[$code . '_title'] = 'required|max:255';
                        $rules[$code . '_room_category'] = 'required';
                        $rules[$code . '_description'] = 'required|min:15';
                        $rules[$code . '_amenities'] = ($amenitiesCount > 0 ? 'required' : 'sometimes') . '|array|max:' . $amenitiesLimit;
                    }
                }

                return $rules;
            }
        }
    }

    public function messages()
    {
        $messageArray = [];

        $languages = Language::all();


        $bookingHours = BookingHour::orderBy('serial_number', 'asc')->get();

        foreach ($bookingHours as $index => $bookingHour) {
            $messageArray['prices.' . $index . '.required'] = 'rent for ' . $bookingHour->hour . ' hours is required.';
            $messageArray['prices.' . $index . '.numeric'] = 'rent for ' . $bookingHour->hour . ' hours is  must be a number.';
        }


        foreach ($languages as $language) {
            $code = $language->code;
            
            $messageArray[$code . '_title.required'] =
            __('The title field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_title.max'] = __('The title field cannot contain more than 255 characters for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_title.required'] = __('The title field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_room_category.required'] = __('The category field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_description.required'] = __('The description field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_description.min'] = __('The description field must have at least 15 characters for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_amenities.required'] = __('The amenities field is required for') . ' ' . $language->name . ' ' . __('language') . '.';
            $messageArray[$code . '_amenities.max'] = __('The amenities field for') . ' ' . $language->name . ' ' . __('language must not have more than') . ' ' . $this->aminitiesLimit() . ' ' . __('items') . '.';

        }

        return $messageArray;
    }
    private function aminitiesLimit()
    {
        $vendorId = $this->vendor_id;
        if ($vendorId == 0) {
            return 999999;
        } else {
            return  packageTotalRoomAmenities($vendorId);
        }
    }
}
