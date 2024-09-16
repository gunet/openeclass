
@extends('layouts.default')

@section('content')

<div class="row back-navbar-eclass"></div>
<div class="row back-navbar-eclass2"></div>


   <script type="text/javascript" src="{{ $urlAppend }}js/notes_color_header.js"></script>
   <div class="pb-5">
        <div class="container-fluid notes_container">
            <div class="row">
                <div class="col-xl-12 col-lg-8 col-md-12 col-sm-6 col-xs-6 justify-content-center">
                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5" style="margin-top:-20px;">

                    @include('layouts.partials.legend_view')

                        <div class="row p-2"></div>
                        <div class="row p-2"></div>
                        <div class="row p-2"></div>

                        <form class="form_fieldset_addnotes" action="{{ $urlAppend }}main/notes/index.php" method="POST">

                            <div class="form-group">
                                <label for="newTitle" class="col-sm-2 control-label-notes">{{ trans('langTitle') }}</label>
                                <div class="col-sm-12">
                                    <input name="newTitle" type="text" class="form-control" id="newTitle" value="{{ trans('titleToModify') }}" placeholder="{{ trans('langTitle') }}">
                                    <span class='help-block'><?php echo Session::getError('newTitle') ?></span>
                                </div>
                            </div>


                            <div class="row p-2"></div>
                            <div class="row p-2"></div>
                            <div class="row p-2"></div>


                            <div class='form-group'>
                                <label for='refobjgentype' class='col-sm-2 control-label-notes'>{{ trans('langReferencedObject') }}</label>
                                <div class='col-sm-12'>
                                    {!! $build_object_referennce_fields !!}
                                </div>
                            </div>


                            <div class="row p-2"></div>
                            <div class="row p-2"></div>
                            <div class="row p-2"></div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label-notes'>{{ trans('langNoteBody') }}</label>
                                <div class='col-sm-12'>
                                    {!! $rich_text_editor !!}
                                </div>
                            </div>


                            <div class="row p-2"></div>


                            <div class='form-group'>

                                    <!-- <div class="col-md-4">
                                        <input class='add_btn btn btn-success' type='submit' name='submitNote' value="{{ trans('langAdd') }}">
                                    </div>  -->

                                    <div class="col-xxl-10 col-lx-10 col-lg-10 col-md-10 col-sm-6" style="margin-left:12px;">
                                        <div class="row">
                                            <input class='add_btn btn btn-success' type='submit' name='submitNote' value="{{ trans('langAdd') }}">
                                            <a class='cancel_btn btn btn-secondary' style="margin-left:5px" href='$_SERVER[SCRIPT_NAME]'>{{ trans('langCancel') }}</a>
                                        </div>
                                     </div>
                            </div>


                            @if(!empty($contentToModify))
                                <?php $note = Database::get()->querySingle("SELECT * FROM `note` WHERE `note`.`content`='{$contentToModify}' ");?>
                                <input type="hidden" name="id" value="{{$note->id}}"/>
                            @endif


                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>




@endsection
