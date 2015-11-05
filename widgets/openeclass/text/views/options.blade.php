                            <form id="optionsForm{{ $widget_widget_area_id }}">
                                <ul class="nav nav-tabs" role="tablist">
                                    @foreach ($active_ui_languages as $key => $active_ui_language)
                                      <li role="presentation" class="{{ $key == 0 ? 'active' : '' }}">
                                          <a href="#{{ $active_ui_language . $widget_widget_area_id }}" id="home-tab" role="tab" data-toggle="tab" aria-controls="{{ $active_ui_language . $widget_widget_area_id}}" aria-expanded="{{ $key == 0 ? 'true' : 'false' }}">{{ $native_language_names_init[$active_ui_language] }}</a>
                                      </li>
                                    @endforeach
                                </ul>
                                 <div class="tab-content">
                                    @foreach ($active_ui_languages as $key => $active_ui_language)
                                        <div role="tabpanel" class="tab-pane fade{{ $key == 0 ? ' active in' : '' }}" id="{{ $active_ui_language . $widget_widget_area_id }}" aria-labelledby="{{ $active_ui_language . $widget_widget_area_id}}-tab">
                                            <textarea class='form-control' name="text_{{ $active_ui_language }}">{{ isset(${'text_'.$active_ui_language}) ? ${'text_'.$active_ui_language} : '' }}</textarea>
                                        </div>
                                    @endforeach
                                </div>                           
                            </form>