@if (isset($clock_type) && $clock_type == 0 || !isset($clock_type))
    <div class="panel">
        <div class="panel-body">
            <ul class="clock">	
                    <li class="sec"></li>
                    <li class="hour"></li>
                    <li class="min"></li>
            </ul>            
        </div>
    </div>
@else
    <div class="panel">
        <div class="panel-body light digital_clock">
            <div class="display">
                <ul>
                    <li class="digital_hour"></li>
                    <li class="point">:</li>
                    <li class="digital_min"></li>
                    <li class="point">:</li>
                    <li class="digital_sec"></li>
                </ul>                
            </div>
        </div>
    </div>
@endif