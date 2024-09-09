        <form id="optionsForm{{ $widget_widget_area_id }}">
            <fieldset>
            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
            <div class='radio'>
              <label>
                <input type='radio' name='clock_type' value='0'{{ isset($clock_type) && $clock_type == 0 || !isset($clock_type) ? ' checked' : '' }}>  Αναλογικό Ρολόι
              </label>
            </div>
            <div class='radio mt-2'>
              <label>
                <input type='radio' name='clock_type' value='1'{{ isset($clock_type) && $clock_type == 1 ? ' checked' : '' }}> Ψηφιακό Ρολόι
              </label>
            </div>     
            </fieldset>      
        </form>