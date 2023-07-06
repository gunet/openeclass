@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

            <div class="row rowMargin">

                <div class="col-12 col_maincontent_active_Homepage">

                        <div class="row">

                                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                                @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                                @if(isset($action_bar))
                                    {!! $action_bar !!}
                                @else
                                    <div class='mt-4'></div>
                                @endif

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
                                        
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                                @endif

                                

                                <!---------------------------------------------------------------------------------------->
                                <!---------------------------------- Include admin panels -------------------------------->

                                  @include('layouts.partials.sidebarAdmin')

                                <!----------------------------------------------------------------------------------------->
                                <!----------------------------------------------------------------------------------------->



                                @if ($is_admin)
                                    <div class='col-12 mt-4'>
                                        <div class='row row-cols-1 row-cols-lg-2 g-4'>

                                            <div class='col'>
                                                <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white h-100'>
                                                    <div class='card-header border-0 bg-white'>
                                                        <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langPlatformIdentity') }}</span>
                                                    </div>
                                                    <div class='card-body'>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-lg-6 col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langWebServerVersion') }}</strong>
                                                            </div>
                                                            <div class='col-lg-6 col-12'>
                                                                <em>{{ $_SERVER['SERVER_SOFTWARE'] }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-lg-6 col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langPHPVersion') }}</strong>
                                                            </div>
                                                            <div class='col-lg-6 col-12'>
                                                                <em>{{ PHP_VERSION }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-lg-6 col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langMySqlVersion') }}</strong>
                                                            </div>
                                                            <div class='col-6'>
                                                                <em>{{ $serverVersion }}</em>
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-lg-6 col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langVersion') }}:</strong>
                                                            </div>
                                                            <div class='col-lg-6 col-12'>
                                                                <em>{{ $siteName }} {{ ECLASS_VERSION }}</em>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class='col'>
                                                <div class='card panelCard BorderSolid px-lg-4 py-lg-3 bg-white h-100'>
                                                    <div class='card-header border-0 bg-white'>
                                                        <span class='text-uppercase normalColorBlueText TextBold fs-6'>{{ trans('langIndexInfo') }}</span>
                                                    </div>
                                                    <div class='card-body'>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-lg-6 col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langIndexNumDocs') }}:</strong>
                                                            </div>
                                                            <div class='col-lg-6 col-12'>
                                                                {{ $numDocs }}
                                                            </div>
                                                        </div>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-lg-6 col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langIndexIsOptimized') }}</strong>
                                                            </div>
                                                            <div class='col-lg-6 col-12'>
                                                                {{ $isOpt }}
                                                            </div>
                                                        </div>
                                                        @if ($idxHasDeletions)
                                                            <div class='row p-2 margin-bottom-thin'>
                                                                <div class='12'>
                                                                    <a href='../search/optpopup.php' onclick="return optpopup('../search/optpopup.php', 600, 500)">{{ trans('langOptimize') }}</a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='12'>
                                                                <a id='reindex_link' href='../search/idxpopup.php?reindex'>{{ trans('langReindex') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {!! $idxModal !!}
                                                <img src='cron.php' width='2' height='1' alt=''>
                                            </div>
                                        </div>
                                    </div>

                                @endif

                               

                                <div class='col-12 mt-4'>
                                    <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                        <div class='card-body'>
                                            <p class='text-center text-uppercase TextBold fs-5 normalColorBlueText mb-0'>{{ trans('langOnlineUsers') }} :{{ getOnlineUsers() }}</p>
                                        </div>
                                    </div>
                                </div>
                               
                                <div class='col-12 mt-4'>
                                    
                                    @php 
                                        $colSize = '';
                                        if (count($cronParams) > 0){
                                            $colSize = '2';
                                        }else{
                                            $colSize = '1';
                                        }
                                    @endphp
                                    <div class="row row-cols-1 row-cols-lg-{{ $colSize }} g-4">
                                        

                                        <div class='col'>
                                            <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                                <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                    <div class='text-uppercase normalColorBlueText TextBold fs-6'>
                                                       {{ trans('langInfoAdmin') }}
                                                    </div>
                                                </div>
                                                <div class='card-body'>
                                                    <div class='row p-2 margin-bottom-thin'>
                                                        <div class='col-12'>
                                                            <strong class='control-label-notes'>{{ trans('langOpenRequests') }}:</strong>
                                                        </div>
                                                        <div class='col-12'>
                                                            @if ($count_prof_requests)
                                                                {{ trans('langThereAre') }} {{ $count_prof_requests }} {{ trans('langOpenRequests') }}
                                                            @else
                                                                {{ trans('langNoOpenRequests') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class='row p-2 margin-bottom-thin'>
                                                        <div class='col-12'>
                                                            <strong class='control-label-notes'>{{ trans('langLastLesson') }}</strong>
                                                        </div>
                                                        <div class='col-12'>
                                                            @if ($lastCreatedCourse)
                                                                <b>{{ $lastCreatedCourse->title }}</b>
                                                                ({{ $lastCreatedCourse->code }}, {{ $lastCreatedCourse->prof_names }})
                                                            @else
                                                                {{ trans('langNoCourses') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class='row p-2 margin-bottom-thin'>
                                                        <div class='col-12'>
                                                            <strong class='control-label-notes'>{{ trans('langLastProf') }}</strong>
                                                        </div>
                                                        <div class='col-12'>
                                                            <b>{{ $lastProfReg->givenname . " " . $lastProfReg->surname }}</b>
                                                            ({{ $lastProfReg->username }}, {{ date("j/n/Y H:i", strtotime($lastProfReg->registered_at)) }})
                                                        </div>
                                                    </div>
                                                    <div class='row p-2 margin-bottom-thin'>
                                                        <div class='col-12'>
                                                            <strong class='control-label-notes'>{{ trans('langLastStud') }}</strong>
                                                        </div>
                                                        <div class='col-12'>
                                                            @if ($lastStudReg)
                                                                <b>{{ $lastStudReg->givenname . " " . $lastStudReg->surname }}</b>
                                                                ({{ $lastStudReg->username . ", " . date("j/n/Y H:i", strtotime($lastStudReg->registered_at)) }})
                                                            @else
                                                                {{ trans('langLastStudNone') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class='row p-2 margin-bottom-thin'>
                                                        <div class='col-12'>
                                                            <strong class='control-label-notes'>{{ trans('langAfterLastLoginInfo') }}</strong>
                                                        </div>
                                                        <div class='col-12'>
                                                            {{ trans('langAfterLastLogin') }}
                                                            <ul class='custom_list'>
                                                            <li>
                                                                <b>{{ $lastregisteredprofs }}</b>
                                                                {{ trans('langTeachers') }}
                                                            </li>
                                                            <li>
                                                                <b>{{ $lastregisteredstuds }}</b>
                                                                {{ trans('langStudents') }}
                                                            </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if (count($cronParams) > 0)
                                            <div class='col'>
                                                <div class='card panelCard px-lg-4 py-lg-3 h-100'>

                                                    <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                                        <div class='text-uppercase normalColorBlueText TextBold fs-6'>
                                                            {{ trans('langCronInfo') }}
                                                        </div>
                                                    </div>
                                                    <div class='card-body'>
                                                        <div class='row p-2 margin-bottom-thin'>
                                                            <div class='col-12'>
                                                                <strong class='control-label-notes'>{{ trans('langCronName') }}</strong>
                                                            </div>
                                                            <div class='col-12'>
                                                                {{ trans('langCronLastRun') }}
                                                                <div class='row p-2'>
                                                                    @foreach ($cronParams as $cronParam)
                                                                        <div class='col-lg-6 col-12'>{{ $cronParam->name }}</div>
                                                                        <div class='col-lg-6 col-12'>{{ $cronParam->last_run }}</div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                        </div>
                </div>
            </div>
       
</div>
@endsection
