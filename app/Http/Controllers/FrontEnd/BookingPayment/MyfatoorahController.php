<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Vendor;
use Basel\MyFatoorah\MyFatoorah;
use Exception;
use Illuminate\Http\Request;
use Session;

class MyfatoorahController extends Controller
{
    public $myfatoorah;

    public function __construct()
    {
        $this->myfatoorah = MyFatoorah::getInstance(true);
    }

    public function index(Request $request, $paymentFor)
    {
        try {

            if ($request->session()->has('price')) {
                $priceId = $request->session()->get('price');
            } else {
                Session::flash('error', 'Something went wrong!');

                return redirect()->back();
            }

            $bookingProcess = new BookingController();

            // do calculation
            $calculatedData = $bookingProcess->calculation($request, $priceId);


            $currencyInfo = $this->getCurrencyInfo();
            $allowed_currency = array('KWD', 'SAR', 'BHD', 'AED', 'QAR', 'OMR', 'JOD');
            if (!in_array($currencyInfo->base_currency_text, $allowed_currency)) {
                return redirect()->back()->with('error', 'Invalid currency for myfatoorah payment.')->withInput();
            }

            $arrData = $bookingProcess->timeCheck($request, 'Myfatoorah');


            $grandTotal = number_format($calculatedData['grandTotal'], 2, '.', '');
            /* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            ~~~~~~~~~~~~~~~~~ Payment Gateway Info ~~~~~~~~~~~~~~
            ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
            $info = OnlineGateway::where('keyword', 'myfatoorah')->first();
            $information = json_decode(
                $info->information,
                true
            );
            $random_1 = rand(999, 9999);
            $random_2 = rand(9999, 99999);
            $result = $this->myfatoorah->sendPayment(
                $request['booking_name'],
                $grandTotal,
                [
                    'CustomerMobile' => $information['sandbox_status'] == 1 ? '56562123544' : $request->phone,
                    'CustomerReference' => "$random_1",
                    'UserDefinedField' => "$random_2",
                    "InvoiceItems" => [
                        [
                            "ItemName" => "Product Purchase",
                            "Quantity" => 1,
                            "UnitPrice" => $grandTotal
                        ]
                    ]
                ]
            );
            if ($result && $result['IsSuccess'] == true) {
                $request->session()->put('myfatoorah_payment_type', 'booking');
                $request->session()->put('paymentFor', $paymentFor);
                $request->session()->put('arrData', $arrData);
                return redirect($result['Data']['InvoiceURL']);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Payment Cancel.')->withInput();
        }
    }

    public function successCallback(Request $request)
    {
        $arrData = $request->session()->get('arrData');
        if (!empty($request->paymentId)) {
            $result = $this->myfatoorah->getPaymentStatus('paymentId', $request->paymentId);
            if ($result && $result['IsSuccess'] == true && $result['Data']['InvoiceStatus'] == "Paid") {
                $bookingProcess = new BookingController();

                // store all data in the database
                $bookingInfo = $bookingProcess->storeData($arrData);


                // generate an invoice in pdf format 
                $invoice = $bookingProcess->generateInvoice($bookingInfo);

                // then, update the invoice field info in database 
                $bookingInfo->update(['invoice' => $invoice]);

                // send a mail to the customer with the invoice
                $bookingProcess->prepareMailForCustomer($bookingInfo);

                // send a mail to the vendor with the invoice
                $bookingProcess->prepareMailForvendor($bookingInfo);

                //tranction part
                $vendor_id = $bookingInfo->vendor_id;


                //calculate commission
                if ($vendor_id == 0) {
                    $commission = $bookingInfo->grand_total;
                } else {
                    $commission = 0;
                }

                //get vendor
                $vendor = Vendor::where('id', $vendor_id)->first();

                //add blance to admin revinue
                $earning = Basic::first();

                if ($vendor_id == 0) {
                    $earning->total_earning = $earning->total_earning + $bookingInfo->grand_total;
                } else {
                    $earning->total_earning = $earning->total_earning + $commission;
                }
                $earning->save();

                //store Balance  to vendor
                if ($vendor) {
                    $pre_balance = $vendor->amount;
                    $vendor->amount = $vendor->amount + ($bookingInfo->grand_total - ($commission));
                    $vendor->save();
                    $after_balance = $vendor->amount;
                } else {

                    $after_balance = NULL;
                    $pre_balance = NULL;
                }
                //calculate commission end

                $data = [
                    'transcation_id' => time(),
                    'booking_id' => $bookingInfo->id,
                    'transcation_type' => 'room_booking',
                    'user_id' => null,
                    'vendor_id' => $vendor_id,
                    'payment_status' => 1,
                    'payment_method' => $bookingInfo->payment_method,
                    'grand_total' => $bookingInfo->grand_total,
                    'commission' => $commission,
                    'pre_balance' => $pre_balance,
                    'after_balance' => $after_balance,
                    'gateway_type' => $bookingInfo->gateway_type,
                    'currency_symbol' => $bookingInfo->currency_symbol,
                    'currency_symbol_position' => $bookingInfo->currency_symbol_position,
                ];
                store_transaction($data);


                // remove all session data
                $request->session()->forget('price');
                $request->session()->forget('checkInTime');
                $request->session()->forget('checkInDate');
                $request->session()->forget('adult');
                $request->session()->forget('children');
                $request->session()->forget('roomDiscount');
                $request->session()->forget('takeService');
                $request->session()->forget('serviceCharge');

                return [
                    'status' => 'success'
                ];
            } else {
                return [
                    'status' => 'fail'
                ];
            }
        } else {
            return [
                'status' => 'fail'
            ];
        }
    }

    public function failCallback(Request $request)
    {
        if (!empty($request->paymentId)) {
            $result = $this->myfatoorah->getPaymentStatus('paymentId', $request->paymentId);

            if ($result && $result['IsSuccess'] == true && $result['Data']['InvoiceStatus'] == "Pending") {
                Session::flash('warning', 'Payment Cancel.');
            }
        }
    }
}
