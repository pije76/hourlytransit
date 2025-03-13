 @if (count($hourlyPrices) > 0)
   <ul class="list-group custom-radio">
     @foreach ($hourlyPrices as $hourlyPrice)
       @php
         $price = App\Models\BookingHour::find($hourlyPrice->hour_id);
       @endphp
       <li>
         <input class="input-radio" type="radio" name="price" id="radio_{{ $hourlyPrice->id }}"
           value="{{ $hourlyPrice->id }}">
         <label class="form-radio-label" for="radio_{{ $hourlyPrice->id }}">
           <span> {{ $price->hour }} {{ __('Hrs') }}</span>
           <span class="qty"> {{ symbolPrice($hourlyPrice->price) }}</span>
         </label>
       </li>
     @endforeach
   </ul>
 @else
   <h6 class="mt-2 text-warning ps-3 pb-2">{{ __('No booking slot available') }}</h6>
 @endif
