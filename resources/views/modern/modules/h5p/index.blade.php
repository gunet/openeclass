

@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

			<div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

				    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>


                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

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

                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5">
						<span class="control-label-notes">
							{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small>
						</span>
					</div>

					@if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

						@if($is_editor)
							{!! $tool_content !!}
						@endif


						@if($content)
							<table class="announcements_table">
								<thead>
									<tr class="notes_thead">
										<th class="text-left text-white">H5P</th>
										<th class="text-center text-white" style="width:109px;">
											<span class="fa fa-cogs"></span>
										</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($content as $item)
										<tr>
											<td>
												<a href='view.php?course={{ $course_code }}&amp;id={{ $item->id }}'>{{ $item->title }}</a>
											</td>
											<td class='text-center'>
												@if ($is_editor)
													{!! action_button([
														[ 'icon' => 'fa-times',
														'title' => trans('langDelete'),
														'url' => "delete.php?course=$course_code&amp;id=$item->id",
														'class' => 'delete',
														'confirm' => trans('langConfirmDelete') ]
														], false) !!}
												@else
													&nbsp;
												@endif
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						@else
							<div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
								<div class='alert alert-warning'>
									{{ trans('langNoH5PContent') }}
								</div>
							</div>
						@endif

					</div>
				</div>


		</div>
	</div>
</div>

@endsection
