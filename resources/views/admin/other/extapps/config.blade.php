@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='row extapp'>
        <div class='col-xs-12'>
            <div class='form-wrapper'>
                <form class='form-horizontal' role='form' action='extapp.php?edit={{ $appName }}' method='post'>
                <fieldset>
                <?php $boolean_fields = [];?>
                @foreach ($app->getParams() as $param)
                    @if ($param->getType() == ExtParam::TYPE_BOOLEAN)
                        <?php $boolean_fields[] = $param; ?>
                    @elseif ($param->getType() == ExtParam::TYPE_MULTILINE)
                        <div class='form-group'>
                            <label for='{{ $param->name() }}' class='col-sm-2 control-label'>{{ $param->display() }}</label>
                            <div class='col-sm-10'>
                                <textarea class='form-control' rows='3' cols='40' name='{{ $param->name() }}'>
                                    {{ $param->value() }}
                                </textarea>
                            </div>
                        </div>
                    @else
                        <div class='form-group'>
                            <label for='{{ $param->name() }}' class='col-sm-2 control-label'>{{ $param->display() }}</label>
                            <div class='col-sm-10'>
                                <input class='form-control' type='text' name='{{ $param->name() }}' value='{{ $param->value() }}'>
                            </div>
                        </div>
                    @endif
                @endforeach
                @foreach ($boolean_fields as $param)
                        <div class='form-group'>
                            <div class='col-sm-offset-2 col-sm-10'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='{{ $param->name() }}'{!! $param->value() == 1 ? " value='0' checked" : " value='1'" !!}> 
                                        {{ $param->display() }}
                                    </label>
                                </div>
                            </div>
                        </div>
                @endforeach
                    <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>
                            <button class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                {{ trans('langModify') }}
                            </button> 
                            <button class='btn btn-danger' type='submit' name='submit' value='clear'>
                                {{ trans('langClearSettings') }}
                            </button>
                        </div>
                    </div>
                </fieldset>
                {!! generate_csrf_token_form_field() !!}
                </form>
            </div>
        </div>
    </div>
@endsection