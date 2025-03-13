<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\BasicSettings\PageHeading;
use App\Models\BasicSettings\SEO;
use App\Models\CustomPage\Page;
use App\Models\CustomPage\PageContent;
use App\Models\Language;

class PageController extends Controller
{
  public function page($slug)
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['bgImg'] = $misc->getBreadcrumb();
    $pageId = PageContent::where('slug', $slug)->firstOrFail()->page_id;

    //custom page page heading
    $pageHeading = PageHeading::select('custom_page_heading')
    ->where('language_id', $language->id)
      ->first();
    $pageHeading = isset($pageHeading->custom_page_heading) ? json_decode($pageHeading->custom_page_heading, true) : [];
    $information['title'] = (is_array($pageHeading) && isset($pageHeading[$pageId])) ? $pageHeading[$pageId] : '';

    //custom seo info
    $seoInfo = SEO::select('custome_page_meta_keyword', 'custome_page_meta_description')
    ->where('language_id', $language->id)
      ->first();
    $metaKeyword = isset($seoInfo->custome_page_meta_keyword) ? json_decode($seoInfo->custome_page_meta_keyword, true) : '';
    $metaDescription = isset($seoInfo->custome_page_meta_description) ? json_decode($seoInfo->custome_page_meta_description, true) : '';
    $information['meta_keywords'] = isset($metaKeyword[$pageId]) ? $metaKeyword[$pageId] : '';
    $information['meta_description'] = isset($metaDescription[$pageId]) ? $metaDescription[$pageId] : '';

    $checkContent = PageContent::where('language_id', $language->id)->where('page_id', $pageId)->first();
    $currentLang =  Language::where('is_default', 1)->first();
    if (empty($checkContent)) {
      session()->put('currentLocaleCode', $currentLang->code);
      return redirect()->back()->with('warning', 'Content not available. Please try another language.');
    }

    $information['pageInfo'] = Page::join('page_contents', 'pages.id', '=', 'page_contents.page_id')
      ->where('pages.status', '=', 1)
      ->where('page_contents.language_id', '=', $language->id)
      ->where('page_contents.page_id', '=', $pageId)
      ->findOrFail($pageId);
    return view('frontend.custom-page', $information);
  }
}
