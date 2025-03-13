@extends('frontend.layout')

@section('pageHeading')
  {{ !empty($pageHeading) ? $pageHeading->hotel_wishlist_page_title : __('Saved Hotels') }}
@endsection

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->hotel_wishlist_page_title : __('Saved Hotels'),
  ])

  <!--====== Start Dashboard Section ======-->
  <div class="user-dashboard pt-100 pb-60">
    <div class="container"> 
      <div class="row gx-xl-5">
        @includeIf('frontend.user.side-navbar')
        <div class="col-lg-9">
          <div class="account-info radius-md mb-40">
            <div class="title">    
              <h3>{{ __('Hotel Wishlist') }}</h3>
            </div>
            <div class="main-info">

              @if (count($wishlists) == 0)
                <h4 class="text-center">{{ __('NO HOTEL WISHLIST ITEM FOUND') . '!' }}</h4>
              @else
                <div class="main-table">
                  <div class="table-responsive">
                    <table id="myTable" class="table table-striped w-100">
                      <thead>
                        <tr>
                          <th>{{ __('Serial') }}</th>
                          <th>{{ __('Hotel title') }}</th>
                          <th>{{ __('Action') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($wishlists as $item)
                          <tr>
                            <td>#{{ $loop->iteration }}</td>
                            <td>
                              <a href="{{ route('frontend.hotel.details', ['slug' => $item->slug, 'id' => $item->hotel_id]) }}"
                                target="_blank">
                                {{ strlen(@$item->title) > 50 ? mb_substr(@$item->title, 0, 50, 'utf-8') . '...' : @$item->title }}
                              </a>
                            </td>
                            <td>
                              <a href="{{ route('frontend.hotel.details', [$item->slug, $item->hotel_id]) }}"
                                class="btn"target="_blank"><i class="fas fa-eye"></i> {{ __('View') }}</a>
                              <a href="{{ route('remove.wishlist.hotel', $item->hotel_id) }}" class="btn btn-danger"><i
                                  class="fas fa-times"></i>{{ __('Remove') }}</a>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--====== End Dashboard Section ======-->
@endsection
