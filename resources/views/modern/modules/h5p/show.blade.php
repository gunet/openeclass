@extends('layouts.default_old')

@push('head_scripts')
<link type="text/css" rel="stylesheet" media="all" href="{{ $urlServer }}/js/h5p-standalone/styles/h5p.css" />
<script type="text/javascript" src="{{ $urlServer }}/js/h5p-standalone/js/h5p-standalone-main.min.js"></script>
@endpush

@section('content')

{!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
			<div class="h5p-container"></div>
        </div>
    </div>
	
  <script type="text/javascript">
    (function($) {
      $(function() {
        $('.h5p-container').h5p({
          frameJs: '{{ $urlServer }}/js/h5p-standalone/js/h5p-standalone-frame.min.js',
          frameCss: '{{ $urlServer }}/js/h5p-standalone/styles/h5p.css',
          h5pContent: '{{ $workspaceUrl }}',
          displayOptions: {
            frame: true,
            copyright : true,
            embed: false,
            download: false,
            icon: true,
            export: false
          }
        });
      });
    })(H5P.jQuery);
  </script>
@endsection
