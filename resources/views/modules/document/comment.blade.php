<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
			<form class='form-horizontal' role='form' method='post' action='{{ $base_url }}'>
                <input type='hidden' name='commentPath' value='{{ getIndirectReference($file->path) }}'>
                <fieldset>
                    {!! $group_hidden_input !!}
                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{
                            $is_dir? trans('langDirectory') : trans('langWorkFile') }}:</label>
                        <div class='col-sm-10'>
                            <p class='form-control-static'>{{ $file->filename }}</p>
                        </div>
                    </div>

                    @unless ($is_dir)
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='file_title' value='{{ $file->title }}'>
                            </div>
                        </div>
                    @endunless

                    <div class='form-group'>
                        <label class='col-sm-2 control-label'>{{ trans('langComment') }}:</label>
                        <div class='col-sm-10'>
                            <input class='form-control' type='text' name='file_comment' value='{{ $file->comment }}'>
                        </div>
                    </div>

                    @unless ($is_dir)
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langCategory') }}:</label>
                            <div class='col-sm-10'>
                                {!! selection($categories, 'file_category', $file->category) !!}
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langSubject') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='file_subject' value='{{ $file->subject }}'></div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langInfoAbout') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='file_description' value='{{ $file->description }}'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langAuthor') }}:</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='file_author' value='{{ $file->author }}'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langCopyrighted') }}:</label>
                            <div class='col-sm-10'>
                                {!! selection($copyright_titles, 'file_copyrighted', $file->copyrighted) !!}
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label'>{{ trans('langLanguage') }}:</label>
                            <div class='col-sm-10'>
                                {!! selection($languages, 'file_language', $file->language) !!}
                            </div>
                        </div>
                    @endunless

                    <div class='form-group'>
                        <div class='col-xs-offset-2 col-xs-10'>
                            <button class='btn btn-primary' type='submit'>{{ trans('langOkComment') }}</button>
                            <a class='btn btn-default' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                        </div>
                    </div>

                    @unless ($is_dir)
                        <div class='form-group'>
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
</div>
