
    <div class='col-12'>
        <div class='form-wrapper shadow-sm p-3 mt-2 rounded'>
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

                    @unless ($is_dir)
                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='file_title' value='{{ $file->title }}'>
                            </div>
                        </div>
                    @endunless


                    <div class='form-group mt-3'>
                        <label class='col-sm-12 control-label-notes'>{{ trans('langComment') }}</label>
                        <div class='col-sm-12'>
                            <input class='form-control' type='text' name='file_comment' value='{{ $file->comment }}'>
                        </div>
                    </div>


                    @unless ($is_dir)
                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langCategory') }}</label>
                            <div class='col-sm-12'>
                                {!! selection($categories, 'file_category', $file->category) !!}
                            </div>
                        </div>



                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langSubject') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='file_subject' value='{{ $file->subject }}'></div>
                        </div>



                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langDescription') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='file_description' value='{{ $file->description }}'>
                            </div>
                        </div>



                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langAuthor') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='file_author' value='{{ $file->author }}'>
                            </div>
                        </div>



                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langCopyrighted') }}</label>
                            <div class='col-sm-12'>
                                {!! selection($copyrightTitles, 'file_copyrighted', $file->copyrighted) !!}
                            </div>
                        </div>



                        <div class='form-group mt-3'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                            <div class='col-sm-12'>
                                {!! selection($languages, 'file_language', $file->language) !!}
                            </div>
                        </div>
                    @endunless




                    @if($menuTypeID == 3 or $menuTypeID == 1)
                    <div class='form-group mt-3'>
                        <div class='col-12'>
                            <div class='row'>
                                <div class='col-6'>
                                    <button class='btn btn-primary btn-sm submitAdminBtn w-100' type='submit' >{{ trans('langOkComment') }}</button>
                                </div>
                                <div class='col-6'>
                                    <a class='btn btn-secondary btn-sm cancelAdminBtn w-100' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class='form-group mt-3'>
                        <div class='col-offset-2 col-10'>
                            <button class='btn btn-primary btn-sm' type='submit' >{{ trans('langOkComment') }}</button>
                            <a class='btn btn-secondary btn-sm' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                    </div>
                    @endif


                    @unless ($is_dir)
                        <div class='form-group mt-3'>
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

