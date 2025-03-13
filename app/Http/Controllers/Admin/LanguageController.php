<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\StoreRequest;
use App\Http\Requests\Language\UpdateRequest;
use App\Models\Admin;
use App\Models\CustomPage\Page;
use App\Models\CustomPage\PageContent;
use App\Models\HomePage\Hero\Slider;
use App\Models\Journal\Blog;
use App\Models\Journal\BlogInformation;
use App\Models\Language;
use App\Models\MenuBuilder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $languages = Language::all();

        return view('admin.language.index', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        // get all the keywords from the default file of language
        $data = file_get_contents(resource_path('lang/') . 'default.json');

        // make a new json file for the new language
        $fileName = strtolower($request->code) . '.json';

        // create the path where the new language json file will be stored
        $fileLocated = resource_path('lang/') . $fileName;

        // finally, put the keywords in the new json file and store the file in lang folder
        file_put_contents($fileLocated, $data);


        // get all the keywords from the default file of language for admin
        $admin_data = file_get_contents(resource_path('lang/') . 'admin_default.json');

        // make a new json file for the new language for admin
        $admin_json_file = 'admin_' . trim($request->code) . '.json';

        // create the path where the new language json file will be stored for admin
        $admin_path = resource_path('lang/') . $admin_json_file;

        // finally, put the keywords in the new json file and store the file in lang folder for admin
        file_put_contents($admin_path, $admin_data);


        $in = $request->all();
        $in['code'] = strtolower($request->code);

        // then, store data in db
        $language = Language::query()->create($in);

        $data = [];

        $data[] = [
            'text' => 'Home',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "home"
        ];
        $data[] = [
            'text' => 'Hotels',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "hotels"
        ];
        $data[] = [
            'text' => 'Rooms',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "rooms"
        ];
        $data[] = [
            'text' => 'Vendors',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "vendors"
        ];
        $data[] = [
            'text' => 'Pricing',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "pricing"
        ];
        $data[] = [
            'text' => 'Blog',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "blog"
        ];
        $data[] = [
            'text' => 'FAQ',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "faq"
        ];
        $data[] = [
            'text' => 'About Us',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "about-us"
        ];
        $data[] = [
            'text' => 'Contact',
            "href" => "",
            "icon" => "empty",
            "target" => "_self",
            "title" => "",
            "type" => "contact"
        ];
        MenuBuilder::create([
            'language_id' => $language->id,
            'menus' => json_encode($data, true)
        ]);

        $defaultlang = Language::Where('is_default', '1')->first();
        $slider = Slider::where('language_id', $defaultlang->id)->first();
        if ($slider) {
            $newSlider = $slider->replicate();
            $newSlider->language_id = $language->id;
            $newSlider->save();
        }

        Session::flash('success', __('Language added successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Make a default language for this system.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function makeDefault($id)
    {
        // first, make other languages to non-default language
        $prevDefLang = Language::query()->where('is_default', '=', 1);

        $prevDefLang->update(['is_default' => 0]);

        // second, make the selected language to default language
        $language = Language::query()->find($id);

        $language->update(['is_default' => 1]);

        return back()->with('success', $language->name . ' ' . 'is set as default language.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request)
    {
        $language = Language::query()->find($request->id);
        $in = $request->all();
        $in['code'] = strtolower($request->code);

        if ($language->code !== $request->code) {
            /**
             * get all the keywords from the previous file,
             * which was named using previous language code
             */
            $data = file_get_contents(resource_path('lang/') . $language->code . '.json');

            // make a new json file for the new language (code)
            $fileName = strtolower($request->code) . '.json';

            // create the path where the new language (code) json file will be stored
            $fileLocated = resource_path('lang/') . $fileName;

            // then, put the keywords in the new json file and store the file in lang folder
            file_put_contents($fileLocated, $data);

            // now, delete the previous language code file
            @unlink(resource_path('lang/') . $language->code . '.json');

            // get all the keywords from the default file of language for admin
            $admin_data = file_get_contents(resource_path('lang/') . 'admin_default.json');

            // make a new json file for the new language for admin
            $admin_json_file = 'admin_' . trim($request->code) . '.json';

            // create the path where the new language json file will be stored for admin
            $admin_path = resource_path('lang/') . $admin_json_file;

            // finally, put the keywords in the new json file and store the file in lang folder for admin
            file_put_contents($admin_path, $admin_data);

            // now, delete the previous language code file for admin
            @unlink(resource_path('lang/') . 'admin_' . $language->code . '.json');


            // finally, update the info in db
            $language->update($in);
        } else {
            $language->update($in);
        }

        Session::flash('success', __('Language updated successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }

    public function addKeyword(Request $request)
    {
        $rules = [
            'keyword' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $languages = Language::get();
        foreach ($languages as $language) {
            // get all the keywords of the selected language
            $jsonData = file_get_contents(resource_path('lang/') . $language->code . '.json');

            // convert json encoded string into a php associative array
            $keywords = json_decode($jsonData, true);
            $datas = [];
            $datas[$request->keyword] = $request->keyword;

            foreach ($keywords as $key => $keyword) {
                $datas[$key] = $keyword;
            }
            //put data
            $jsonData = json_encode($datas);

            $fileLocated = resource_path('lang/') . $language->code . '.json';

            // put all the keywords in the selected language file
            file_put_contents($fileLocated, $jsonData);
        }

        //for default json
        // get all the keywords of the selected language
        $jsonData = file_get_contents(resource_path('lang/') . 'default.json');

        // convert json encoded string into a php associative array
        $keywords = json_decode($jsonData, true);
        $datas = [];
        $datas[$request->keyword] = $request->keyword;

        foreach ($keywords as $key => $keyword) {
            $datas[$key] = $keyword;
        }
        
        //put data
        $jsonData = json_encode($datas);

        $fileLocated = resource_path('lang/') . 'default.json';

        // put all the keywords in the selected language file
        file_put_contents($fileLocated, $jsonData);

        Session::flash('success', __('A new keyword successfully') . '!');

        return response()->json(['status' => 'success'], 200);
    }
    public function addKeywordAdmin(Request $request)
    {
        $rules = [
            'keyword' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $languages = Language::get();
        foreach ($languages as $language) {
            // get all the keywords of the selected language
            $jsonData = file_get_contents(resource_path('lang/') . 'admin_' . $language->code . '.json');


            // convert json encoded string into a php associative array
            $keywords = json_decode($jsonData, true);
            $datas = [];
            $datas[$request->keyword] = $request->keyword;

            foreach ($keywords as $key => $keyword) {
                $datas[$key] = $keyword;
            }
            //put data
            $jsonData = json_encode($datas);

            $fileLocated = resource_path('lang/') . 'admin_' . $language->code . '.json';

            // put all the keywords in the selected language file
            file_put_contents($fileLocated, $jsonData);
        }

        //for default json
        // get all the keywords of the selected language
        $jsonData = file_get_contents(resource_path('lang/') . 'admin_default.json');

        // convert json encoded string into a php associative array
        $keywords = json_decode($jsonData, true);
        $datas = [];
        $datas[$request->keyword] = $request->keyword;

        foreach ($keywords as $key => $keyword) {
            $datas[$key] = $keyword;
        }

        $jsonData = json_encode($datas);

        $fileLocated = resource_path('lang/') . 'admin_default.json';

        // put all the keywords in the selected language file
        file_put_contents($fileLocated, $jsonData);

        Session::flash('success', __('A new keyword successfully') . '!');
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Display all the keywords of specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editKeyword($id)
    {
        $isAdmin =  0;

        $language = Language::query()->findOrFail($id);

        // get all the keywords of the selected language
        $jsonData = file_get_contents(resource_path('lang/') . $language->code . '.json');

        // convert json encoded string into a php associative array
        $keywords = json_decode($jsonData);

        return view('admin.language.edit-keyword', compact('language', 'keywords', 'isAdmin'));
    }

    public function editAdminKeyword($id)
    {
        $isAdmin =  1;

        $language = Language::query()->findOrFail($id);

        // get all the keywords of the selected language
        $jsonData = file_get_contents(resource_path('lang/') . 'admin_' . $language->code . '.json');

        // convert json encoded string into a php associative array
        $keywords = json_decode($jsonData);

        return view('admin.language.edit-keyword', compact('language', 'keywords', 'isAdmin'));
    }

    /**
     * Update the keywords of specified resource in respective json file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateKeyword(Request $request, $id)
    {
        $arrData = $request['keyValues'];
        // Count the number of elements in $arrData
        $dataCount = count($arrData);

        // first, check each key has value or not
        foreach ($arrData as $key => $value) {
            if ($value == null) {
                Session::flash('warning', 'Value is required for "' . $key . '" key.');

                return redirect()->back();
            }
        }

        $jsonData = json_encode($arrData);

        $language = Language::query()->find($id);

        if ($request->isAdmin) {

            $fileLocated = resource_path('lang/') . 'admin_' . $language->code . '.json';

            // Load existing keywords from file
            $existingData = [];
            if (file_exists($fileLocated)) {
                $existingData = json_decode(file_get_contents($fileLocated), true) ?? [];
            }
            // Merge existing keywords with new ones (new keys overwrite old ones)
            $mergedData = array_merge($existingData, $arrData);

            // Define the validation file path
            $validationFilePath = resource_path('lang/admin_' . $language->code . '/validation.php');

            // Check if the validation file exists
            if (file_exists($validationFilePath)) {
                // Include the file and fetch the content
                $validation = include($validationFilePath);

                // Access the 'attributes' array from the included file
                if (isset($validation['attributes'])) {
                    $validationArray = $validation['attributes'];
                } else {
                    $validationArray = []; // Default to an empty array if 'attributes' is not set
                }
            } else {
                $validationArray = []; // Default if the file doesn't exist
            }

            //update existing keys
            $newKeys = [
                "name" => "name",
                "username" => "username",
                "email" => "email address",
                "first_name" => "first name",
                "last_name" => "last name",
                "password" => "password",
                "password_confirmation" => "confirm password",
                "city" => "city",
                "country" => "country",
                "address" => "address",
                "phone" => "phone",
                "mobile" => "mobile",
                "age" => "age",
                "gender" => "gender",
                "day" => "day",
                "month" => "month",
                "year" => "year",
                "hour" => "hour",
                "minute" => "minute",
                "second" => "second",
                "title" => "title",
                "subtitle" => "subtitle",
                "text" => "text",
                "description" => "description",
                "content" => "content",
                "occupation" => "occupation",
                "comment" => "comment",
                "rating" => "rating",
                "terms" => "terms",
                "question" => "question",
                "answer" => "answer",
                "status" => "status",
                "term" => "term",
                "price" => "price",
                "amount" => "amount",
                "date" => "date",
                "latitude" => "latitude",
                "longitude" => "longitude",
                "value" => "value",
                "type" => "type",
                "code" => "code",
                "url" => "url",
                "stock" => "stock",
                "delay" => "delay",
                "image" => "image",
                "language_id" => "language",
                "serial_number" => "serial number",
                "category_id" => "category",
                "slider_images" => "slider images",
                "order_number" => "order number",
                "end_time" => "end time",
                "start_date" => "start date",
                "end_date" => "end date",
                "product_tax_amount" => "product tax amount",
                "shipping_charge" => "shipping charge",
                "short_text" => "short text",
                "featured_image" => "featured image",
                "current_price" => "current price",
                "min_limit" => "min limit",
                "max_limit" => "max limit",
                "email_address" => "email address",
                "contact_number" => "contact number",
                "new_password" => "new password",
                "new_password_confirmation" => "new password confirmation",
                "google_adsense_publisher_id" => "google adsense publisher id",
                "ad_type" => "ad type",
                "resolution_type" => "resolution type",
                "button_text" => "button text",
                "button_url" => "button url",
                "background_color_opacity" => "background color opacity",
                "base_currency_symbol" => "base currency symbol",
                "base_currency_symbol_position" => "base currency symbol position",
                "base_currency_text" => "base currency text",
                "base_currency_text_position" => "base currency text position",
                "base_currency_rate" => "base currency rate",
                "website_title" => "website title",
                "secondary_color" => "secondary color",
                "primary_color" => "primary color",
                "preloader" => "preloader",
                "logo" => "logo",
                "favicon" => "favicon",
                "smtp_host" => "smtp host",
                "smtp_port" => "smtp port",
                "encryption" => "encryption",
                "from_name" => "from name",
                "from_mail" => "from mail",
                "smtp_password" => "smtp password",
                "smtp_username" => "smtp username",
                "mail_subject" => "mail subject",
                "mail_body" => "mail body",
                "cookie_alert_text" => "cookie alert text",
                "role_id" => "role_id",
                "paypal_status" => "paypal status",
                "paypal_sandbox_status" => "paypal sandbox status",
                "paypal_client_id" => "paypal client ID",
                "paypal_client_secret" => "paypal client secret",
                "instamojo_status" => "instamojo status",
                "instamojo_sandbox_status" => "instamojo sandbox status",
                "instamojo_key" => "instamojo API key",
                "instamojo_token" => "instamojo auth token",
                "paytm_status" => "paytm status",
                "paytm_environment" => "paytm environment",
                "paytm_merchant_key" => "paytm merchant key",
                "paytm_merchant_mid" => "paytm merchant MID",
                "paytm_merchant_website" => "paytm merchant website",
                "paytm_industry_type" => "paytm industry type",
                "stripe_status" => "stripe status",
                "stripe_key" => "stripe key",
                "stripe_secret" => "stripe secret",
                "flutterwave_status" => "flutterwave status",
                "flutterwave_public_key" => "flutterwave public key",
                "flutterwave_secret_key" => "flutterwave secret key",
                "razorpay_status" => "razorpay status",
                "razorpay_key" => "razorpay key",
                "razorpay_secret" => "razorpay secret",
                "mollie_status" => "mollie status",
                "mollie_key" => "mollie API key",
                "paystack_status" => "paystack status",
                "paystack_key" => "paystack API key",
                "mercadopago_status" => "mercadopago status",
                "mercadopago_sandbox_status" => "mercadopago sandbox status",
                "mercadopago_token" => "mercadopago token",
                "authorize_net_status" => "Authorize.Net status",
                "sandbox_check" => "sandbox check",
                "login_id" => "login ID",
                "transaction_key" => "transaction key",
                "public_key" => "public key",
                "google_map_api_key" => "google map api key",
                "google_map_radius" => "google map radius",
                "disqus_short_name" => "disqus short name",
                "tawkto_status" => "tawkto status",
                "tawkto_direct_chat_link" => "tawkto direct chat link",
                "whatsapp_number" => "whatsapp number",
                "whatsapp_header_title" => "whatsapp header title",
                "whatsapp_popup_message" => "whatsapp popup message",
                "google_recaptcha_site_key" => "googlerecaptasitekey",
                "google_recaptcha_status" => "googlerecaptastatus",
                "google_recaptcha_secret_key" => "googlerecaptasecretkey",
                "google_client_id" => "googleclientid",
                "google_client_secret" => "googleclientsecret",
                "google_login_status" => "googleloginstatus",
                "current_password" => "current password",
                "hero_section_title" => "hero section title",
                "hero_section_subtitle" => "hero section subtitle",
                "city_section_title" => "city section title",
                "featured_section_title" => "featured section title",
                "featured_section_text" => "featured section text",
                "featured_room_section_title" => "featured room section title",
                "featured_room_section_button_text" => "featured room section button text",
                "counter_section_video_link" => "counter section video link",
                "blog_section_title" => "blog section title",
                "blog_section_button_text" => "blog section button text",
                "call_to_action_section_title" => "call to action section title",
                "call_to_action_button_url" => "call to action button url",
                "call_to_action_section_btn" => "call to action section btn",
                "testimonial_section_title" => "testimonial section title",
                "benifit_section_title" => "benifit section title",
                "testimonial_section_subtitle" => "testimonial section subtitle",
                "testimonial_section_clients" => "testimonial section clients",
                "order" => "order",
                "background_image" => "background image",
                "about_company" => "about company",
                "number_of_hotel" => "number of hotel",
                "number_of_images_per_hotel" => "number of images per hotel",
                "number_of_amenities_per_hotel" => "number of amenities per hotel",
                "number_of_room" => "number of room",
                "number_of_images_per_room" => "number of images per room",
                "number_of_amenities_per_room" => "number of amenities per room",
                "number_of_bookings" => "number of bookings",
                "feature_image" => "feature_image",
                "hotel_id" => "hotel",
                "about_company" => "aboutcompany",
                "hour" => "hour",
                "adult" => "adult",
                "bed" => "bed",
                "bathroom" => "bathroom",
                "number_of_rooms_of_this_same_type" => "number of rooms of this same type",
                "customer_name" => "customer name",
                "customer_phone" => "customer phone",
                "customer_email" => "customer email",
                "payment_method" => "payment method",
                "payment_status" => "payment status",
                "vapid_public_key" => "vapid public key",
                "vapid_private_key" => "vapid private key",
                "mail_address" => "mail address",
                "keyword" => "keyword",
                "direction" => "direction",
                "tawkto_direct_chat_link" => "tawkto direct chat link",
                "facebook_app_id" => "facebook app id",
                "google_map_api_key_status" => "google map api key status",
                "google_map_api_key" => "google map api key",
                "radius" => "radius",
                "maintenance_msg" => "maintenance message",
                "photo" => "photo",
                "expiration_reminder" => "expiration reminder",
            ];

            /**
             *
             */
            if (is_array($validationArray)) {
                foreach ($newKeys as $key => $value) {
                    if (!array_key_exists($key, $validationArray)) {
                        $validationArray[$key] = $value;
                    }
                }
            }
            // update values which matching keys with new values
            foreach ($mergedData as $key => $value) {
                if (array_key_exists($key, $validationArray)) {
                    $validationArray[$key] = $value;
                }
            }

            //save the changes in validation attributes array
            $validation['attributes'] = $validationArray;
            $validationContent = "<?php\n\nreturn " . var_export($validation, true) . ";\n";
            if (file_exists($validationFilePath)) {
                file_put_contents($validationFilePath, $validationContent);
            }
            // Save the updated data
            file_put_contents($fileLocated, json_encode($mergedData));
        } else {

            $fileLocated = resource_path('lang/') . $language->code . '.json';

            // put all the keywords in the selected language file
            file_put_contents($fileLocated, $jsonData);
        }

        Session::flash('success', $language->name . __('language\'s keywords updated successfully'));

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $language = Language::query()->find($id);

        if ($language->is_default == 1) {
            return redirect()->back()->with('success',  __('Default language cannot be delete') . '!');
        } else {
            /**
             * delete website-menu info
             */
            $websiteMenuInfo = $language->menuInfo()->first();

            if (!empty($websiteMenuInfo)) {
                $websiteMenuInfo->delete();
            }

            /**
             * delete hote category info
             */
            $hotelCategorys = $language->hotelCategory()->get();
            foreach ($hotelCategorys as $hotelCategory) {
                $hotelCategory->delete();
            }

            /**
             * delete hote content info
             */
            $hotelContents = $language->hotelContent()->get();
            foreach ($hotelContents as $hotelContent) {
                $hotelContent->delete();
            }

            /**
             * delete room category info
             */
            $roomCategories = $language->roomCategories()->get();
            foreach ($roomCategories as $roomCategory) {
                $roomCategory->delete();
            }

            /**
             * delete room content info
             */
            $roomContents = $language->roomContent()->get();
            foreach ($roomContents as $roomContent) {
                $roomContent->delete();
            }

            /**
             * delete service content info
             */
            $serviceContents = $language->serviceContent()->get();
            foreach ($serviceContents as $serviceContent) {
                $serviceContent->delete();
            }

            /**
             * delete amenities info
             */
            $amenities = $language->amenities()->get();
            foreach ($amenities as $amenitie) {
                $amenitie->delete();
            }

            /**
             * delete cities info
             */
            $cities = $language->cities()->get();
            foreach ($cities as $citie) {
                @unlink(public_path('assets/img/location/city/') . $citie->feature_image);
                $citie->delete();
            }
            /**
             * delete cities info
             */
            $states = $language->states()->get();
            foreach ($states as $state) {
                $state->delete();
            }
            /**
             * delete countries info
             */
            $countries = $language->countries()->get();
            foreach ($countries as $countrie) {
                $countrie->delete();
            }
            /**
             * delete features info
             */
            $features = $language->features()->get();
            foreach ($features as $feature) {
                @unlink(public_path('assets/img/') . $feature->image);
                $feature->delete();
            }
            /**
             * delete hotelCounterContents info
             */
            $hotelCounterContents = $language->hotelCounterContents()->get();
            foreach ($hotelCounterContents as $hotelCounterContent) {
                $hotelCounterContent->delete();
            }

            /**
             * delete benifits info
             */
            $benifits = $language->benifits()->get();
            foreach ($benifits as $benifit) {
                @unlink(public_path('assets/img/benifits/') . $benifit->background_image);
                $benifit->delete();
            }

            $vendorInfos = $language->vendorInfo()->get();
            foreach ($vendorInfos as $vendorInfo) {
                $vendorInfo->delete();
            }

            /**
             * delete hero-slider infos
             */
            $sliders = $language->sliderInfo()->get();

            if (count($sliders) > 0) {
                foreach ($sliders as $slider) {
                    $bgImg = $slider->background_image;

                    @unlink(public_path('assets/img/hero/sliders/') . $bgImg);
                    $slider->delete();
                }
            }

            $banners = $language->banner()->get();
            foreach ($banners as $banner) {
                @unlink(public_path('assets/img/banners/') . $banner->image);
                $banner->delete();
            }


            $counterInfos = $language->counterInfo()->get();
            foreach ($counterInfos as $counterInfo) {
                $counterInfo->delete();
            }

            /**
             * delete testimonial infos
             */
            $testimonials = $language->testimonial()->get();

            if (count($testimonials) > 0) {
                foreach ($testimonials as $testimonial) {
                    $clientImg = $testimonial->image;

                    @unlink(public_path('assets/img/clients/') . $clientImg);
                    $testimonial->delete();
                }
            }

            /**
             * delete footer-content info
             */
            $footerContentInfo = $language->footerContent()->first();

            if (!empty($footerContentInfo)) {
                $footerContentInfo->delete();
            }
            /**
             * delete footer-quick-links
             */
            $quickLinks = $language->footerQuickLink()->get();

            if (count($quickLinks) > 0) {
                foreach ($quickLinks as $quickLink) {
                    $quickLink->delete();
                }
            }
            /**
             * delete custom-page infos
             */
            $customPageInfos = $language->customPageInfo()->get();

            if (count($customPageInfos) > 0) {
                foreach ($customPageInfos as $customPageData) {
                    $customPageInfo = $customPageData;
                    $customPageData->delete();

                    // delete the custom-page if, this page does not contain any other page-content in any other language
                    $otherPageContents = PageContent::query()->where('language_id', '<>', $language->id)
                        ->where('page_id', '=', $customPageInfo->page_id)
                        ->get();

                    if (count($otherPageContents) == 0) {
                        $page = Page::query()->find($customPageInfo->page_id);
                        $page->delete();
                    }
                }
            }
            /**
             * delete blog-categories info
             */
            $blogCategories = $language->blogCategory()->get();

            if (count($blogCategories) > 0) {
                foreach ($blogCategories as $blogCategory) {
                    $blogCategory->delete();
                }
            }
            /**
             * delete blog infos
             */
            $blogInfos = $language->blogInformation()->get();

            if (count($blogInfos) > 0) {
                foreach ($blogInfos as $blogData) {
                    $blogInfo = $blogData;
                    $blogData->delete();

                    // delete the blog if, this blog does not contain any other blog-information in any other language
                    $otherBlogInfos = BlogInformation::query()->where('language_id', '<>', $language->id)
                        ->where('blog_id', '=', $blogInfo->blog_id)
                        ->get();

                    if (count($otherBlogInfos) == 0) {
                        $blog = Blog::query()->find($blogInfo->blog_id);
                        @unlink(public_path('assets/img/blogs/') . $blog->image);
                        $blog->delete();
                    }
                }
            }
            /**
             * delete faq infos
             */
            $faqs = $language->faq()->get();

            if (count($faqs) > 0) {
                foreach ($faqs as $faq) {
                    $faq->delete();
                }
            }
            /**
             * delete popup infos
             */
            $popups = $language->announcementPopup()->get();

            if (count($popups) > 0) {
                foreach ($popups as $popup) {
                    @unlink(public_path('assets/img/popups/') . $popup->image);
                    $popup->delete();
                }
            }

            $pageNames = $language->pageName()->get();
            foreach ($pageNames as $pageName) {
                $pageName->delete();
            }
            $seoInfos = $language->seoInfo()->get();
            foreach ($seoInfos as $seoInfo) {
                $seoInfo->delete();
            }
            /**
             * delete cookie-alert info
             */
            $cookieAlertInfo = $language->cookieAlertInfo()->first();

            if (!empty($cookieAlertInfo)) {
                $cookieAlertInfo->delete();
            }

            /**
             * delete about-Section info
             */
            $aboutSection = $language->aboutSection()->first();

            if (!empty($aboutSection)) {
                $aboutSection->delete();
            }
            /**
             * delete about-Section info
             */
            $SectionContent = $language->SectionContent()->first();

            if (!empty($SectionContent)) {
                $SectionContent->delete();
            }
            /**
             * delete the language json file
             */
            @unlink(resource_path('lang/') . $language->code . '.json');
            /**
             * delete the language json file for admin
             */
            @unlink(resource_path('lang/') . 'admin_' . $language->code . '.json');
            /**
             * change the  admin lang code 
             */

            $defaultLanguage = Language::query()->where('is_default', 1)->first();

            $lang_code = 'admin_' . $defaultLanguage->code;
            $code =  $defaultLanguage->code;

            /**
             * change the  admin lang code 
             */
            Admin::where('code', $language->code)
                ->update([
                    'lang_code' => $lang_code,
                    'code' => $code
                ]);
            /**
             * change the  vendor lang code 
             */
            Vendor::where('code', $language->code)
                ->update([
                    'lang_code' => $lang_code,
                    'code' => $code
                ]);

            /**
             * finally, delete the language info from db
             */
            $language->delete();

            return redirect()->back()->with('success',  __('Language deleted successfully') . '!');
        }
    }

    /**
     * Check the specified language is RTL or not.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkRTL($id)
    {
        if (!is_null($id)) {
            $defaultdirection = 0;
            $direction = Language::query()->where('id', '=', $id)
                ->pluck('direction')
                ->first();

            if (Auth::guard('admin')->check()) {
                $code = Auth::guard('admin')->user()->code;

                $defaultdirection = Language::query()->where('code', '=', $code)
                    ->pluck('direction')
                    ->first();
            }

            return response()->json([
                'successData' => $direction,
                'defaultdirection' => $defaultdirection,
            ], 200);
        } else {
            return response()->json(['errorData' => 'Sorry, an error has occured!'], 400);
        }
    }
}
