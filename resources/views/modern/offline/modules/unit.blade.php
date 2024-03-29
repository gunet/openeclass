@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active col_maincontent_active_module_content">

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

                   
                    <div class='col-12'>
                        <h2 class='page-subtitle'>
                            {{ trans('langCourseUnits') }}
                        </h2>
                    </div>
                   

                    
                    
                    


                    
                    <div class='col-12 mt-4'>
                        <div class='card panelCard px-lg-4 py-lg-3'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                {{ $course_unit_title }}
                            </div>
                            <div class='card-body'>

                                <div class='col-12 col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4'>
                                    @if (!empty($prev_unit_title))
                                        <a title='{{ $prev_unit_title }}' href='{{ $prev_unit_link }}'><i class='fa fa-arrow-left space-after-icon'></i>{{ $prev_unit_title }}</a>
                                    @endif
                                    @if (!empty($next_unit_title))
                                        <a title='{{ $next_unit_title }}' href='{{ $next_unit_link }}'>{{ $next_unit_title }}<i class='fa fa-arrow-right space-before-icon'></i></a>
                                    @endif
                                </div>

                                <div class='mt-2'>
                                    <p>{!! $course_unit_comments !!}</p>
                                </div>

                                <div class='unit-resources mt-2'>
                                    <div class='table-responsive'>
                                        <table class='table table-striped table-hover table-default'>
                                        <tbody>
                                            @foreach ($unit_resources as $r)
                                                <tr>
                                                    <td width='1'><span class='{{ get_unit_resource_icon($r->type, $r->res_id) }}'></span></td>
                                                    <td><a href='{{ get_unit_resource_link($r->type, $r->res_id) }}' target='_blank' aria-label='(opens in a new tab)'>{{ $r->title }}</a><br /><p>{!! $r->comments !!}</p></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
   
</div>
</div>

    <!-- <div class='row'>
        <div class='col-md-12'>
            <div class='form-wrapper'>
                    <form class='form-horizontal' name='unitselect' action='http://hobit.noc.uoa.gr/openeclass/modules/units/' method='get'>
                        <div class='form-group'>
                            <label class='col-sm-8 control-label'>Θεματικές Ενότητες</label>
                            <div class='col-sm-4'>
                                <label class='hidden' for='id'>Θεματικές Ενότητες</label>
                                <select name='id' id='id' class='form-control' onChange='document.unitselect.submit();'>
                                    <option value='1' selected >θέμα 1</option><option value='2'>Θέμα 2</option><option value='5'>titlos</option><option value='3'>θέμα 3</option><option value='27'>μια νέα ενότητα</option><option value='4'>Αυτοκίνητο</option><option value='28'>άλλο #1</option><option value='29'>άλλο #2</option><option value='227'>αυτοκίνητο</option>
                                </select>
                            </div>
                        </div>
                    </form>
            </div>
        </div>
    </div>
-->
@endsection
