<div class='modal fade' id='assigneesModal' tabindex='-1' role='dialog' aria-labelledby='assigneesModalLabel' aria-hidden='true'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='assigneesModalLabel'>{{ trans('langWorkAssignTo') }}...</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='{{ trans('langCancel') }}'></button>

            </div>
            <form method='post' action='{{ $targetUrl }}'>
                {!! generate_csrf_token_form_field() !!}
                <div class='modal-body'>
                    <select class='form-select' name='assignTo[]' multiple id='assignTo'>
                        @foreach ($course_users as $cu)
                            <option value='{{ $cu->user_id }}'
                            @if (in_array($cu->user_id, $assigned))
                                selected
                            @endif>{{$cu->name}} ({{$cu->email}})</option>
                        @endforeach
                    </select>
                </div>
                <div class='modal-footer'>
                    <button type='button' class='btn cancelAdminBtn' class='close' data-bs-dismiss='modal'>{{ trans('langCancel') }}</button>
                    <button class='btn submitAdminBtn ms-1' type='submit' name='assignmentSubmit'>{{ trans('langSubmit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class='modal fade' id='watchersModal' tabindex='-1' role='dialog' aria-labelledby='watchersModalLabel' aria-hidden='true'>
    <div class='modal-dialog' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='watchersModalLabel'>{{ trans("langWatchers") }}...</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='{{ trans('langCancel') }}'></button>

            </div>
            <form method='post' action='{{ $targetUrl }}'>
                {!! generate_csrf_token_form_field() !!}
                <div class='modal-body'>
                    <select class='form-select' name='watchers[]' multiple id='watchersInput'>
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
                    <button type='button' class='btn cancelAdminBtn' class='close' data-bs-dismiss='modal'>{{ trans('langCancel') }}</button>
                    <button class='btn submitAdminBtn ms-1' type='submit' name='watchersSubmit'>{{ trans('langSubmit') }}</button>
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
