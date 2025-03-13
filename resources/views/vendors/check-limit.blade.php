 @php
   $vendorId = Auth::guard('vendor')->user()->id;
   $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);
   if (!empty($current_package) && !empty($current_package->features)) {
       $permissions = json_decode($current_package->features, true);
   } else {
       $permissions = null;
   }

 @endphp
 @if ($current_package != '[]')

   <div class="modal fade" id="checkLimitModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
         <div class="modal-header">
           <h3 class="modal-title card-title" id="exampleModalLabel">
             {{ __('All Limit') }}</h3>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           @php
             $hotelCanAdd = packageTotalHotel($vendorId) - vendorTotalAddedHotel($vendorId);
             $roomCanAdd = packageTotalRoom($vendorId) - vendorTotalAddedRoom($vendorId);
           @endphp
           <div class="alert alert-warning">
             <span
               class="text-warning">{{ __('If any feature has crossed its current subscription package\'s limit, then you won\'t be able to add/edit any other feature.') }}</span>
           </div>
           <ul class="list-group list-group-bordered">

             <li class="list-group-item">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   @if ($hotelCanAdd < 0)
                     <i class="fas fa-exclamation-circle text-danger"></i>
                   @endif
                   {{ __('Hotels Left') }} :
                 </span>

                 <span class="badge badge-primary badge-sm">
                   {{ $current_package->number_of_hotel - vendorTotalAddedHotel($vendorId) >= 999999 ? __('Unlimited') : ($current_package->number_of_hotel - vendorTotalAddedHotel($vendorId) < 0 ? 0 : $current_package->number_of_hotel - vendorTotalAddedHotel($vendorId)) }}
                 </span>
               </div>

               @if (vendorTotalAddedHotel($vendorId) > $current_package->number_of_hotel)
                 <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                   {{ abs($current_package->number_of_hotel - vendorTotalAddedHotel($vendorId)) }}
                   {{ abs($current_package->number_of_hotel - vendorTotalAddedHotel($vendorId)) == 1 ? __('hotel') : __('hotels') }}
                 </p>
               @endif
               @if (vendorTotalAddedHotel($vendorId) == $current_package->number_of_hotel)
                 <p class="text-info m-0">{{ __('You reach your limit') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item ">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   @if ($hotelImgDown)
                     <i class="fas fa-exclamation-circle text-danger"></i>
                   @endif
                   {{ __('Hotel Images (Per Hotel)') }} :
                 </span>
                 @if ($hotelImgDown)
                   <button type="button" class="btn  btn-danger mr-2  btn-sm btn-round" data-toggle="modal"
                     data-target="#hotelImgDownModal">
                     {{ __('Remove') }}
                   </button>
                 @else
                   <span class="badge badge-primary badge-sm">
                     {{ $current_package->number_of_images_per_hotel }}
                   </span>
                 @endif
               </div>
               @if ($hotelImgDown)
                 <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}

                   {{ __('gallery images') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item ">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   @if ($hotelAmenitieDown)
                     <i class="fas fa-exclamation-circle text-danger"></i>
                   @endif
                   {{ __('Amenities (Per Hotel)') }} :
                 </span>
                 @if ($hotelAmenitieDown)
                   <button type="button" class="btn  btn-danger mr-2  btn-sm btn-round" data-toggle="modal"
                     data-target="#hotelAmenitiesDownModal">
                     {{ __('Remove') }}
                   </button>
                 @else
                   <span class="badge badge-primary badge-sm">
                     {{ $current_package->number_of_amenities_per_hotel }}
                   </span>
                 @endif
               </div>
               @if ($hotelAmenitieDown)
                 <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete Amenities') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   @if ($roomCanAdd < 0)
                     <i class="fas fa-exclamation-circle text-danger"></i>
                   @endif
                   {{ __('Rooms Left') }} :
                 </span>

                 <span class="badge badge-primary badge-sm">
                   {{ $current_package->number_of_room - vendorTotalAddedRoom($vendorId) >= 999999 ? __('Unlimited') : ($current_package->number_of_room - vendorTotalAddedRoom($vendorId) < 0 ? 0 : $current_package->number_of_room - vendorTotalAddedRoom($vendorId)) }}
                 </span>
               </div>

               @if (vendorTotalAddedRoom($vendorId) > $current_package->number_of_room)
                 <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}
                   {{ abs($current_package->number_of_room - vendorTotalAddedRoom($vendorId)) }}
                   {{ abs($current_package->number_of_room - vendorTotalAddedRoom($vendorId)) == 1 ? 'room' : 'rooms' }}
                 </p>
               @endif
               @if (vendorTotalAddedRoom($vendorId) == $current_package->number_of_room)
                 <p class="text-info m-0">{{ __('You reach your limit') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item ">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   @if ($roomImgDown)
                     <i class="fas fa-exclamation-circle text-danger"></i>
                   @endif
                   {{ __('Room Images (Per Room)') }} :
                 </span>
                 @if ($roomImgDown)
                   <button type="button" class="btn  btn-danger mr-2  btn-sm btn-round" data-toggle="modal"
                     data-target="#roomImgDownModal">
                     {{ __('Remove') }}
                   </button>
                 @else
                   <span class="badge badge-primary badge-sm">
                     {{ $current_package->number_of_images_per_hotel }}
                   </span>
                 @endif
               </div>
               @if ($roomImgDown)
                 <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete') }}

                   {{ __('gallery images') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item ">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   @if ($roomAmenitieDown)
                     <i class="fas fa-exclamation-circle text-danger"></i>
                   @endif
                   {{ __('Amenities (Per Room)') }} :
                 </span>
                 @if ($roomAmenitieDown)
                   <button type="button" class="btn  btn-danger mr-2  btn-sm btn-round" data-toggle="modal"
                     data-target="#roomAmenitiesDownModal">
                     {{ __('Remove') }}
                   </button>
                 @else
                   <span class="badge badge-primary badge-sm">
                     {{ $current_package->number_of_amenities_per_room }}
                   </span>
                 @endif
               </div>
               @if ($roomAmenitieDown)
                 <p class="text-warning m-0">{{ __('Limit has been crossed, you have to delete Amenities') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item">
               <div class="d-flex  justify-content-between">
                 <span class="text-focus">
                   {{ __('Bookings Left') }} :
                 </span>

                 <span class="badge badge-primary badge-sm">
                   {{ $current_package->number_of_bookings - vendorTotalBooking($vendorId) >= 999999 ? __('Unlimited') : ($current_package->number_of_bookings - vendorTotalBooking($vendorId) < 0 ? 0 : $current_package->number_of_bookings - vendorTotalBooking($vendorId)) }}
                 </span>
               </div>
               @if (vendorTotalBooking($vendorId) >= $current_package->number_of_bookings)
                 <p class="text-info m-0">{{ __('You reach your limit') }}
                 </p>
               @endif
             </li>

             <li class="list-group-item  border  d-flex   justify-content-between">
               <span>{{ __('Add Booking From Dashboard') }}: </span>
               @if (is_array($permissions) && in_array('Add Booking From Dashboard', $permissions))
                 <span class="mx-2 d-inline-block badge badge-success badge-pill">
                   {{ __('Enabled') }}</span>
               @else
                 <span class="badge badge-danger">{{ __('Disabled') }}</span>
               @endif
             </li>
             <li class="list-group-item  border  d-flex   justify-content-between">
               <span>{{ __('Edit Booking From Dashboard') }}: </span>
               @if (is_array($permissions) && in_array('Edit Booking From Dashboard', $permissions))
                 <span class="mx-2 d-inline-block badge badge-success badge-pill">
                   {{ __('Enabled') }}</span>
               @else
                 <span class="badge badge-danger">{{ __('Disabled') }}</span>
               @endif
             </li>
             <li class="list-group-item  border  d-flex   justify-content-between">
               <span>{{ __('Support Tickets') }}: </span>
               @if (is_array($permissions) && in_array('Support Tickets', $permissions))
                 <span class="mx-2 d-inline-block badge badge-success badge-pill">
                   {{ __('Enabled') }}</span>
               @else
                 <span class="badge badge-danger">{{ __('Disabled') }}</span>
               @endif
             </li>
           </ul>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary"
             data-dismiss="modal">{{ $keywords['Close'] ?? __('Close') }}</button>
         </div>
       </div>
     </div>
   </div>


   <div class="modal fade" id="hotelImgDownModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
         <div class="modal-header">
           <h3 class="modal-title card-title" id="exampleModalLabel">
             {{ __('Remove Image from the below hotels') }}</h3>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           <ul class="list-group list-group-bordered">
             @foreach ($hotelImgHotelContents as $hotel)
               <li class="list-group-item p-0">
                 <a href="{{ route('vendor.hotel_management.edit_hotel', ['id' => $hotel->id]) }}"
                   class="dropdown-item">
                   <div class="d-flex">
                     <span>
                       {{ strlen(@$hotel->title) > 50 ? mb_substr(@$hotel->title, 0, 50, 'utf-8') . '.....' : @$hotel->title }}
                     </span>
                     <span>
                       <i class="far fa-link"></i>
                     </span>
                   </div>
                 </a>
               </li>
             @endforeach
           </ul>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary"
             data-dismiss="modal">{{ $keywords['Close'] ?? __('Close') }}</button>
         </div>
       </div>
     </div>
   </div>
   <div class="modal fade" id="roomImgDownModal" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
         <div class="modal-header">
           <h3 class="modal-title card-title" id="exampleModalLabel">
             {{ __('Remove Image from the below hotels') }}</h3>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           <ul class="list-group list-group-bordered">
             @foreach ($roomImgRoomContents as $room)
               <li class="list-group-item p-0">
                 <a href="{{ route('vendor.room_management.edit_room', ['id' => $room->id]) }}"
                   class="dropdown-item">
                   <div class="d-flex">
                     <span>
                       {{ strlen(@$room->title) > 50 ? mb_substr(@$room->title, 0, 50, 'utf-8') . '.....' : @$room->title }}
                     </span>
                     <span>
                       <i class="far fa-link"></i>
                     </span>
                   </div>
                 </a>
               </li>
             @endforeach
           </ul>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary"
             data-dismiss="modal">{{ $keywords['Close'] ?? __('Close') }}</button>
         </div>
       </div>
     </div>
   </div>

   <div class="modal fade" id="hotelAmenitiesDownModal" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
         <div class="modal-header">
           <h3 class="modal-title card-title" id="exampleModalLabel">
             {{ __('Remove amenities from the below hotels') }}</h3>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           <ul class="list-group list-group-bordered">
             @foreach ($hotelamenitiehotelContents as $hotel)
               <li class="list-group-item p-0">
                 <a href="{{ route('vendor.hotel_management.edit_hotel', ['id' => $hotel->id]) }}"
                   class="dropdown-item">
                   <div class="d-flex">
                     <span>
                       {{ strlen(@$hotel->title) > 50 ? mb_substr(@$hotel->title, 0, 50, 'utf-8') . '.....' : @$hotel->title }}
                     </span>
                     <span>
                       <i class="far fa-link"></i>
                     </span>
                   </div>
                 </a>
               </li>
             @endforeach
           </ul>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary"
             data-dismiss="modal">{{ $keywords['Close'] ?? __('Close') }}</button>
         </div>
       </div>
     </div>
   </div>

   <div class="modal fade" id="roomAmenitiesDownModal" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
         <div class="modal-header">
           <h3 class="modal-title card-title" id="exampleModalLabel">
             {{ __('Remove amenities from the below rooms') }}</h3>
           <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
           </button>
         </div>
         <div class="modal-body">
           <ul class="list-group list-group-bordered">
             @foreach ($roomamenitiehotelContents as $room)
               <li class="list-group-item p-0">
                 <a href="{{ route('vendor.room_management.edit_room', ['id' => $room->id]) }}"
                   class="dropdown-item">
                   <div class="d-flex">
                     <span>
                       {{ strlen(@$room->title) > 50 ? mb_substr(@$room->title, 0, 50, 'utf-8') . '.....' : @$room->title }}
                     </span>
                     <span>
                       <i class="far fa-link"></i>
                     </span>
                   </div>
                 </a>
               </li>
             @endforeach
           </ul>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary"
             data-dismiss="modal">{{ $keywords['Close'] ?? __('Close') }}</button>
         </div>
       </div>
     </div>
   </div>
 @endif
