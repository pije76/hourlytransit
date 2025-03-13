<?php

namespace App\Http\Controllers\Admin\BasicSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PageHeadingRequest;
use App\Models\BasicSettings\PageHeading;
use App\Models\CustomPage\Page;
use App\Models\Language;
use Illuminate\Support\Facades\Session;

class PageHeadingController extends Controller
{
    public function pageHeadings(Request $request)
    {
        // first, get the language info from db
        $language = Language::query()->where('code', '=', $request->language)->firstOrFail();
        $information['language'] = $language;

        //additional page
        $information['pages'] = Page::query()->get();
        // then, get the page headings info of that language from db
        $information['data'] = $language->pageName()->first();

        $information['decodedHeadings'] = json_decode($information['data']->custom_page_heading, true);
        // get all the languages from db
        $information['langs'] = Language::all();

        return view('admin.basic-settings.page-headings', $information);
    }

    public function updatePageHeadings(Request $request)
    {
        // first, get the language info from db
        $language = Language::query()->where('code', '=', $request->language)->first();
        // then, get the page heading info of that language from db
        $heading = $language->pageName()->first();

        if (empty($heading)) {
            PageHeading::query()->create($request->except('language_id') + [
                'language_id' => $language->id
            ]);
        } else {
            $heading->update($request->all());
        }

        Session::flash('success', __('Page headings updated successfully') . '!');

        return redirect()->back();
    }
}
