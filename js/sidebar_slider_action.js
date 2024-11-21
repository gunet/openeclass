$(document).ready(function(){

    // Actions needed to be done after full DOM elements downloaded
    $(window).on("resize", function () {
        if ($(".float-menu").css("position") === "relative") {
            $(".float-menu").removeAttr("style");
            $(".float-menu").removeClass("float-menu-in");
        }
    });

    // Leftnav - rotate Category Menu Item icon
    if ($(".collapse.show").length > 0) { //when page first loads the show.bs.collapse event is not triggered
        $(".collapse.show").prev("a").find("span.fa").addClass("fa-rotate-180");
    }
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).prev("a").find("span.fa").addClass("fa-rotate-180");
    });
    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).prev("a").find("span.fa").removeClass("fa-rotate-180");
    });

    if ($("#leftnav").hasClass("float-menu-in")) {
        $("#leftnav").animate({
            "left": "-225"
        }, {duration: 150, start: function () {
                $(this).removeClass("float-menu-in");
            }});
    }

    var container = document.querySelector(".col_maincontent_active");
    if(getNewCookieSlider("CookieSlideSidebar") != null){
        if(container != null){
            container.classList.add("active-cont");
        }
    }else{
        if(container != null){
            var active_container = document.querySelector(".active-cont");
            if(active_container != null){
                container.classList.remove("active-cont");
            }
        }
    }

});


function ToggleButton() {
    var sidebar = document.querySelector(".col_sidebar_active");
    var container = document.querySelector(".col_maincontent_active");

    sidebar.classList.toggle("active-nav")
    container.classList.toggle("active-cont");
    
    if(sidebar.classList.contains("active-nav")){
        setNewCookieSlider("CookieSlideSidebar","true",30);
    }else{
        setNewCookieSlider("CookieSlideSidebar","true",0);
    }
}


function setNewCookieSlider(name, value, days) {
    var date = new Date(), expires = "";
    if (days > 0) {
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        if (typeof(date.toUTCString)==="function") {
            expires = "; expires=" + date.toUTCString();
        } else {
            //deprecated
            expires = "; expires=" + date.toGMTString();
        }
    } else {
        // remove cookie
        let yesterday = new Date(date);
        yesterday.setDate(yesterday.getDate() - 1);
        expires = "; expires=" + yesterday.toUTCString();
    }
    document.cookie = name + "=" + value + expires + "; path=/; samesite=strict";
}


function getNewCookieSlider(name) {
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
