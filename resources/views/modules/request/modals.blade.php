<div class='modal fade' id='assigneesModal' tabindex='-1' role='dialog' aria-labelledby='assigneesModalLabel' aria-hidden='true'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='{{ trans('langCancel') }}'>
                    <span aria-hidden='true'>&times;</span>
                </button>
                <div class='modal-title h4' id='assigneesModalLabel'>{{ trans("m['WorkAssignTo']") }}...</div>
            </div>
            <form method='post' action='{{ $targetUrl }}'>
                {!! generate_csrf_token_form_field() !!}
                <div class='modal-body'>
                    <select class='form-control' name='assignTo[]' multiple id='assignTo'>
                        @foreach ($course_users as $cu)
                            <option value='{{ $cu->user_id }}'
                            @if (in_array($cu->user_id, $assigned))
                                selected
                            @endif>{{$cu->name}} ({{$cu->email}})</option>
                        @endforeach
                    </select>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-default' class='close' data-dismiss='modal'>{{ trans('langCancel') }}</button>
                    <button class='btn btn-primary' type='submit' name='assignmentSubmit'>{{ trans('langSubmit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class='modal fade' id='watchersModal' tabindex='-1' role='dialog' aria-labelledby='watchersModalLabel' aria-hidden='true'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='{{ trans('langCancel') }}'>
                    <span aria-hidden='true'>&times;</span>
                </button>
                <div class='modal-title h4' id='watchersModalLabel'>{{ trans("langWatchers") }}...</div>
            </div>
            <form method='post' action='{{ $targetUrl }}'>
                {!! generate_csrf_token_form_field() !!}
                <div class='modal-body'>
                    <select class='form-control' name='watchers[]' multiple id='watchersInput'>
                        @foreach ($course_users as $cu)
                            @unless (in_array($cu->user_id, $assigned))
                                <option value='{{ $cu->user_id }}'
                                @if (in_array($cu->user_id, $watchers))
                                    selected
                                @endif>{{$cu->name}} ({{$cu->email}})</option>
                            @endunless
                        @endforeach
                    </select>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn btn-default' class='close' data-dismiss='modal'>{{ trans('langCancel') }}</button>
                    <button class='btn btn-primary' type='submit' name='watchersSubmit'>{{ trans('langSubmit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function() {
        $("#assignTo").select2({
            dropdownParent: $("#assigneesModal"),
            width: '100%'
        });
        $("#watchersInput").select2({
            dropdownParent: $("#watchersModal"),
            width: '100%'
        });
    });
</script>
