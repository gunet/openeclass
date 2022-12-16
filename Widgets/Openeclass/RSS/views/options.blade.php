        <form id="optionsForm{{ $widget_widget_area_id }}">
              <label class='d-inline-flex w-100'>
                  <span class='w-25 control-label-notes pt-2 me-2'>RSS feed url:</span> 
                  <input class='form-control' type='text' name='feed_url' value='{{ isset($feed_url) ? $feed_url : '' }}'>
              </label><br /><br />
              <label class='d-inline-flex w-100'>
                    <span class='w-25 control-label-notes pt-2 me-2'>Πλήθος νέων:</span>   
                    <select class='form-select' name='feed_items'>
                        <option value="3" {{ isset($feed_items) && $feed_items == 3 ? ' selected' : ''}} '>3</option>
                        <option value="5" {{ isset($feed_items) && $feed_items == 5 ? ' selected' : ''}} '>5</option>
                        <option value="10" {{ isset($feed_items) && $feed_items == 10 ? ' selected' : ''}} '> 10</option>
                    </select> 
              </label>         
        </form>

