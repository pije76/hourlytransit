<?php

namespace App\Http\Helpers;

use App\Models\BasicSettings\Basic;
use App\Models\Hotel;
use App\Models\HotelContent;
use App\Models\Language;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Room;
use App\Models\RoomContent;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class VendorPermissionHelper
{

  public static function packagePermission(int $vendor_id)
  {
    $bs = Basic::first();
    Config::set('app.timezone', $bs->timezone);

    $currentPackage = Membership::query()->where([
      ['vendor_id', '=', $vendor_id],
      ['status', '=', 1],
      ['start_date', '<=', Carbon::now()->format('Y-m-d')],
      ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
    ])->first();
    $package = isset($currentPackage) ? Package::query()->find($currentPackage->package_id) : null;
    return $package ? $package : collect([]);
  }

  public static function uniqidReal($lenght = 13)
  {
    $bs = Basic::first();
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
      $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
      $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
      throw new Exception("no cryptographically secure random function available");
    }
    return substr(bin2hex($bytes), 0, $lenght);
  }

  public static function currentPackagePermission(int $userId)
  {
    $bs = Basic::first();
    Config::set('app.timezone', $bs->timezone);
    $currentPackage = Membership::query()->where([
      ['vendor_id', '=', $userId],
      ['status', '=', 1],
      ['start_date', '<=', Carbon::now()->format('Y-m-d')],
      ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
    ])->first();
    return isset($currentPackage) ? Package::query()->findOrFail($currentPackage->package_id) : null;
  }
  public static function userPackage(int $userId)
  {
    $bs = Basic::first();
    Config::set('app.timezone', $bs->timezone);

    return Membership::query()->where([
      ['vendor_id', '=', $userId],
      ['status', '=', 1],
      ['start_date', '<=', Carbon::now()->format('Y-m-d')],
      ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
    ])->first();
  }

  public static function currPackageOrPending($userId)
  {

    $currentPackage = Self::currentPackagePermission($userId);
    if (!$currentPackage) {
      $currentPackage = Membership::query()->where([
        ['vendor_id', '=', $userId],
        ['status', 0]
      ])->whereYear('start_date', '<>', '9999')->orderBy('id', 'DESC')->first();
      $currentPackage = isset($currentPackage) ? Package::query()->findOrFail($currentPackage->package_id) : null;
    }
    return isset($currentPackage) ? $currentPackage : null;
  }

  public static function currMembOrPending($userId)
  {
    $currMemb = Self::userPackage($userId);
    if (!$currMemb) {
      $currMemb = Membership::query()->where([
        ['vendor_id', '=', $userId],
        ['status', 0],
      ])->whereYear('start_date', '<>', '9999')->orderBy('id', 'DESC')->first();
    }
    return isset($currMemb) ? $currMemb : null;
  }

  public static function hasPendingMembership($userId)
  {
    $count = Membership::query()->where([
      ['vendor_id', '=', $userId],
      ['status', 0]
    ])->whereYear('start_date', '<>', '9999')->count();
    return $count > 0 ? true : false;
  }

  public static function nextPackage(int $userId)
  {
    $bs = Basic::first();
    Config::set('app.timezone', $bs->timezone);
    $currMemb = Membership::query()->where([
      ['vendor_id', $userId],
      ['start_date', '<=', Carbon::now()->toDateString()],
      ['expire_date', '>=', Carbon::now()->toDateString()]
    ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999');
    $nextPackage = null;
    if ($currMemb->first()) {
      $countCurrMem = $currMemb->count();
      if ($countCurrMem > 1) {
        $nextMemb = $currMemb->orderBy('id', 'DESC')->first();
      } else {
        $nextMemb = Membership::query()->where([
          ['vendor_id', $userId],
          ['start_date', '>', $currMemb->first()->expire_date]
        ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->first();
      }
      $nextPackage = $nextMemb ? Package::query()->where('id', $nextMemb->package_id)->first() : null;
    }
    return $nextPackage;
  }

  public static function nextMembership(int $userId)
  {
    $bs = Basic::first();
    Config::set('app.timezone', $bs->timezone);
    $currMemb = Membership::query()->where([
      ['vendor_id', $userId],
      ['start_date', '<=', Carbon::now()->toDateString()],
      ['expire_date', '>=', Carbon::now()->toDateString()]
    ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999');
    $nextMemb = null;
    if ($currMemb->first()) {
      $countCurrMem = $currMemb->count();
      if ($countCurrMem > 1) {
        $nextMemb = $currMemb->orderBy('id', 'DESC')->first();
      } else {
        $nextMemb = Membership::query()->where([
          ['vendor_id', $userId],
          ['start_date', '>', $currMemb->first()->expire_date]
        ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->first();
      }
    }
    return $nextMemb;
  }

  public static function packagesDowngraded($vendorId)
  {
    $userCurrentPackage =  VendorPermissionHelper::currentPackagePermission($vendorId);
    $defLanguage = Language::query()->where('is_default', '=', 1)->first();

    $hotelImgDown = $roomImgDown = $featureDown = $socialLinkDown = $amenitieDown = $hotelAmenitieDown = $roomAmenitieDown =  false;
    $hotelImgHotelContents = $ProductImgContents = $hotelamenitiehotelContents = $roomamenitiehotelContents = $roomImgRoomContents = null;

    if ($userCurrentPackage) {

      //hotel img downgrade
      $hotelImage = Hotel::with(['hotel_galleries'])->where('vendor_id', $vendorId)->get();
      if ($hotelImage) {
        foreach ($hotelImage as $hotel) {
          $pimages = $hotel->hotel_galleries;

          if ($userCurrentPackage->number_of_images_per_hotel < count($pimages)) {
            $hotelImgDown = true;
            break;
          }
        }

        $hotelImgIds = [];
        foreach ($hotelImage as $hotel) {
          $pimages = $hotel->hotel_galleries;

          if ($userCurrentPackage->number_of_images_per_hotel < count($pimages)) {

            if (!in_array($hotel->id, $hotelImgIds)) {
              array_push($hotelImgIds, $hotel->id);
            }
          }
        }
        $image = "ok";
        $hotelImgHotelContents = HotelContent::join('hotels', 'hotels.id', '=', 'hotel_contents.hotel_id')
          ->where('hotel_contents.language_id', $defLanguage->id)
          ->when($image, function ($query) use ($hotelImgIds) {
            return $query->whereIn('hotels.id', $hotelImgIds);
          })
          ->select('hotels.id', 'hotel_contents.title')
          ->orderBy('hotels.id', 'asc')
          ->get();
      }

      //room img downgrade
      $roomImg = Room::with(['room_galleries'])->where('vendor_id', $vendorId)->get();
      if ($roomImg) {
        foreach ($roomImg as $room) {
          $pimages = $room->room_galleries;

          if ($userCurrentPackage->number_of_images_per_room < count($pimages)) {
            $roomImgDown = true;
            break;
          }
        }

        $roomImgIds = [];
        foreach ($roomImg as $room) {
          $pimages = $room->room_galleries;

          if ($userCurrentPackage->number_of_images_per_room < count($pimages)) {

            if (!in_array($room->id, $roomImgIds)) {
              array_push($roomImgIds, $room->id);
            }
          }
        }
        $image = "ok";
        $roomImgRoomContents = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
          ->where('room_contents.language_id', $defLanguage->id)
          ->when($image, function ($query) use ($roomImgIds) {
            return $query->whereIn('rooms.id', $roomImgIds);
          })
          ->select('rooms.id', 'room_contents.title')
          ->orderBy('rooms.id', 'asc')
          ->get();
      }




      // // check hotel amenities are down graded
      $hotelAmenities = Hotel::with('hotel_contents')->where('vendor_id', $vendorId)->select('id')->get();
      if ($hotelAmenities) {
        foreach ($hotelAmenities as $hotelAmenitie) {

          foreach ($hotelAmenitie->hotel_contents as $content) {
            if ($content->amenities) {
              $amenities = json_decode($content->amenities);

              if ($userCurrentPackage->number_of_amenities_per_hotel < count($amenities)) {
                $hotelAmenitieDown = true;
                break;
              }
            }
          }
        }

        $hotelAminitiesIds = [];
        foreach ($hotelAmenities as $hotelAmenitie) {

          foreach ($hotelAmenitie->hotel_contents as $content) {
            if ($content->amenities) {
              $amenities = json_decode($content->amenities);

              if ($userCurrentPackage->number_of_amenities_per_hotel < count($amenities)) {
                if (!in_array($hotelAmenitie->id, $hotelAminitiesIds)) {
                  array_push($hotelAminitiesIds, $hotelAmenitie->id);
                }
              }
            }
          }
        }

        $amenitie = "ok";
        $hotelamenitiehotelContents = HotelContent::join('hotels', 'hotels.id', '=', 'hotel_contents.hotel_id')
          ->where('hotel_contents.language_id', $defLanguage->id)
          ->when($amenitie, function ($query) use ($hotelAminitiesIds) {
            return $query->whereIn('hotels.id', $hotelAminitiesIds);
          })
          ->select('hotels.id', 'hotel_contents.title')
          ->orderBy('hotels.id', 'asc')
          ->get();
      }
      // check room amenities are down graded
      $roomAmenities = Room::with('room_content')->where('vendor_id', $vendorId)->select('id')->get();
      if ($roomAmenities) {
        foreach ($roomAmenities as $roomAmenitie) {

          foreach ($roomAmenitie->room_content as $content) {
            if ($content->amenities) {
              $amenities = json_decode($content->amenities);

              if ($userCurrentPackage->number_of_amenities_per_room < count($amenities)) {
                $roomAmenitieDown = true;
                break;
              }
            }
          }
        }

        $roomAminitiesIds = [];
        foreach ($roomAmenities as $roomAmenitie) {

          foreach ($roomAmenitie->room_content as $content) {
            if ($content->amenities) {
              $amenities = json_decode($content->amenities);

              if ($userCurrentPackage->number_of_amenities_per_room < count($amenities)) {
                if (!in_array($roomAmenitie->id, $roomAminitiesIds)) {
                  array_push($roomAminitiesIds, $roomAmenitie->id);
                }
              }
            }
          }
        }

        $amenitie = "ok";
        $roomamenitiehotelContents = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
          ->where('room_contents.language_id', $defLanguage->id)
          ->when($amenitie, function ($query) use ($roomAminitiesIds) {
            return $query->whereIn('rooms.id', $roomAminitiesIds);
          })
          ->select('rooms.id', 'room_contents.title')
          ->orderBy('rooms.id', 'asc')
          ->get();
      }
    }

    return [
      'hotelImgDown' => $hotelImgDown,
      'roomImgDown' => $roomImgDown,
      'hotelImgHotelContents' => $hotelImgHotelContents,
      'hotelamenitiehotelContents' => $hotelamenitiehotelContents,
      'roomamenitiehotelContents' => $roomamenitiehotelContents,
      'roomImgRoomContents' => $roomImgRoomContents,
      'featureDown' => $featureDown,
      'socialLinkDown' => $socialLinkDown,
      'amenitieDown' => $amenitieDown,
      'hotelAmenitieDown' => $hotelAmenitieDown,
      'roomAmenitieDown' => $roomAmenitieDown,
      'ProductImgContents' => $ProductImgContents,
    ];
  }
}
