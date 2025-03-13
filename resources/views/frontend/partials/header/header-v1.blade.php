 <!-- Header-area start -->
 <header class="header-area header_v1">
   <!-- Start mobile menu -->
   <div class="mobile-menu">
     <div class="container">
       <div class="mobile-menu-wrapper"></div>
     </div>
   </div>
   <!-- End mobile menu -->

   <div class="main-responsive-nav">
     <div class="container">
       <!-- Mobile Logo -->
       <div class="logo">
         @if (!empty($websiteInfo->logo))
           <a href="{{ route('index') }}">
             <img src="{{ asset('assets/img/' . $websiteInfo->logo) }}" alt="logo">
           </a>
         @endif
       </div>
       <!-- Menu toggle button -->
       <button class="menu-toggler" type="button">
         <span></span>
         <span></span>
         <span></span>
       </button>
     </div>
   </div>

   <div class="main-navbar">
     <div class="header-top bg-primary py-15">
       <div class="container">
         <div class="header-left">
           <ul class="contact-list list-unstyled">
             <li class="icon-start">
               <a href="mailto:{{ $basicInfo->email_address }}" title="{{ $basicInfo->email_address }}">
                 <i class="fal fa-envelope"></i>
                 {{ $basicInfo->email_address }}
               </a>
             </li>
             <li class="icon-start">
               <a href="tel:{{ $basicInfo->contact_number }}" title="{{ $basicInfo->contact_number }}">
                 <i class="fal fa-user-headset"></i>{{ $basicInfo->contact_number }}
               </a>
             </li>
           </ul>
         </div>
         <div class="header-right">
           <div class="more-option mobile-item">
             <div class="item">
               <div class="dropdown">
                 <button class="btn-icon-text size-auto dropdown-toggle" type="button" data-bs-toggle="dropdown"
                   aria-expanded="false">
                   <i class="fal fa-user-circle"></i>
                   <span>
                     @if (!Auth::guard('web')->check())
                       {{ __('Customer') }}
                     @else
                       {{ Auth::guard('web')->user()->username }}
                     @endif
                   </span>
                 </button>
                 <ul class="dropdown-menu">
                   @if (!Auth::guard('web')->check())
                     <li><a class="dropdown-item" href="{{ route('user.login') }}">{{ __('Login') }}</a></li>
                     <li><a class="dropdown-item" href="{{ route('user.signup') }}">{{ __('Signup') }}</a></li>
                   @else
                     <li><a class="dropdown-item" href="{{ route('user.dashboard') }}">{{ __('Dashboard') }}</a></li>
                     <li><a class="dropdown-item" href="{{ route('user.logout') }}">{{ __('Logout') }}</a></li>
                   @endif
                 </ul>
               </div>
             </div>
             <div class="item">
               <div class="dropdown">
                 <button class="btn-icon-text size-auto dropdown-toggle" type="button" data-bs-toggle="dropdown"
                   aria-expanded="false">
                   <i class="fa fa-hotel"></i>
                   <span>{{ __('Vendor') }}</span>
                 </button>
                 <ul class="dropdown-menu">
                   @if (!Auth::guard('vendor')->check())
                     <li><a class="dropdown-item" href="{{ route('vendor.login') }}">{{ __('Login') }}</a></li>
                     <li><a class="dropdown-item" href="{{ route('vendor.signup') }}">{{ __('Signup') }}</a></li>
                   @else
                     <li><a class="dropdown-item" href="{{ route('vendor.dashboard') }}">{{ __('Dashboard') }}</a>
                     </li>

                     <li><a class="dropdown-item" href="{{ route('vendor.logout') }}">{{ __('Logout') }}</a></li>
                   @endif
                 </ul>
               </div>
             </div>
           </div>
         </div>
       </div>
     </div>
     <div class="header-bottom">
       <div class="container">
         <nav class="navbar navbar-expand-lg">
           <!-- Logo -->
           <a class="navbar-brand" href="{{ route('index') }}">
             <img src="{{ asset('assets/img/' . $websiteInfo->logo) }}" alt="logo">
           </a>
           <!-- Navigation items -->
           <div class="collapse navbar-collapse">
             @php $menuDatas = json_decode($menuInfos); @endphp
             <ul id="mainMenu" class="navbar-nav mobile-item mx-auto">
               @foreach ($menuDatas as $menuData)
                 @php $href = get_href($menuData); @endphp
                 @if (!property_exists($menuData, 'children'))
                   <li class="nav-item">
                     <a class="nav-link" href="{{ $href }}">{{ $menuData->text }}</a>
                   </li>
                 @else
                   <li class="nav-item">
                     <a class="nav-link toggle" href="{{ $href }}">{{ $menuData->text }}<i
                         class="fal fa-plus"></i></a>
                     <ul class="menu-dropdown">
                       @php $childMenuDatas = $menuData->children; @endphp
                       @foreach ($childMenuDatas as $childMenuData)
                         @php $child_href = get_href($childMenuData); @endphp
                         <li class="nav-item">
                           <a class="nav-link" href="{{ $child_href }}">{{ $childMenuData->text }}</a>
                         </li>
                       @endforeach
                     </ul>
                   </li>
                 @endif
               @endforeach
             </ul>
           </div>
           <div class="more-option mobile-item">
             <div class="item">
               <div class="language">
                 <form action="{{ route('change_language') }}" method="GET">
                   <select class="niceselect" name="lang_code" onchange="this.form.submit()">
                     @foreach ($allLanguageInfos as $languageInfo)
                       <option value="{{ $languageInfo->code }}"
                         {{ $languageInfo->code == $currentLanguageInfo->code ? 'selected' : '' }}>
                         {{ $languageInfo->name }}
                       </option>
                     @endforeach
                   </select>
                 </form>
               </div>
             </div>
           </div>
         </nav>
       </div>
     </div>
   </div>
 </header>
 <!-- Header-area end -->
