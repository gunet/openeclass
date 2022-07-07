$(document).ready(function(){
     
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    /////// set Min-Height of sidebar tools course to be same with colMainContent ////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////

        var elem = document.querySelector(".col_maincontent_active");
        if(elem) {
            var rect = elem.getBoundingClientRect();
            console.log(rect);
            $('.navbar_sidebar').css('min-height', rect.height+70);
            
        }

        $('#btn-syllabus').on('click',function(){
            var elem = document.querySelector(".col_maincontent_active");
            if(elem) {
                var rect = elem.getBoundingClientRect();
                $('.navbar_sidebar').css('min-height', rect.height);
                
            }
        })

        $('.collapsed').on('click',function(){
            var elem = document.querySelector(".col_maincontent_active");
            if(elem) {
                var rect = elem.getBoundingClientRect();
                $('.navbar_sidebar').css('min-height', rect.height);
                
            }
        })

        $('.metadataCourseCollapseBtn').on('click',function(){
            var elem = document.querySelector(".col_maincontent_active");
            if(elem) {
                var rect = elem.getBoundingClientRect();
                $('.navbar_sidebar').css('min-height', rect.height);
                
            }
        })

        //From FAQ 
        $('.expand').on('click',function(){

            // if( $(".col_maincontent_active").height() > $(".navbar_sidebar").height() ){
            //     $(".navbar_sidebar").css("height", $(".col_maincontent_active").css("height") );
            // }

            // var elem = document.querySelector(".col_maincontent_active");
            // if(elem) {
            //     var rect = elem.getBoundingClientRect();
            //     $('.navbar_sidebar').css('min-height', rect.height);
                
            // }
            // console.log(rect);
        })



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
        localStorage.removeItem("MenuBtnStorage");
    }

    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////// dropdown tools in sidebar for course /////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
    
    
    var clicker_count2 = 0;
    if(clicker_count2 == 0){
        $("#Dropdown2").collapse("show");
        $(".classTwo").css("transform","rotate(0deg)");
    }
    $('.active_tools_dropdown2').on('click',function()
	{
        clicker_count2++;
        if($("#Dropdown2").hasClass('show')){
            $("#Dropdown2").collapse("hide");
            $(".classTwo").css("transform","rotate(180deg)");
        }else{
            $("#Dropdown2").collapse("show");
            $(".classTwo").css("transform","rotate(0deg)");
        }

        if($("#Dropdown3").hasClass('show')){
            $("#Dropdown3").collapse("hide");
            $(".classThree").css("transform","rotate(180deg)");
        }
        localStorage.setItem('showActiveCourseTools',1);
    })


    var clicker_count3 = 0;
    if(clicker_count3 == 0){
       $("#Dropdown3").collapse("hide");
       $(".classThree").css("transform","rotate(180deg)");
    }
    $('.active_tools_dropdown3').on('click',function()
	{
        clicker_count3++;
        if($("#Dropdown3").hasClass('show')){
            $("#Dropdown3").collapse("hide");
            $(".classThree").css("transform","rotate(180deg)");
        }else{
            $("#Dropdown3").collapse("show");
            $(".classThree").css("transform","rotate(0deg)"); 
        }

        if($("#Dropdown2").hasClass('show')){
            $("#Dropdown2").collapse("hide");
            $(".classTwo").css("transform","rotate(180deg)");
        }   
        localStorage.setItem('showActiveCourseTools',0);
    })

    if(localStorage.getItem('showActiveCourseTools') != null){
        if(localStorage.getItem('showActiveCourseTools') == 0){
            $("#Dropdown2").removeClass("show");
            $("#Dropdown2").addClass("collapse hide");

            $("#Dropdown3").removeClass("hide");
            $("#Dropdown3").addClass("collapse show");
        }else{
            $("#Dropdown2").removeClass("hide");
            $("#Dropdown2").addClass("collapse show");
            
            $("#Dropdown3").removeClass("show");
            $("#Dropdown3").addClass("collapse hide");
        }
    }else{
        localStorage.removeItem('showActiveCourseTools');
    }
    


     //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
     //////////////////////////////////////////////////////////////////////////////////
    /////////////////////// dropdown tools in sidebar for admin //////////////////////
     //////////////////////////////////////////////////////////////////////////////////
     //////////////////////////////////////////////////////////////////////////////////

    $('.active_tools_dropdownAdmin1').on('click',function()
	{
        if(!$("#DropdownAdmin1").hasClass('show')){
            $("#DropdownAdmin1").addClass("show");
            $(".classAdminOne").css("transform","rotate(90deg)");
        }else{
            $("#DropdownAdmin1").removeClass("show");
            $("#DropdownAdmin1").addClass("hide");
            $(".classAdminOne").css("transform","rotate(0deg)");
        }


        if($("#DropdownAdmin2").hasClass('show')){
            $("#DropdownAdmin2").removeClass("show");
            $(".classAdminTwo").css("transform","rotate(0deg)");
        } 
        if($("#DropdownAdmin3").hasClass('show')){
            $("#DropdownAdmin3").removeClass("show");
            $(".classAdminThree").css("transform","rotate(0deg)");
        }   
        localStorage.setItem('showAdminTools',1);
        console.log('διαχειριση πλατφορμας');
    })


    $('.active_tools_dropdownAdmin2').on('click',function()
	{

        if(!$("#DropdownAdmin2").hasClass('show')){
            $("#DropdownAdmin2").addClass("show");
            $(".classAdminTwo").css("transform","rotate(90deg)");
        }else{
            $("#DropdownAdmin2").removeClass("show");
            $("#DropdownAdmin2").addClass("hide");
            $(".classAdminTwo").css("transform","rotate(0deg)");
        }


        if($("#DropdownAdmin1").hasClass('show')){
            $("#DropdownAdmin1").removeClass("show");
            $(".classAdminOne").css("transform","rotate(0deg)");
        } 
        if($("#DropdownAdmin3").hasClass('show')){
            $("#DropdownAdmin3").removeClass("show");
            $(".classAdminThree").css("transform","rotate(0deg)");
        }      
        localStorage.setItem('showAdminTools',2);
        console.log('διαχειριση χρηστων');
    })


    $('.active_tools_dropdownAdmin3').on('click',function()
	{

        if(!$("#DropdownAdmin3").hasClass('show')){
            $("#DropdownAdmin3").addClass("show");
            $(".classAdminThree").css("transform","rotate(90deg)");
        }else{
            $("#DropdownAdmin3").removeClass("show");
            $("#DropdownAdmin3").addClass("hide");
            $(".classAdminThree").css("transform","rotate(0deg)");
        }


        if($("#DropdownAdmin1").hasClass('show')){
            $("#DropdownAdmin1").removeClass("show");
            $(".classAdminOne").css("transform","rotate(0deg)");
        } 
        if($("#DropdownAdmin2").hasClass('show')){
            $("#DropdownAdmin2").removeClass("show");
            $(".classAdminTwo").css("transform","rotate(0deg)");
        }     
        localStorage.setItem('showAdminTools',3);
        console.log('διαχειριση μαθηματων');    
    })

    if(localStorage.getItem('showAdminTools') != null){
        if(localStorage.getItem('showAdminTools') == 1){
            $("#DropdownAdmin2").removeClass("show");
            $(".classAdminTwo").css("transform","rotate(0deg)");

            $("#DropdownAdmin3").removeClass("show");
            $(".classAdminThree").css("transform","rotate(0deg)");

            $("#DropdownAdmin1").addClass("show");
            $(".classAdminOne").css("transform","rotate(90deg)");
            
        }else if(localStorage.getItem('showAdminTools') == 2){
            $("#DropdownAdmin1").removeClass("show");
            $(".classAdminOne").css("transform","rotate(0deg)");

            $("#DropdownAdmin3").removeClass("show");
            $(".classAdminThree").css("transform","rotate(0deg)");

            $("#DropdownAdmin2").addClass("show");
            $(".classAdminTwo").css("transform","rotate(90deg)");

        }else{
            $("#DropdownAdmin1").removeClass("show");
            $(".classAdminOne").css("transform","rotate(0deg)");

            $("#DropdownAdmin2").removeClass("show");
            $(".classAdminTwo").css("transform","rotate(0deg)");

            $("#DropdownAdmin3").addClass("show");
            $(".classAdminThree").css("transform","rotate(90deg)");
        }
    }else{
        localStorage.removeItem('showActiveCourseTools');
    }

     //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
     //////////////////////////////////////////////////////////////////////////////////
    ////// Set backgroundColor to active buttons of sidebarCourse /////////
     //////////////////////////////////////////////////////////////////////////////////
     //////////////////////////////////////////////////////////////////////////////////

    $('.sidebarTexts').on('click',function() {
       var getId = $(this).attr('id');
       localStorage.setItem('getIdSideBarTexts',getId);
    });

    if(localStorage.getItem('getIdSideBarTexts') != null){
        $('#'+localStorage.getItem('getIdSideBarTexts')).css('background-color','#C0C0C0');
    }

     //////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////
     //////////////////////////////////////////////////////////////////////////////////
    ////// Set backgroundColor to active buttons of sidebarAdmin /////////
     //////////////////////////////////////////////////////////////////////////////////
     //////////////////////////////////////////////////////////////////////////////////

    
     $('.sidebarTextsAdmin').on('click',function() {
        var getIdAdmin = $(this).attr('id');
        localStorage.setItem('getIdSideBarTextsAdmin',getIdAdmin);
     });
     
     if(localStorage.getItem('getIdSideBarTextsAdmin') != null){
         $('#'+localStorage.getItem('getIdSideBarTextsAdmin')).css('background-color','#C0C0C0');
     }

     $("#AdminToolBtn").on("click",function(){
        localStorage.removeItem("getIdSideBarTextsAdmin");
     })
});

