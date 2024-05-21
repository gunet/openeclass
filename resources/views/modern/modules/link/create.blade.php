@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')
                    
                    
                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    

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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    
                    
                    
                    <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                        
                        <div class='form-wrapper form-edit rounded'>
                            
                            <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}&amp;urlview={{ $urlview }}'>
                                @if ($action == 'editlink')
                                    <input type='hidden' name='id' value='{{ getIndirectReference($id) }}'>
                                @endif
                                <fieldset>
                                    
                                    <div class='form-group{{ $urlLinkError ? " has-error" : "" }}'>
                                        <label for='urllink' class='col-sm-6 control-label-notes'>URL</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' id='urllink' name='urllink' value="{{ isset($link) ? $link->url : "" }}">
                                                {!! Session::getError('urllink', "<span class='help-block Accent-200-cl'>:message</span>") !!}
                                            </div>
                                        </div>

                                       

                                        <div class='form-group mt-4'>
                                            <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langLinkName') }}</label>
                                            <div class='col-sm-12'>
                                                <input class='form-control' type='text' id='title' name='title' value="{{ isset($link) ? $link->title : "" }}">
                                            </div>
                                        </div>

                                       


                                        <div class='form-group mt-4'>
                                            <label for='description' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                            <div class='col-sm-12'>{!! $description_textarea !!}</div>
                                        </div>

                                        

                                        <div class='form-group mt-4'>
                                            <label for='selectcategory' class='col-sm-6 control-label-notes'>{{ trans('langCategory') }}</label>
                                            <div class='col-sm-12'>
                                                <select class='form-select' name='selectcategory' id='selectcategory'>
                                                    @if ($is_editor)
                                                        <option value='{{ getIndirectReference(0) }}'>--</option>
                                                    @endif
                                                    @if ($social_bookmarks_enabled)
                                                        <option value='{{ getIndirectReference(-2) }}'{{ isset($category) && $category == -2 ? " selected": "" }}>{{ trans('langSocialCategory') }}</option>
                                                    @endif
                                                    @if ($is_editor)
                                                        @foreach ($categories as $row)
                                                            <option value='{{ getIndirectReference($row->id) }}'{{ isset($category) && $category == $row->id ? " selected": "" }}>{{ $row->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                       

                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                                <input type='submit' class='btn submitAdminBtn' name='submitLink' value='{{ $submit_label }}' />
                                                <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                            </div>
                                        </div>
                                </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                        </div>
                
                </div>
            </div>

        </div>
   
</div>        
</div>       
@endsection

