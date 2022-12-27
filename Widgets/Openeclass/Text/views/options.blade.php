<form id="optionsForm{{ $widget_widget_area_id }}">
    <ul class="nav nav-pills" role="tablist">
        @foreach ($active_ui_languages as $key => $active_ui_language)
          <li class="nav-item" role="presentation">
              <button class="nav-link nav-link-widgets {{ $key == 0 ? 'active' : '' }}" id="{{ $active_ui_language . $widget_widget_area_id}}-tab" data-bs-toggle="tab" data-bs-target="#{{ $active_ui_language . $widget_widget_area_id }}-tab-pane" type="button" role="tab" aria-controls="{{ $active_ui_language . $widget_widget_area_id}}-tab-pane" aria-selected="true">{{ $native_language_names_init[$active_ui_language] }}</button>
          </li>
        @endforeach
    </ul>
     <div class="tab-content mt-2">
        @foreach ($active_ui_languages as $key => $active_ui_language)
            <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="{{ $active_ui_language . $widget_widget_area_id }}-tab-pane" role="tabpanel"  aria-labelledby="{{ $active_ui_language . $widget_widget_area_id}}-tab" tabindex="0">
                <textarea class='form-control Borders' rows='3' cols='40' name="text_{{ $active_ui_language }}">{{ isset(${'text_'.$active_ui_language}) ? ${'text_'.$active_ui_language} : '' }}</textarea>
            </div>
        @endforeach
    </div>
</form>
