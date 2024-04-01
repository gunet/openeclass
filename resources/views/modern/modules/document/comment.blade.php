
<div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4 dialog_box'>
    <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
        <div class='form-wrapper form-edit mt-2 border-0 px-0'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                <input type='hidden' name='commentPath' value='{{$file->path}}'>
                <fieldset>
                    {!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-sm-12 control-label-notes'>{{
                            $is_dir? trans('langDirectory') : trans('langWorkFile') }}</label>
                        <div class='col-sm-12'>
                            <p class='form-control-static'>{{ $file->filename }}</p>
                        </div>
                    </div>

                    <div class='row'>
                        @unless ($is_dir)
                        <div class='col-12'>
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='file_title' value='{{ $file->title }}'>
                                </div>
                            </div>
                        </div>
                        @endunless

                        <div class='col-12'>
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langComment') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='file_comment' value='{{ $file->comment }}'>
                                </div>
                            </div>
                        </div>
                    </div>


                    @unless ($is_dir)
                        <div class='row'>
                            <div class='col-12'>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langCategory') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($categories, 'file_category', $file->category) !!}
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langSubject') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='file_subject' value='{{ $file->subject }}'></div>
                                </div>
                            </div>
                        </div>


                        <div class='row'>
                            <div class='col-12'>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='file_description' value='{{ $file->description }}'>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langAuthor') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='file_author' value='{{ $file->author }}'>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class='row'>
                            <div class='col-12'>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langCopyrighted') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($copyrightTitles, 'file_copyrighted', $file->copyrighted) !!}
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                    <div class='col-sm-12'>
                                        {!! selection($languages, 'file_language', $file->language) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endunless





                    <div class='form-group mt-4'>
                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                            <button class='btn submitAdminBtn' type='submit' >
                                {{ trans('langOkComment') }}
                            </button>
                            <a class='btn cancelAdminBtn' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                    </div>



                    @unless ($is_dir)
                        <div class='form-group mt-4'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <span class='help-block'>{{ trans('langNotRequired') }}</span>
                            </div>
                        </div>
                    @endunless
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
			</form>
        </div>
    </div>
    <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
        <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
    </div>
</div>

