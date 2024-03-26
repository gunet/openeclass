$(document).ready(function(){
     
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    // set Min-Height of left menu in order to has the same witdh of colMainContent //
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////

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

    onLoad();

});



function onLoad() {
    var sidebar = document.querySelector(".col_sidebar_active");
    var container = document.querySelector(".col_maincontent_active");
    if(window.localStorage.getItem("active-nav") && window.localStorage.getItem("active-nav") == "true"){
        if(sidebar != null){
            sidebar.classList.add("active-nav");
        }
        if(container != null){
            container.classList.add("active-cont");
        }
    }
}
  
function ToogleButton() {
    var sidebar = document.querySelector(".col_sidebar_active");
    var container = document.querySelector(".col_maincontent_active");

    sidebar.classList.toggle("active-nav")
    container.classList.toggle("active-cont");
    
    if(sidebar.classList.contains("active-nav")){
        window.localStorage.setItem("active-nav", "true");
    }else{
        window.localStorage.removeItem("active-nav");
    }
}

