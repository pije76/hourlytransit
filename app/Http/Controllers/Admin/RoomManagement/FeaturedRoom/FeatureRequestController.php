<?php

namespace App\Http\Controllers\Admin\RoomManagement\FeaturedRoom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\BasicSettings\Basic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Language;
use App\Models\Room;
use App\Models\RoomContent;
use App\Models\RoomFeature;
use App\Models\VendorInfo;
use Config;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class FeatureRequestController extends Controller
{
    public function index(Request $request)
    {
        $information['langs'] = Language::all();
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $orderNumber = $paymentStatus = $orderStatus = $title = null;

        $roomIds = [];
        if ($request->filled('title')) {
            $title = $request->title;
            $room_contents = RoomContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('room_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $roomIds)) {
                    array_push($roomIds, $room_content);
                }
            }
        }

        if ($request->filled('order_no')) {
            $orderNumber = $request['order_no'];
        }
        if ($request->filled('payment_status')) {
            $paymentStatus = $request['payment_status'];
        }
        if ($request->filled('order_status')) {
            $orderStatus = $request['order_status'];
        }

        $orders = RoomFeature::query()->when($orderNumber, function ($query, $orderNumber) {
            return $query->where('order_number', 'like', '%' . $orderNumber . '%');
        })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('room_features.room_id', $roomIds);
            })
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', '=', $paymentStatus);
            })
            ->when($orderStatus, function ($query, $orderStatus) {
                return $query->where('order_status', '=', $orderStatus);
            })
            ->orderByDesc('id')
            ->paginate(10);

        $information['orders'] = $orders;
        return view('admin.room-management.featured-room.index',  $information);
    }
    public function pending(Request $request)
    {
        $information['langs'] = Language::all();
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $orderNumber = $paymentStatus = $orderStatus = $title = null;

        $roomIds = [];
        if ($request->filled('title')) {
            $title = $request->title;
            $room_contents = RoomContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('room_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $roomIds)) {
                    array_push($roomIds, $room_content);
                }
            }
        }

        if ($request->filled('order_no')) {
            $orderNumber = $request['order_no'];
        }
        if ($request->filled('payment_status')) {
            $paymentStatus = $request['payment_status'];
        }
        if ($request->filled('order_status')) {
            $orderStatus = $request['order_status'];
        }

        $orders = RoomFeature::query()->when($orderNumber, function ($query, $orderNumber) {
            return $query->where('order_number', 'like', '%' . $orderNumber . '%');
        })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('room_features.room_id', $roomIds);
            })
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', '=', $paymentStatus);
            })
            ->when($orderStatus, function ($query, $orderStatus) {
                return $query->where('order_status', '=', $orderStatus);
            })
            ->where('order_status', 'pending')
            ->orderByDesc('id')
            ->paginate(10);

        $information['orders'] = $orders;
        return view('admin.room-management.featured-room.pending',  $information);
    }
    public function approved(Request $request)
    {
        $information['langs'] = Language::all();
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $orderNumber = $paymentStatus = $orderStatus = $title = null;

        $roomIds = [];
        if ($request->filled('title')) {
            $title = $request->title;
            $room_contents = RoomContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('room_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $roomIds)) {
                    array_push($roomIds, $room_content);
                }
            }
        }

        if ($request->filled('order_no')) {
            $orderNumber = $request['order_no'];
        }
        if ($request->filled('payment_status')) {
            $paymentStatus = $request['payment_status'];
        }
        if ($request->filled('order_status')) {
            $orderStatus = $request['order_status'];
        }

        $orders = RoomFeature::query()->when($orderNumber, function ($query, $orderNumber) {
            return $query->where('order_number', 'like', '%' . $orderNumber . '%');
        })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('room_features.room_id', $roomIds);
            })
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', '=', $paymentStatus);
            })
            ->when($orderStatus, function ($query, $orderStatus) {
                return $query->where('order_status', '=', $orderStatus);
            })
            ->where('order_status', 'apporved')
            ->orderByDesc('id')
            ->paginate(10);

        $information['orders'] = $orders;
        return view('admin.room-management.featured-room.approve',  $information);
    }
    public function rejected(Request $request)
    {
        $information['langs'] = Language::all();
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;
        $orderNumber = $paymentStatus = $orderStatus = $title = null;

        $roomIds = [];
        if ($request->filled('title')) {
            $title = $request->title;
            $room_contents = RoomContent::where('language_id', $language->id)
                ->where('title', 'like', '%' . $title . '%')
                ->get()
                ->pluck('room_id');
            foreach ($room_contents as $room_content) {
                if (!in_array($room_content, $roomIds)) {
                    array_push($roomIds, $room_content);
                }
            }
        }

        if ($request->filled('order_no')) {
            $orderNumber = $request['order_no'];
        }
        if ($request->filled('payment_status')) {
            $paymentStatus = $request['payment_status'];
        }
        if ($request->filled('order_status')) {
            $orderStatus = $request['order_status'];
        }

        $orders = RoomFeature::query()->when($orderNumber, function ($query, $orderNumber) {
            return $query->where('order_number', 'like', '%' . $orderNumber . '%');
        })
            ->when($title, function ($query) use ($roomIds) {
                return $query->whereIn('room_features.room_id', $roomIds);
            })
            ->when($paymentStatus, function ($query, $paymentStatus) {
                return $query->where('payment_status', '=', $paymentStatus);
            })
            ->when($orderStatus, function ($query, $orderStatus) {
                return $query->where('order_status', '=', $orderStatus);
            })
            ->where('order_status', 'rejected')
            ->orderByDesc('id')
            ->paginate(10);

        $information['orders'] = $orders;
        return view('admin.room-management.featured-room.rejected',  $information);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $order = RoomFeature::find($id);
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();

        $room = Room::with(['room_content' => function ($query) use ($language) {
            return $query->where('language_id', $language->id);
        }])->where('id', $order->room_id)->first();

        $room_title = $room->room_content[0]->title;
        $slug = $room->room_content[0]->slug;
        $url = route('frontend.room.details', ['slug' => $slug, 'id' => $room->id]);


        $vendor = VendorInfo::Where('vendor_id', $order->vendor_id)->first();

        $info = Basic::select('google_recaptcha_status')->first();
        if ($info->google_recaptcha_status == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $be = Basic::select('smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'to_mail', 'website_title')->firstOrFail();



        if ($request['payment_status'] == 'pending') {

            $order->update([
                'payment_status' => 'pending'
            ]);
        } else if ($request['payment_status'] == 'completed') {

            $order->update([
                'payment_status' => 'completed'
            ]);

            // generate an invoice in pdf format 
            $invoice = $this->generateInvoice($order);

            // then, update the invoice field info in database 
            $order->update(['invoice' => $invoice]);


            //Transactions part
            $earning = Basic::first();

            $earning->total_earning = $earning->total_earning + $order->total;

            $earning->save();

            $after_balance = NULL;
            $pre_balance = NULL;

            $data = [
                'transcation_id' => time(),
                'booking_id' => $order->id,
                'transcation_type' => 'room_feature',
                'user_id' => null,
                'vendor_id' => null,
                'payment_status' => 1,
                'payment_method' => $order->payment_method,
                'grand_total' => $order->total,
                'commission' => $order->total,
                'pre_balance' => $pre_balance,
                'after_balance' => $after_balance,
                'gateway_type' => $order->gateway_type,
                'currency_symbol' => $order->currency_symbol,
                'currency_symbol_position' => $order->currency_symbol_position,
            ];
            store_transaction($data);

            $mail_template = MailTemplate::where('mail_type', 'payment_to_feature_room_accepted_(_offline_payment_gateway_)')->first();

            if ($be->smtp_status == 1) {
                $subject = $mail_template->mail_subject;

                $body = $mail_template->mail_body;
                $body = preg_replace("/{username}/", $vendor->name, $body);
                $body = preg_replace("/{payment_via}/", $order->payment_method, $body);
                $body = preg_replace("/{room_title}/", "<a href=" . $url . ">$room_title</a>", $body);
                $body = preg_replace("/{package_price}/", symbolPrice($order->total), $body);
                $body = preg_replace("/{website_title}/", $be->website_title, $body);

                if ($be->smtp_status == 1) {
                    try {
                        $smtp = [
                            'transport' => 'smtp',
                            'host' => $be->smtp_host,
                            'port' => $be->smtp_port,
                            'encryption' => $be->encryption,
                            'username' => $be->smtp_username,
                            'password' => $be->smtp_password,
                            'timeout' => null,
                            'auth_mode' => null,
                        ];
                        Config::set('mail.mailers.smtp', $smtp);
                    } catch (\Exception $e) {
                        Session::flash('warning', __('Mail could not be sent') . '.');
                    }
                }
                try {
                    $data = [
                        'to' => $order->vendor_mail,
                        'subject' => $subject,
                        'body' => $body,
                        'invoice' => public_path('assets/file/invoices/room-feature/' . $invoice)
                    ];
                    if ($be->smtp_status == 1) {
                        Mail::send([], [], function (Message $message) use ($data, $be) {
                            $fromMail = $be->from_mail;
                            $fromName = $be->from_name;
                            $message->to($data['to'])
                                ->subject($data['subject'])
                                ->from($fromMail, $fromName)
                                ->html($data['body'], 'text/html');
                            if (array_key_exists('invoice', $data) && file_exists($data['invoice'])) {
                                $message->attach($data['invoice'], [
                                    'as' => 'Invoice.pdf',
                                    'mime' => 'application/pdf',
                                ]);
                            }
                        });
                    }
                } catch (Exception $e) {
                    Session::flash('warning', __('Mail could not be sent') . '.');
                }
            }
        } else {

            $order->update([
                'payment_status' => 'rejected',
                'order_status' => 'rejected'
            ]);
            $mail_template = MailTemplate::where('mail_type', 'payment_to_feature_room_rejected_(_offline_payment_gateway_)')->first();

            if ($be->smtp_status == 1) {
                $subject = $mail_template->mail_subject;

                $body = $mail_template->mail_body;

                $body = preg_replace("/{payment_via}/", $order->payment_method, $body);
                $body = preg_replace("/{room_title}/", "<a href=" . $url . ">$room_title</a>", $body);
                $body = preg_replace("/{package_price}/", symbolPrice($order->total), $body);
                $body = preg_replace("/{username}/", $vendor->name, $body);
                $body = preg_replace("/{website_title}/", $be->website_title, $body);

                if ($be->smtp_status == 1) {
                    try {
                        $smtp = [
                            'transport' => 'smtp',
                            'host' => $be->smtp_host,
                            'port' => $be->smtp_port,
                            'encryption' => $be->encryption,
                            'username' => $be->smtp_username,
                            'password' => $be->smtp_password,
                            'timeout' => null,
                            'auth_mode' => null,
                        ];
                        Config::set('mail.mailers.smtp', $smtp);
                    } catch (\Exception $e) {
                        Session::flash('warning', __('Mail could not be sent') . '.');
                    }
                }
                try {
                    $data = [
                        'to' => $order->vendor_mail,
                        'subject' => $subject,
                        'body' => $body,

                    ];
                    if ($be->smtp_status == 1) {
                        Mail::send([], [], function (Message $message) use ($data, $be) {
                            $fromMail = $be->from_mail;
                            $fromName = $be->from_name;
                            $message->to($data['to'])
                                ->subject($data['subject'])
                                ->from($fromMail, $fromName)
                                ->html($data['body'], 'text/html');
                        });
                    }
                } catch (Exception $e) {

                    Session::flash('warning', __('Mail could not be sent') . '.');
                }
            }
        }
        Session::flash('success', __('Payment Status Updated Successfully') . '!');
        return redirect()->back();
    }


    public function updateOrderStatus(Request $request, $id)
    {
        $order = RoomFeature::find($id);

        $vendor = VendorInfo::Where('vendor_id', $order->vendor_id)->first();

        $info = Basic::select('google_recaptcha_status')->first();
        if ($info->google_recaptcha_status == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $be = Basic::select('smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'to_mail', 'website_title')->firstOrFail();

        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();

        $room = Room::with(['room_content' => function ($query) use ($language) {
            return $query->where('language_id', $language->id);
        }])->where('id', $order->room_id)->first();

        $room_title = $room->room_content[0]->title;
        $slug = $room->room_content[0]->slug;
        $url = route('frontend.room.details', ['slug' => $slug, 'id' => $room->id]);


        if ($request['order_status'] == 'pending') {

            $order->update([
                'order_status' => 'pending'
            ]);
        } else if ($request['order_status'] == 'apporved') {

            $days = $order->days;

            $startDates = Carbon::now()->startOfDay();
            $endDates = $startDates->copy()->addDays($days);

            $order->update([

                'order_status' => 'apporved',
                'start_date' => $startDates,
                'end_date' => $endDates

            ]);
            $startDate = $startDates->format('j F, Y');
            $endDate = $endDates->format('j F, Y');
            $mail_template = MailTemplate::where('mail_type', 'room_feature_request_approved')->first();

            if ($be->smtp_status == 1) {
                $subject = $mail_template->mail_subject;

                $body = $mail_template->mail_body;
                $body = preg_replace("/{username}/", $vendor->name, $body);
                $body = preg_replace("/{room_title}/", "<a href=" . $url . ">$room_title</a>", $body);
                $body = preg_replace("/{days}/", $days, $body);
                $body = preg_replace("/{activation_date}/", $startDate, $body);
                $body = preg_replace("/{end_date}/", $endDate, $body);
                $body = preg_replace("/{website_title}/", $be->website_title, $body);

                if ($be->smtp_status == 1) {
                    try {
                        $smtp = [
                            'transport' => 'smtp',
                            'host' => $be->smtp_host,
                            'port' => $be->smtp_port,
                            'encryption' => $be->encryption,
                            'username' => $be->smtp_username,
                            'password' => $be->smtp_password,
                            'timeout' => null,
                            'auth_mode' => null,
                        ];
                        Config::set('mail.mailers.smtp', $smtp);
                    } catch (\Exception $e) {
                        Session::flash('warning', __('Mail could not be sent') . '.');
                    }
                }
                try {
                    $data = [
                        'to' => $order->vendor_mail,
                        'subject' => $subject,
                        'body' => $body,
                    ];
                    if ($be->smtp_status == 1) {
                        Mail::send([], [], function (Message $message) use ($data, $be) {
                            $fromMail = $be->from_mail;
                            $fromName = $be->from_name;
                            $message->to($data['to'])
                                ->subject($data['subject'])
                                ->from($fromMail, $fromName)
                                ->html($data['body'], 'text/html');
                        });
                    }
                } catch (Exception $e) {

                    Session::flash('warning', __('Mail could not be sent') . '.');
                }
            }
        } else {

            $order->update([
                'order_status' => 'rejected'
            ]);

            $mail_template = MailTemplate::where('mail_type', 'room_feature_request_rejected')->first();

            if ($be->smtp_status == 1) {
                $subject = $mail_template->mail_subject;

                $body = $mail_template->mail_body;
                $body = preg_replace("/{username}/", $vendor->name, $body);
                $body = preg_replace("/{room_title}/", "<a href=" . $url . ">$room_title</a>", $body);
                $body = preg_replace("/{website_title}/", $be->website_title, $body);

                if ($be->smtp_status == 1) {
                    try {
                        $smtp = [
                            'transport' => 'smtp',
                            'host' => $be->smtp_host,
                            'port' => $be->smtp_port,
                            'encryption' => $be->encryption,
                            'username' => $be->smtp_username,
                            'password' => $be->smtp_password,
                            'timeout' => null,
                            'auth_mode' => null,
                        ];
                        Config::set('mail.mailers.smtp', $smtp);
                    } catch (\Exception $e) {
                        Session::flash('warning', __('Mail could not be sent') . '.');
                    }
                }
                try {
                    $data = [
                        'to' => $order->vendor_mail,
                        'subject' => $subject,
                        'body' => $body,
                    ];
                    if ($be->smtp_status == 1) {
                        Mail::send([], [], function (Message $message) use ($data, $be) {
                            $fromMail = $be->from_mail;
                            $fromName = $be->from_name;
                            $message->to($data['to'])
                                ->subject($data['subject'])
                                ->from($fromMail, $fromName)
                                ->html($data['body'], 'text/html');
                        });
                    }
                } catch (Exception $e) {
                    Session::flash('warning', __('Mail could not be sent') . '.');
                }
            }
        }
        Session::flash('success', __('Status Updated Successfully') . '!');
        return redirect()->back();
    }
    public function generateInvoice($order)
    {
        $fileName = $order->id . '.pdf';
        $directory = public_path('assets/file/invoices/room-feature/');
        @mkdir($directory, 0775, true);

        if (!file_exists($directory)) {
            @mkdir($directory, 0775, true);
        }

        $fileLocated = $directory . $fileName;

        $position = $order->currency_text_position;
        $currency = $order->currency_text;

        Pdf::loadView('frontend.pdf.room-feature', compact('order', 'position', 'currency'))->save($fileLocated);

        return $fileName;
    }
    public function destroy($id)
    {
        $order = RoomFeature::find($id);

        // delete the attachment
        @unlink(public_path('assets/file/attachments/room-feature/') . $order->attachment);

        $order->delete();

        return redirect()->back()->with('success',  __('Deleted successfully') . '!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $order = RoomFeature::find($id);

            // delete the attachment
            @unlink(public_path('assets/file/attachments/room-feature/') . $order->attachment);

            $order->delete();
        }

        Session::flash('success', __('Selectet item deleted successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }
}
