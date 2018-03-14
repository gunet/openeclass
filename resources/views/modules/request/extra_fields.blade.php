<fieldset class='extra-fields-set' id='fields_{{ $type_id }}'>
    <legend>{{ $type_name }}</legend>
    @foreach ($fields_info as $field_id => $field_description)
        <div class='form-group'>
            <label for='field_{{ $type_id }}_{{ $field_id }}' class='col-sm-2 control-label'>{{ $field_description->name }}:</label>
            <div class='col-sm-10'>
                @if ($field_description->datatype == REQUEST_FIELD_TEXTBOX)
                    <input type='text' class='form-control'
                        id='field_{{ $type_id }}_{{ $field_id }}'
                        name='field_{{ $type_id }}_{{ $field_id }}'
                        @if (isset($field_description->data))
                            value='{{ $field_description->data }}'
                        @endif
                    >
                @elseif ($field_description->datatype == REQUEST_FIELD_TEXTAREA)
                    <textarea class='form-control'
                        name='field_{{ $type_id }}_{{ $field_id }}'
                        id='field_{{ $type_id }}_{{ $field_id }}'>@if (isset($field_description->data)){{ $field_description->data }}@endif</textarea>
                @elseif ($field_description->datatype == REQUEST_FIELD_DATE)
                    <input class='form-control date-picker'
                    valuename='field_{{ $type_id }}_{{ $field_id }}2
                    id='field_{{ $type_id }}_{{ $field_id }}' type='text'
                        @if (isset($field_description->data))
                            value='{{ $field_description->data }}'
                        @endif
                    >
                @elseif ($field_description->datatype == REQUEST_FIELD_MENU_EDITABLE)
                    <select class='form-control combobox' name='field_{{ $type_id }}_{{ $field_id }}' id='field_{{ $type_id }}_{{ $field_id }}'>
                        @if (!$field_description->data)
                            <option></option>
                        @endif
                        @foreach ($field_description->values as $option)
                            <option value='{{ $option }}'
                                @if ($option == $field_description->data)
                                    selected
                                @endif
                            >{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif ($field_description->datatype == REQUEST_FIELD_MENU)
                    <select class='form-control' name='field_{{ $type_id }}_{{ $field_id }}' id='field_{{ $type_id }}_{{ $field_id }}'>
                        @foreach ($field_description->values as $option)
                            <option value='{{ $option }}'
                                @if ($option == $field_description->data)
                                    selected
                                @endif
                            >{{ $option }}</option>
                        @endforeach
                    </select>
                @endif
                @if ($field_description->description)
                    <span class='help-block'>{{ $field_description->description }}</span>
                @endif
            </div>
        </div>
    @endforeach
</fieldset>

<script>
    $('.date-picker').datepicker({
        format: 'dd-mm-yyyy',
        pickerPosition: 'bottom-right',
        language: '{{ $language }}',
        autoclose: true
    });
    $('.combobox').combobox();
</script>
