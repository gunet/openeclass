
<div class='col-12 mt-3'>
    @if (isset($clock_type) && $clock_type == 0 || !isset($clock_type))
        <ul class="clock clock-sidebar p-0 m-auto">	
                <li class="sec"></li>
                <li class="hour"></li>
                <li class="min"></li>
        </ul>            
    @else
        <div class="digital_clock p-0">
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
    @endif
</div>
