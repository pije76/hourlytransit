<?php

namespace App\Http\Requests\Room;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BookingProcessRequest extends FormRequest
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
        return [
            'booking_name' => 'required',
            'booking_email' => 'required|email:rfc,dns',
            'booking_phone' => 'required',
            'booking_address' => 'required',
            'identity_number' => request()->input('gateway') == 'iyzico' ? 'required' : '',
            'zip_code' => request()->input('gateway') == 'iyzico' ? 'required' : ''
        ];
    }
    public function messages()
    {
        return [
            'booking_name.required' => 'The first name field is required.',
            'booking_email.required' => 'The email field is required.',
            'booking_phone.required' => 'The phone number field is required.',
            'booking_address.required' => 'The address field is required.',
        ];
    }
    
}
