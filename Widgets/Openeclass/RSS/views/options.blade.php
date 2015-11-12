        <form id="optionsForm{{ $widget_widget_area_id }}">
              <label>
                  RSS feed url: <input type='text' size='50' name='feed_url' value='{{ isset($feed_url) ? $feed_url : '' }}'>
              </label><br />
              <label>
                Πλήθος νέων:    <select name='feed_items'>
                                    <option value="3" {{ isset($feed_items) && $feed_items == 3 ? ' selected' : ''}} '>3</option>
                                    <option value="5" {{ isset($feed_items) && $feed_items == 5 ? ' selected' : ''}} '>5</option>
                                    <option value="10" {{ isset($feed_items) && $feed_items == 10 ? ' selected' : ''}} '> 10</option>
                                </select> 
              </label>         
        </form>

