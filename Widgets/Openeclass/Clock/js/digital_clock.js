$(function(){       
        setInterval( function() {
                // Create a newDate() object and extract the seconds of the current time on the visitor's
                var seconds = new Date().getSeconds();
                // Add a leading zero to seconds value
                $(".digital_sec").html(( seconds < 10 ? "0" : "" ) + seconds);
                },1000);

        setInterval( function() {
                // Create a newDate() object and extract the minutes of the current time on the visitor's
                var minutes = new Date().getMinutes();
                // Add a leading zero to the minutes value
                $(".digital_min").html(( minutes < 10 ? "0" : "" ) + minutes);
            },1000);

        setInterval( function() {
                // Create a newDate() object and extract the hours of the current time on the visitor's
                var hours = new Date().getHours();
                // Add a leading zero to the hours value
                $(".digital_hour").html(( hours < 10 ? "0" : "" ) + hours);
            }, 1000);	        
});
