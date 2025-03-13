  <!-- Dashboard-area end -->
  <div class="modal fade roombokmodal" id="roombokmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="roombokmodalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">{{ __('Services') }} </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th scope="col">{{ __('sl') . '#' }}</th>
                <th scope="col">{{ __('Service') }}</th>
                <th scope="col">{{ __('Price') }}</th>
              </tr>
            </thead>
            <tbody>
              @php
                $sl = 1;
              @endphp

              @foreach ($additional_services as $service)
                <tr>
                  <td> {{ $sl++ }} </td>
                  <td>{{ $service->service_name }}</td>
                  <td>{{ $service->price }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('Close') }}</button>
        </div>
      </div>
    </div>
  </div>
