<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use Illuminate\Http\Request;

class FaqController extends Controller
{
  public function faq()
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();
    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_faq', 'meta_description_faq')->first();

    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['bgImg'] = $misc->getBreadcrumb();

    $information['faqs'] = $language->faq()->orderBy('serial_number', 'asc')->get();

    return view('frontend.faq', $information);
  }
}
