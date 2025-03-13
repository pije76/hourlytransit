@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->pricing_page_title }}
  @else
    {{ __('Pricing') }}
  @endif
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_pricing }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_pricing }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->pricing_page_title : __('Pricing'),
  ])
  <!-- Page title end-->

  <section class="pricing-area pricing-area_v1 pt-100 pb-70">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="section-title title-center mb-50" data-aos="fade-up">
            {{-- <h2 class="title mb-20">Great Transparent Plan</h2> --}}
            <div class="tabs-navigation tabs-navigation_v3">
              <ul class="nav nav-tabs radius-md" data-hover="fancyHover">

                @foreach ($terms as $term)
                  <li class="nav-item {{ $loop->first ? 'active' : '' }}">
                    <button class="nav-link hover-effect btn-md radius-sm {{ $loop->first ? 'active' : '' }}"
                      data-bs-toggle="tab" data-bs-target="#{{ strtolower($term) }}" type="button">
                      {{ __($term) }}
                    </button>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
          <div class="tab-content" data-aos="fade-up">

            @foreach ($terms as $term)
              <div class="tab-pane slide {{ $loop->first ? 'active show' : '' }}" id="{{ strtolower($term) }}">
                <div class="row justify-content-center">
                  @php
                    $packages = \App\Models\Package::where('status', '1')->where('term', strtolower($term))->get();
                  @endphp
                  @foreach ($packages as $package)
                    @php
                      $permissions = $package->features;
                      if (!empty($package->features)) {
                          $permissions = json_decode($permissions, true);
                      }
                    @endphp
                    <div class="col-md-6 col-lg-4 item">
                      <div class="card p-30 mb-30 radius-lg border {{ $package->recommended ? 'active' : '' }}">
                        <div class="card_top">
                          <div class="card_icon">
                            <i class="{{ $package->icon }}"></i>
                          </div>
                          <div class="pricing_card_header d-flex flex-column">
                            <h3 class="card_title mb-0">{{ __($package->title) }}</h3>
                            @if ($package->recommended == '1')
                              <span>{{ __('Popular') }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="card_subtitle mt-15">
                          <h4 class="mb-0">{{ symbolPrice($package->price) }}
                            @if ($package->term == 'monthly')
                              / {{ __('Month') }}
                            @elseif($package->term == 'yearly')
                              / {{ __('Year') }}
                            @elseif($package->term == 'lifetime')
                              / {{ __('Lifetime') }}
                            @endif
                          </h4>
                        </div>
                        <ul class="card_list toggle-list list-unstyled mt-25" data-toggle-list="pricingToggle"
                          data-toggle-show="5">
                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_hotel == 999999)
                                {{ __('Hotels') }}
                              @elseif($package->number_of_hotel == 1)
                                {{ __('Hotel') }}
                              @else
                                {{ __('Hotels') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_hotel == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_hotel }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_room == 999999)
                                {{ __('Rooms') }}
                              @elseif($package->number_of_room == 1)
                                {{ __('Room') }}
                              @else
                                {{ __('Rooms') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_room == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_room }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_amenities_per_hotel == 999999)
                                {{ __('Amenities Per Hotel') }}
                              @elseif($package->number_of_amenities_per_hotel == 1)
                                {{ __('Amenitie Per Hotel') }}
                              @else
                                {{ __('Amenities Per Hotel') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_amenities_per_hotel == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_amenities_per_hotel }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_amenities_per_room == 999999)
                                {{ __('Amenities Per Room') }}
                              @elseif($package->number_of_amenities_per_room == 1)
                                {{ __('Amenitie Per Room') }}
                              @else
                                {{ __('Amenities Per Room') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_amenities_per_room == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_amenities_per_room }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_images_per_hotel == 999999)
                                {{ __('Images Per Hotel') }}
                              @elseif($package->number_of_images_per_hotel == 1)
                                {{ __('Image Per Hotel') }}
                              @else
                                {{ __('Images Per Hotel') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_images_per_hotel == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_images_per_hotel }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_images_per_room == 999999)
                                {{ __('Images Per Room') }}
                              @elseif($package->number_of_images_per_room == 1)
                                {{ __('Image Per Room') }}
                              @else
                                {{ __('Images Per Room') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_images_per_room == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_images_per_room }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_bookings == 999999)
                                {{ __('Number of Bookings') }}
                              @elseif($package->number_of_bookings == 1)
                                {{ __('Number of Booking') }}
                              @else
                                {{ __('Number of Bookings') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_bookings == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_bookings }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Add Booking From Dashboard', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Add Booking From Dashboard') }}</span>
                          </li>
                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Edit Booking From Dashboard', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Edit Booking From Dashboard') }}</span>
                          </li>
                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Support Tickets', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Support Tickets') }}</span>
                          </li>


                          @if (!is_null($package->custom_features))
                            @php
                              $features = explode("\n", $package->custom_features);
                            @endphp
                            @if (count($features) > 0)
                              @foreach ($features as $key => $value)
                                <li><span><i class="fal fa-check"></i>{{ __($value) }}</span>
                                </li>
                              @endforeach
                            @endif
                          @endif
                        </ul>
                        <span class="show-more mt-15" data-toggle-btn="toggleListBtn">
                          {{ __('Show More') . '+' }}
                        </span>
                        <div class="card_action mt-25">
                          @auth('vendor')
                            <a href="{{ route('vendor.plan.extend.checkout', $package->id) }}"
                              class="btn btn-lg btn-primary radius-sm w-100" title="{{ __('Apply Now') }}"
                              target="_self">{{ __('Purchase') }}</a>
                          @endauth
                          @guest('vendor')
                            <a href="{{ route('vendor.login', ['redirectPath' => 'buy_plan', 'package' => $package->id]) }}"
                              class="btn btn-lg btn-primary radius-sm w-100" title="{{ __('Apply Now') }}"
                              target="_self">{{ __('Purchase') }}</a>
                          @endguest
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
