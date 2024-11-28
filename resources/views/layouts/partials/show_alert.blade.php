@if(Session::has('message'))
    <div class='col-12 all-alerts'>
        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
            @php
                $alert_type = '';
                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                }else{
                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                }
            @endphp

            @if(is_array(Session::get('message')))
                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                {!! $alert_type !!}<span>
                @foreach($messageArray as $message)
                    {!! $message !!}
                @endforeach</span>
            @else
                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
            @endif

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('langClose') }}"></button>
        </div>
    </div>
@endif

@if (Session::hasMessages())
  <div class='col-12 all-alerts'>
    @foreach (Session::getMessages() as $alert_class => $alert_messages)
      <div class="alert {{ $alert_class }} alert-dismissible fade show" role="alert">
        <i class='fa-solid @switch ($alert_class)
          @case('alert-success') fa-circle-check @break
          @case('alert-info') fa-circle-info @break
          @case('alert-warning') fa-triangle-exclamation @break
          @default fa-circle-xmark @endswitch fa-circle-check fa-lg'></i>
        @if (count($alert_messages) > 1)
          <ul>
            @foreach ($alert_messages as $alert_message)
              <li>{!! $alert_message !!}</li>
            @endforeach
          </ul>
        @else
          {!! $alert_messages[0] !!}
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ trans('langClose') }}"></button>
      </div>
    @endforeach
  </div>
@endif
