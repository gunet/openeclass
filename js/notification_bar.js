$(document).ready(function(){
    const cookieBox = document.querySelector(".notification-top-bar");
    const buttons = document.querySelectorAll(".hide-notification-bar"); 

    const executeCodes = () => {

        //if(document.cookie.includes(cookieUser)) return;

        const cookieValue = getNewCookie("CookieNotification");

        if (cookieValue) {
            return;
        }
        
        buttons.forEach(button => {
            button.addEventListener("click", () => {
                cookieBox.classList.add("hide");
                $('.ContentEclass').removeClass('fixed-announcement');

                //if btn has accepted cookie
                if(button.id == "closeNotificationBar"){
                    //set cookie for 1 month.
                    setNewCookie("CookieNotification","true",30);
                    window.location.reload();
                }
            });
        });
    };

    window.addEventListener("load",executeCodes);
});

function setNewCookie(name, value, days) {
    var date = new Date(), expires = "";
    if (days) {
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        if (typeof(date.toUTCString)==="function") {
            expires = "; expires=" + date.toUTCString();
        } else {
            //deprecated
            expires = "; expires=" + date.toGMTString();
        }
    } else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/; samesite=strict";
}

function getNewCookie(name) {
    const nameEquals = name + '=';
    const cookieArray = document.cookie.split(';');
  
    for (cookie of cookieArray) {
      while (cookie.charAt(0) == ' ') {
        cookie = cookie.slice(1, cookie.length);
      }
  
      if (cookie.indexOf(nameEquals) == 0)
        return decodeURIComponent(
          cookie.slice(nameEquals.length, cookie.length),
        );
    }
  
    return null;
}