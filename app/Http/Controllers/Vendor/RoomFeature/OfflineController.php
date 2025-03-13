<?php

namespace App\Http\Controllers\Vendor\RoomFeature;

use App\Http\Controllers\Controller;
use App\Http\Helpers\BasicMailer;
use App\Http\Helpers\UploadFile;
use App\Models\BasicSettings\Basic;
use App\Models\FeaturedRoomCharge;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\RoomFeature;
use App\Models\Vendor;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class OfflineController extends Controller
{
  public function index(Request $request)
  {
    $gatewayId = $request->gateway;
    $offlineGateway = OfflineGateway::query()->findOrFail($gatewayId);

    $charge = FeaturedRoomCharge::find($request->charge);
    $info = Basic::select('to_mail', 'website_title', 'base_currency_symbol', 'base_currency_symbol_position')->first();

    // validation start
    if ($offlineGateway->has_attachment == 1) {
      $rules = [
        'attachment' => [
          'required',
          new ImageMimeTypeRule()
        ]
      ];

      $message = [
        'attachment.required' => 'Please attach your payment receipt.'
      ];

      $validator = Validator::make($request->only('attachment'), $rules, $message);

      if ($validator->fails()) {
        return Response::json(['errors' => $validator->errors()], 422);
      }
    }
    // validation end

    $directory = public_path('assets/file/attachments/room-feature/');

    // store attachment in local storage
    if ($request->hasFile('attachment')) {
      $attachmentName = UploadFile::store($directory, $request->file('attachment'));
    } else {
      $attachmentName = null;
    }


    $startDate = Carbon::now()->startOfDay();
    $endDate = $startDate->copy()->addDays($charge->days);
    $vendor_mail = Vendor::Find(Auth::guard('vendor')->user()->id);

    if (isset($vendor_mail->to_mail)) {
      $to_mail = $vendor_mail->to_mail;
    } else {
      $to_mail = $vendor_mail->email;
    }

    $order =  RoomFeature::where('room_id', $request->room_id)->first();
    if (empty($order)) {
      $order = new RoomFeature();
    }

    $order->room_id = $request->room_id;
    $order->vendor_id = Auth::guard('vendor')->user()->id;
    $order->vendor_mail = $to_mail;
    $order->order_number = uniqid();
    $order->total = $charge->price;
    $order->payment_method = $offlineGateway->name;
    $order->gateway_type = "offline";
    $order->payment_status = "pending";
    $order->order_status = 'pending';
    $order->attachment = $attachmentName;
    $order->days = $charge->days;
    $order->start_date = $startDate;
    $order->end_date = $endDate;
    $order->currency_symbol = $info->base_currency_symbol;
    $order->currency_symbol_position = $info->base_currency_symbol_position;

    $order->save();

   
    $vendor = Auth::guard('vendor')->user()->username;

    $mailData['subject'] = "$vendor wants to feature a room on $info->website_title";
    $mailBody = "Dear Admin,
            
I hope this email finds you well. I wanted to bring to your attention that $vendor wants to feature a room on our website by.

Thank you for your attention to this matter.";

    $mailData['body'] = nl2br($mailBody);
    $mailData['recipient'] = $info->to_mail;

    BasicMailer::sendMail($mailData);


    return response()->json(['redirectURL' => route('vendor.room_management.room.purchase_feature.offline.success')]);

    // return response()->json('success fully done!');
  }
}
