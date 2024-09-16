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