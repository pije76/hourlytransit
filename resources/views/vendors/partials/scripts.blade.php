<script>
  'use strict';
  let demo_mode = "{{ env('DEMO_MODE') }}";
  const baseUrl = "{{ url('/') }}";
  const adminApproveNotice = {!! json_encode($settings) !!};
  var curr_url = "{{ url()->current() . '?language=' }}";
  var Areyousure = "{{ __('Are you sure') . '?' }}";
  var Youwontbeabletorevertthis = "{!! __('You won\'t be able to revert this') !!}";
  var Yesdeleteit = "{{ __('Yes, delete it') . '.' }}";
  var Yesunfeatureit = "{{ __('Yes, unfeature it') . '.' }}";
  var Youwanttoclosethisticket = "{{ __('You want to close this ticket') . '?' }}";
  var Yescloseit = "{{ __('Yes, close it') . '.' }}";
  var Cancel = "{{ __('Cancel') }}";
  var YourPackagelimitreachedorexceeded = "{{ __('Your Package limit reached or exceeded') . '!' }}";
  var PleaseBuyaplantoaddaticket = "{{ __('Please Buy a plan to add a ticket') . '!' }}";
  var PleaseBuyaplantoaddaHotel = "{{ __('Please Buy a plan to add a Hotel') . '!' }}";
  var Hotellimitreachedorexceeded = "{{ __('Hotel limit reached or exceeded') . '!' }}";
  var PleaseBuyaplantoaddaroom = "{{ __('Please Buy a plan to add a room') . '!' }}";
  var Roomlimitreachedorexceeded = "{{ __('Room limit reached or exceeded') . '!' }}";
  var Alert = "{{ __('Alert') }}";
  var Ifyoudeletethispackagealmembershipsunderthispackagewillbedeleted =
    "{{ __('If you delete this package, all memberships under this package will be deleted') . '.' }}";
  let sucessText = "{{ __('Success') }}";
  let warningText = "{{ __('Warning') }}";

  @if ($settings->time_format == 24)
    var timePicker = true;
    var timeFormate = "HH:mm";
  @elseif ($settings->time_format == 12)
    var timePicker = false;
    var timeFormate = "hh:mm A";
  @endif
</script>

{{-- core js files --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/popper.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/bootstrap.min.js') }}"></script>

{{-- jQuery ui --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/admin/js/jquery.ui.touch-punch.min.js') }}"></script>

{{-- jQuery time-picker --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/jquery.timepicker.min.js') }}"></script>

{{-- jQuery scrollbar --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/jquery.scrollbar.min.js') }}"></script>

{{-- bootstrap date-picker --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/bootstrap-datepicker.min.js') }}"></script>


{{-- bootstrap notify --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/bootstrap-notify.min.js') }}"></script>

{{-- sweet alert --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/sweet-alert.min.js') }}"></script>

{{-- bootstrap tags input --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/bootstrap-tagsinput.min.js') }}"></script>

{{-- bootstrap date-picker --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/bootstrap-datepicker.min.js') }}"></script>
{{-- js color --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/jscolor.min.js') }}"></script>

{{-- fontawesome icon picker js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/fontawesome-iconpicker.min.js') }}"></script>

{{-- datatables js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/datatables-1.10.23.min.js') }}"></script>

{{-- datatables bootstrap js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/datatables.bootstrap4.min.js') }}"></script>

{{-- tinymce editor --}}
<script src="{{ asset('assets/admin/js/tinymce/js/tinymce/tinymce.min.js') }}"></script>

{{-- dropzone js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/dropzone.min.js') }}"></script>

{{-- atlantis js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/atlantis.js') }}"></script>

<!-- Date-range Picker JS -->
<script src="{{ asset('assets/front/js/vendors/moment.min.js') }}"></script>
<script src="{{ asset('assets/front/js/vendors/daterangepicker.js') }}"></script>

{{-- fonts and icons script --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/webfont.min.js') }}"></script>

@if (session()->has('success'))
  <script>
    'use strict';
    var content = {};

    content.message = '{{ session('success') }}';
    content.title = sucessText;
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'success',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif

@if (session()->has('warning'))
  <script>
    'use strict';
    var content = {};

    content.message = '{{ session('warning') }}';
    content.title = warningText;
    content.icon = 'fa fa-bell';

    $.notify(content, {
      type: 'warning',
      placement: {
        from: 'top',
        align: 'right'
      },
      showProgressbar: true,
      time: 1000,
      delay: 4000
    });
  </script>
@endif

<script>
  'use strict';
  const account_status = "{{ Auth::guard('vendor')->user()->status }}";
  const secret_login = "{{ Session::get('secret_login') }}";
</script>

{{-- select2 js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/select2.min.js') }}"></script>

{{-- admin-main js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/admin-main.js') }}"></script>
{{-- admin-partial js --}}
<script type="text/javascript" src="{{ asset('assets/admin/js/admin-partial.js') }}"></script>
