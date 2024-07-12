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

                    @include('layouts.partials.show_alert') 

                    @include('layouts.partials.legend_view')
                    <button class='btn btn-primary mt-3' type='submit'>Εισαγωγή</button>

                    <div class='col-12 mt-4'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langImportH5P') }}</span></div>
                    </div>

                    <div class='col-12 mt-4'>
                        <div class='form-wrapper form-edit p-3 mt-5 rounded'>
                            <form class='form-horizontal' role='form' action='save.php' method='post' enctype='multipart/form-data'>
                                <label for='userFile' class='col-sm-6 control-label-notes'>Αρχείο : </label>
                                                <div class='col-sm-12'>
                                                    <input type='file' id='userFile' name='userFile'>
                                                </div>
                                <button class='btn btn-primary mt-3' type='submit'>Εισαγωγή</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>

</div>
</div>
@endsection
