<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\BasicSettings\Basic;
use App\Models\Journal\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{

  public function index(Request $request)
  {
    $misc = new MiscellaneousController();

    $language = $misc->getLanguage();

    $information['seoInfo'] = $language->seoInfo()->select('meta_keyword_blog', 'meta_description_blog')->first();

    $information['pageHeading'] = $misc->getPageHeading($language);


    $information['bgImg'] = $misc->getBreadcrumb();

    $blogTitle = $blogCategory = null;

    if ($request->filled('title')) {
      $blogTitle = $request['title'];
    }
    if ($request->filled('category')) {
      $blogCategory = $request['category'];
    }

    $information['blogs'] = Blog::join('blog_informations', 'blogs.id', '=', 'blog_informations.blog_id')
      ->join('blog_categories', 'blog_categories.id', '=', 'blog_informations.blog_category_id')
      ->where('blog_informations.language_id', '=', $language->id)
      ->where('blogs.status', '=', 1)
      ->when($blogTitle, function ($query, $blogTitle) {
        return $query->where('blog_informations.title', 'like', '%' . $blogTitle . '%');
      })
      ->when($blogCategory, function ($query, $blogCategory) {
        return $query->where('blog_categories.slug', 'like', '%' . $blogCategory . '%');
      })
      ->select('blogs.image', 'blogs.id', 'blog_categories.name as categoryName', 'blog_categories.slug AS categorySlug', 'blog_informations.title', 'blog_informations.slug', 'blog_informations.author', 'blogs.created_at', 'blog_informations.content')
      ->orderBy('blogs.serial_number', 'asc')
      ->paginate(4);
    $information['categories'] = $this->getCategories($language);

    $information['allBlogs'] = $language->blogInformation()->count();

    return view('frontend.journal.blog', $information);
  }

  public function details($slug, $id)
  {
    $misc = new MiscellaneousController();
    $language = $misc->getLanguage();
    $information['pageHeading'] = $misc->getPageHeading($language);

    $information['bgImg'] = $misc->getBreadcrumb();

    $details = Blog::join('blog_informations', 'blogs.id', '=', 'blog_informations.blog_id')
      ->join('blog_categories', 'blog_categories.id', '=', 'blog_informations.blog_category_id')
      ->where('blog_informations.language_id', '=', $language->id)
      ->where('blogs.status', '=', 1)
      ->where('blog_informations.blog_id', '=', $id)
      ->select('blogs.id', 'blogs.image', 'blogs.created_at', 'blog_informations.title', 'blog_informations.content', 'blog_informations.meta_keywords', 'blog_informations.meta_description', 'blog_categories.name as categoryName', 'blog_categories.slug as categorySlug')
      ->firstOrFail();

    $information['details'] = $details;

    $information['recent_blogs'] = Blog::join('blog_informations', 'blogs.id', '=', 'blog_informations.blog_id')
      ->where('blog_informations.language_id', '=', $language->id)
      ->where('blogs.id', '!=', $details->id)
      ->select('blogs.image', 'blogs.id',  'blog_informations.title', 'blog_informations.slug', 'blog_informations.author', 'blogs.created_at', 'blog_informations.content')
      ->orderBy('blogs.serial_number', 'asc')
      ->limit(3)->get();

    $information['disqusInfo'] = Basic::select('disqus_status', 'disqus_short_name')->firstOrFail();

    $information['categories'] = $this->getCategories($language);

    $information['allBlogs'] = $language->blogInformation()->count();

    return view('frontend.journal.blog-details', $information);
  }
  public function getCategories($language)
  {
    $categories = $language->blogCategory()->where('status', 1)->orderBy('serial_number', 'asc')->get();

    $categories->map(function ($category) {
      $category['blogCount'] = $category->blogInfo()->count();
    });

    return $categories;
  }
}
