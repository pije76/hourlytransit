<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Http\Helpers\BasicMailer;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\Withdraw\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    // use MiscellaneousTrait;
    //index
    public function index()
    {
        $misc = new MiscellaneousController();

        $language = $misc->getLanguage();
        $search = request()->input('search');

        $information['collection'] = Withdraw::with('method')
            ->when($search, function ($query, $keyword) {
                return $query->where('withdraws.withdraw_id', 'like', '%' . $keyword . '%');
            })
            ->orderBy('id', 'desc')->paginate(10);
        $information['currencyInfo'] =    $this->getCurrencyInfo();
        return view('admin.withdraw.history.index', $information);
    }
    //delete
    public function delete(Request $request)
    {
        $withdraw = Withdraw::where('id', $request->id)->first();

        if($withdraw->status == 0){
            $transaction = Transaction::where([['booking_id', $withdraw->id], ['transcation_type', 'withdraw']])->first();
            $transaction->delete();
            //update vendor balance
            $vendor_balance = Vendor::where('id', $withdraw->vendor_id)->pluck('amount')->first();
            $vendor_new_balance = $vendor_balance + $withdraw->amount;

            DB::table('vendors')->updateOrInsert(
                ['id' => $withdraw->vendor_id],
                [
                    'amount' => $vendor_new_balance
                ]
            );
        }
        
        $withdraw->delete();
        return redirect()->back()->with('success',  __('Withdraw Request Deleted Successfully') . '!');
    }
    //approve
    public function approve($id)
    {
        $withdraw = Withdraw::where('id', $id)->first();

        //update transcation 
        $transaction = Transaction::where([['booking_id', $withdraw->id], ['transcation_type', 'withdraw']])->first();
        $transaction->update([
            'payment_status' => 1
        ]);
        $withdraw->status = 1;
        $withdraw->save();

        //add blance to admin revinue
        $earning = Basic::first();

        $earning->total_earning = $earning->total_earning + $withdraw->total_charge;

        $earning->save();


        //mail sending
        // get the mail template info from db
        $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'withdrawal_request_approved')->first();
        $mailData['subject'] = $mailTemplate->mail_subject;
        $mailBody = $mailTemplate->mail_body;

        // get the website title info from db
        $info = Basic::select('website_title', 'base_currency_symbol')->first();

        $vendor = $withdraw->vendor()->first();

        // preparing dynamic data
        $vendorName = $vendor->username;
        $vendorEmail = $vendor->to_mail;
        $vendor_amount = $vendor->amount;
        $method = $withdraw->method()->select('name')->first();
        $websiteTitle = $info->website_title;

        // replacing with actual data
        $mailBody = str_replace('{username}', $vendorName, $mailBody);
        $mailBody = str_replace('{withdraw_id}', $withdraw->withdraw_id, $mailBody);
        $mailBody = str_replace('{current_balance}', symbolPrice($vendor_amount), $mailBody);
        $mailBody = str_replace('{withdraw_amount}', symbolPrice($withdraw->amount), $mailBody);
        $mailBody = str_replace('{charge}', symbolPrice($withdraw->total_charge), $mailBody);
        $mailBody = str_replace('{payable_amount}', symbolPrice($withdraw->payable_amount), $mailBody);
        $mailBody = str_replace('{website_title}', $websiteTitle, $mailBody);

        $mailData['body'] = $mailBody;

        $mailData['recipient'] = $vendorEmail;
        BasicMailer::sendMail($mailData);
        return redirect()->back();
    }
    //decline
    public function decline($id)
    {
        $withdraw = Withdraw::where('id', $id)->first();

        //update transcation
        $transaction = Transaction::where('booking_id', $withdraw->id)
            ->where('transcation_type', 'withdraw')
            ->first();
        $transaction->update([
            'payment_status' => 2,
        ]);

        $withdraw->status = 2;
        $withdraw->save();

        //update vendor balance
        $vendor_balance = Vendor::where('id', $withdraw->vendor_id)->pluck('amount')->first();
        $vendor_new_balance = $vendor_balance + $withdraw->amount;

        DB::table('vendors')->updateOrInsert(
            ['id' => $withdraw->vendor_id],
            [
                'amount' => $vendor_new_balance 
            ]
        );

        //mail sending
        // get the mail template info from db
        $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'withdrawal_request_rejected')->first();
        $mailData['subject'] = $mailTemplate->mail_subject;
        $mailBody = $mailTemplate->mail_body;

        // get the website title info from db
        $info = Basic::select('website_title', 'base_currency_symbol')->first();

        $vendor = $withdraw->vendor()->first();

        // preparing dynamic data
        $vendorName = $vendor->username;
        $vendorEmail = $vendor->to_mail;
        $vendor_amount = $vendor->amount;
        $method = $withdraw->method()->select('name')->first();
        $websiteTitle = $info->website_title;

        // replacing with actual data
        $mailBody = str_replace('{username}', $vendorName, $mailBody);
        $mailBody = str_replace('{withdraw_id}', $withdraw->withdraw_id, $mailBody);
        $mailBody = str_replace('{current_balance}', symbolPrice($vendor_amount), $mailBody);
        $mailBody = str_replace('{website_title}', $websiteTitle, $mailBody);

        $mailData['body'] = $mailBody;

        $mailData['recipient'] = $vendorEmail;

        BasicMailer::sendMail($mailData);
        return redirect()->back()->with('success',  __('Withdraw Request Decline Successfully') . '!');

        return redirect()->back();
    }
}
