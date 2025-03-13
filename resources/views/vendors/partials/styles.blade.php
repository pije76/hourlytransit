{{-- fontawesome css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/all.min.css') }}">

{{-- fontawesome icon picker css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/fontawesome-iconpicker.min.css') }}">

{{--  icon picker css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/fonts.min.css') }}">

{{-- bootstrap css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">

{{-- bootstrap tags-input css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-tagsinput.css') }}">

{{-- jQuery-ui css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/jquery-ui.min.css') }}">

{{-- jQuery-timepicker css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/jquery.timepicker.min.css') }}">

{{-- bootstrap-datepicker css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-datepicker.min.css') }}">

{{-- dropzone css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/dropzone.min.css') }}">

{{-- atlantis css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/atlantis.css') }}">

{{-- select2 css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">

<!-- Date-range Picker -->
<link rel="stylesheet" href="{{ asset('assets/front/css/vendors/daterangepicker.css') }}">

{{-- admin-main css --}}
<link rel="stylesheet" href="{{ asset('assets/admin/css/admin-main.css') }}">

@if (!empty($defaultLang) && $defaultLang->direction == 1)
  <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-rtl.css') }}">
@endif
