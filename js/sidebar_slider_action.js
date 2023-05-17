$(document).ready(function(){
     
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /////// set Min-Height of sidebar tools course to be same with colMainContent ////
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
    

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////// toggle_button for push sidebar in left side /////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////

    var menu_btn = document.querySelector("#menu-btn");
    var sidebar = document.querySelector(".col_sidebar_active");
    var container = document.querySelector(".col_maincontent_active");
   
    var clickerMenuBtn = 0;
    if(localStorage.getItem("MenuBtnStorage")){
        clickerMenuBtn = localStorage.getItem("MenuBtnStorage");
    }
    
    if(menu_btn!=null){
        menu_btn.addEventListener("click",()=>{
            clickerMenuBtn++;
            localStorage.setItem("MenuBtnStorage",clickerMenuBtn);
            sidebar.classList.toggle("active-nav");
            container.classList.toggle("active-cont");
        });
    }else{
        localStorage.setItem("MenuBtnStorage",0);
    }

    if(localStorage.getItem("MenuBtnStorage") %2 > 0){
        sidebar.classList.toggle("active-nav");
        container.classList.toggle("active-cont");
    }else{
        if(sidebar!=null && container!=null){
            sidebar.classList.remove("active-nav");
            container.classList.remove("active-cont");
        }
        localStorage.removeItem("MenuBtnStorage");
    }
});

