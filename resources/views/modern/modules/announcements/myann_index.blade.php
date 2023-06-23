
@extends('layouts.default')

@section('content')

    <div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

            <div class="row rowMargin">

                <div class="col-12 col_maincontent_active_Homepage">
                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        {!! $action_bar !!}

                        @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @if(is_array(Session::get('message')))
                                    @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                    @foreach($messageArray as $message)
                                        {!! $message !!}
                                    @endforeach
                                @else
                                    {!! Session::get('message') !!}
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        @endif
                    
                        

                        <div class='col-12'>
                            <div class='table-responsive mt-0'>
                                <table id='ann_table_my_ann' class='announcements_table'>
                                    <thead>
                                    <tr class='notes_thead'>
                                        <th>{{ trans('langAnnouncement') }}</th>
                                        <th>{{ trans('langDate') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        
    </div>
    

@endsection
