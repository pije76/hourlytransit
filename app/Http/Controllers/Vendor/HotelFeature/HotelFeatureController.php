<?php

namespace App\Http\Controllers\Vendor\HotelFeature;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\HotelFeature\PayPalController;
use App\Http\Controllers\Vendor\HotelFeature\OfflineController;
use App\Http\Controllers\Vendor\HotelFeature\InstamojoController;
use App\Http\Helpers\BasicMailer;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Language;
use App\Models\VendorInfo;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class HotelFeatureController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Hotel Feature';
        $currencyInfo = $this->getCurrencyInfo();
        $rules = [
            'charge' => 'required',
            'gateway' => 'required',
        ];

        $messages = [
            'charge.required' => 'Please select a promotion.',
            'gateway.required' => 'Please select a payment gateway.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request['gateway'] == 'paypal') {
            $paypal = new PayPalController();

            return $paypal->index($request, 'service featured');
        } else if ($request['gateway'] == 'instamojo') {
            $instamojo = new InstamojoController();

            return $instamojo->index($request, 'service featured');
        } else if ($request['gateway'] == 'paystack') {
            $paystack = new PaystackController();

            return $paystack->index($request, 'service featured');
        } else if ($request['gateway'] == 'flutterwave') {
            $flutterwave = new FlutterwaveController();

            return $flutterwave->index($request, 'service featured');
        } else if ($request['gateway'] == 'razorpay') {
            $razorpay = new RazorpayController();

            return $razorpay->index($request, 'service featured');
        } elseif ($request['gateway'] == 'midtrans') {

            if ($currencyInfo->base_currency_text != "IDR") {
                return Response::json(['error' => 'Invalid currency for Midtrans payment.'], 422);
            }
            $amount = $request->price;
            $email = $request->email;
            $userType = "hotelfeature";
            $cancel_url = route('midtrans.cancel');
            $mercadopagoPayment = new MidtransController();
            return $mercadopagoPayment->paymentProcess($request, $userType);
        } elseif ($request['gateway'] == 'iyzico') {

            $profile_status =  $this->check_profile();
            if ($profile_status == 'incomplete') {

                Session::flash('warning', __('Please, Complete your profile before purchase using iyzico payment method') . '!');
                return response()->json(['redirectURL' => route('vendor.edit.profile')]);
            }
            if ($currencyInfo->base_currency_text != "TRY") {
                session()->flash('warning', $currencyInfo->base_currency_text . " is not allowed for Iyzico");
                return Response::json(['error' => $currencyInfo->base_currency_text . " is not allowed for Iyzico"], 422);
            }
            $amount = $request->price;
            $iyzico = new IyzicoController();
            $cancel_url = route('vendor.hotel_management.hotel.purchase_feature.iyzico.cancle');
            return $iyzico->paymentProcess($request, $amount, $title, $cancel_url);
        } elseif ($request['gateway'] == 'myfatoorah') {
            $allowed_currency = array('KWD', 'SAR', 'BHD', 'AED', 'QAR', 'OMR', 'JOD');
            if (!in_array($currencyInfo->base_currency_text, $allowed_currency)) {
                return Response::json(['error' => 'Invalid currency for Myfatoorah payment.'], 422);
            }

            $amount = $request->price;
            $fatoorahdfsafsdaf = new MyfatoorahController();
            $cancel_url = route('membership.cancel');
            return $fatoorahdfsafsdaf->index($request, $amount, $title, $cancel_url);
        } else if ($request['gateway'] == 'yoco') {
            // changing the currency before redirect to Stripe
            if ($currencyInfo->base_currency_text != 'ZAR') {
                return Response::json(['error' => 'Invalid currency for Yoco payment.'], 422);
            }

            $amount = $request->price;
            $yoco = new YocoController();
            $cancel_url = route('membership.cancel');
            return $yoco->index($request, $amount, $title, $cancel_url);
        } else if ($request['gateway']  == 'xendit') {

            $xendit = new XenditController();
            return $xendit->index($request, 'hotel feature');
        } elseif ($request['gateway'] == 'toyyibpay') {
            // changing the currency before redirect to Stripe
            if ($currencyInfo->base_currency_text != 'RM') {

                return Response::json(['error' => 'Invalid currency for toyyibpay payment.'], 422);
            }

            $amount = $request->price;
            $toyyibpay = new ToyyibpayController();
            $cancel_url = route('membership.cancel');
            return $toyyibpay->index($request, $amount, $title, $cancel_url);
        } elseif ($request['gateway'] == 'paytabs') {
            $paytabInfo = paytabInfo();
            // changing the currency before redirect to Stripe
            if ($currencyInfo->base_currency_text != $paytabInfo['currency']) {
                return Response::json(['error' => 'Invalid currency for paytabs payment.'], 422);
            }

            $amount = $request->price;
            $paytabs = new PaytabsController();
            $cancel_url = route('membership.paytabs.cancel');
            return $paytabs->index($request, $amount, $title, $cancel_url);
        } else if ($request['gateway'] == 'mercadopago') {
            $mercadopago = new MercadopagoController();

            return $mercadopago->index($request, 'hotel featured');
        } else if ($request['gateway'] == 'phonepe') {

            $phonepe = new PhonepeController();
            return $phonepe->index($request, 'hotel featured');
        } else if ($request['gateway'] == 'perfect_money') {
            $perfect_money = new PerfectMoneyController();

            return $perfect_money->index($request, 'hotel featured');
        } else if ($request['gateway'] == 'mollie') {
            $mollie = new MollieController();

            return $mollie->index($request, 'hotel featured');
        } else if ($request['gateway'] == 'stripe') {
            $stripe = new StripeController();

            return $stripe->index($request, 'hotel featured');
        } else if ($request['gateway'] == 'paytm') {
            $paytm = new PaytmController();

            return $paytm->index($request, 'hotel featured');
        } else if ($request['gateway'] == 'authorize.net') {
            $authorize = new AuthorizenetController();

            return $authorize->index($request, 'hotel featured');
        } else {
            $offline = new OfflineController();

            return $offline->index($request, 'hotel featured');
        }
    }
    public function prepareMail($to_mail, $price, $paymentMethod, $invoice)
    {

        $vendor = VendorInfo::Where('vendor_id', Auth::guard('vendor')->user()->id)->first();
        $info = Basic::select('google_recaptcha_status')->first();
        if ($info->google_recaptcha_status == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $be = Basic::select('smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'to_mail', 'website_title')->firstOrFail();

        $mail_template = MailTemplate::where('mail_type', 'payment_accepted_for_featured_online_gateway')->first();

        if ($be->smtp_status == 1) {
            $subject = $mail_template->mail_subject;

            $body = $mail_template->mail_body;
            $body = preg_replace("/{username}/", $vendor->name, $body);

            $body = preg_replace("/{payment_via}/", $paymentMethod, $body);

            $body = preg_replace("/{package_price}/", symbolPrice($price), $body);
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
                    Session::flash('error', $e->getMessage());
                    return back();
                }
            }
            try {
                $data = [
                    'to' => $to_mail,
                    'subject' => $subject,
                    'body' => $body,
                    'invoice' => public_path('assets/file/invoices/hotel-feature/' . $invoice)
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

                Session::flash('success', __('Your Payment successfully completed') . '!');
            } catch (Exception $e) {
                Session::flash('error', $e);
            }
            $info = Basic::select('to_mail', 'website_title')->first();
            $vendor = Auth::guard('vendor')->user()->username;

            $mailData['subject'] = "$vendor wants to feature a hotel on $info->website_title";
            $mailBody = "Dear Admin,
            
I hope this email finds you well. I wanted to bring to your attention that $vendor wants to feature a hotel on our website by.

Thank you for your attention to this matter.";

            $mailData['body'] = nl2br($mailBody);
            $mailData['recipient'] = $info->to_mail;

            BasicMailer::sendMail($mailData);
        }

        return;
    }

    private function check_profile()
    {
        $language = Language::where('is_default', 1)->first();
        $vendor = Auth::guard('vendor')->user();
        $vendor_info = $vendor->vendor_info()->where('language_id', $language->id)->first();
        if ($vendor_info) {
            if (is_null($vendor_info->name) || is_null($vendor_info->address) || is_null($vendor_info->city) || is_null($vendor_info->country) || is_null($vendor_info->zip_code)) {
                return 'incomplete';
            } else {
                return 'completed';
            }
        } else {
            return 'incomplete';
        }
    }
    public function generateInvoice($order)
    {
        $fileName = $order->id . '.pdf';
        $directory = public_path('assets/file/invoices/hotel-feature/');
        @mkdir($directory, 0775, true);

        if (!file_exists($directory)) {
            @mkdir($directory, 0775, true);
        }

        $fileLocated = $directory . $fileName;

        $position = $order->currency_text_position;
        $currency = $order->currency_text;

        Pdf::loadView('frontend.pdf.hotel-feature', compact('order', 'position', 'currency'))->save($fileLocated);

        return $fileName;
    }

    public function onlineSuccess()
    {
        return view('vendors.services.online-success');
    }
    public function offlineSuccess()
    {
        return view('vendors.room.offline-success');
    }
}
