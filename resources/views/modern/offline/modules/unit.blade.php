@extends('layouts.default')

@section('content')
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">
    <div class="container-fluid main-container">
        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='row'>
                        <div class='col-md-12'>
                            <h2 class='page-subtitle'>
                                {{ trans('langCourseUnits') }}
                            </h2>
                        </div>
                    </div>

                    <div class='row'>
                        <div class='col-md-12'>
                            <div class='form-wrapper course_units_pager clearfix'>
                                @if (!empty($prev_unit_title))
                                    <a class='pull-left' title='{{ $prev_unit_title }}' href='{{ $prev_unit_link }}'><i class='fa fa-arrow-left space-after-icon'></i>{{ $prev_unit_title }}</a>
                                @else
                                    &nbsp;
                                @endif
                                @if (!empty($next_unit_title))
                                    <a class='float-end' title='{{ $next_unit_title }}' href='{{ $next_unit_link }}'>{{ $next_unit_title }}<i class='fa fa-arrow-right space-before-icon'></i></a>
                                @else
                                    &nbsp;
                                @endif
                            </div>
                        </div>
                    </div>


                  <div class='row'>
                    <div class='col-md-12'>
                      <div class='panel panel-default'>
                        <div class='panel-body'>
                          <div class='inner-heading'>
                            {{ $course_unit_title }}
                          </div>
                          <div>
                            <p>{!! $course_unit_comments !!}</p>
                          </div>

                          <div class='unit-resources'>
                              <div class='table-responsive'>
                                <table class='table table-striped table-hover'>
                                  <tbody>
                                      @foreach ($unit_resources as $r)
                                        <tr>
                                            <td width='1'><span class='{{ get_unit_resource_icon($r->type, $r->res_id) }}'></span></td>
                                            <td><a href='{{ get_unit_resource_link($r->type, $r->res_id) }}' target='_blank'>{{ $r->title }}</a><br /><p>{!! $r->comments !!}</p></td>
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
