@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='table-respansive'>
                            <table class='announcements_table'>
                                <tr class='notes_thead'>
                                    <td colspan='{{ $maxdepth + 4 }}' class='right text-white'>
                                            {{ trans('langThereAre') }}: <b>{{ $nodesCount }}</b> {{ trans('langFaculties') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan='{{ $maxdepth + 4 }}'>
                                        <div id='js-tree'></div>
                                    </td>
                                </tr>
                            </table>  
                        </div> 
                       
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection