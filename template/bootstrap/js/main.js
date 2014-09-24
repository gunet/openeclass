

$(document).ready( function () {

  menuheight();


    // Sidebar switcher - custom CSS
    $('.toggle-sidebar').on('click',function(e){
      $('body').toggleClass('sidebar-opened');
      $('#header .sidebutton').toggleClass('displaynone');
    });

    // Lesson accordion - jQuery ui
//    $( ".accordion" ).accordion({
//      heightStyle: "content",
//      active: false,
//      collapsible: true,
//      animate: 200
//    });
//
//    $( ".accordion-first-active" ).accordion({
//      heightStyle: "fill",
//      autoHeight: false,
//      collapsible: true,
//      animate: 200
//    });

    /* Dropdown menu toggle on click */
    $(document).click(function() {
        $('div.dropdown').removeClass('opened');
    });


    $('div.dropdown:not(.open-on-hover)').on('click',function(event){
        $('div.dropdown:not(.open-on-hover)').not(this).removeClass('opened');
        $(this).toggleClass('opened');
        event.stopPropagation();
    });

    
    var loading = function(e) {
      e.preventDefault();
      e.stopPropagation();
      e.target.classList.add('loading');
      e.target.setAttribute('disabled','disabled');
      setTimeout(function(){
        e.target.classList.remove('loading');
        e.target.removeAttribute('disabled');
      },1500);
    };

    var btns = document.querySelectorAll('button');
    for (var i=btns.length-1;i>=0;i--) {
      btns[i].addEventListener('click',loading);
    }


    // Menu height 
    function menuheight(){

      var nav_item_height = $('#leftnav .navlist > li:first-child .title').outerHeight();
      var nav_length = $('#leftnav .navlist > li').length;
      var logo_height = $('.logo').outerHeight();
      var window_height = $(window).height();

      var nav_available_height = window_height - logo_height - (nav_item_height*nav_length);
      console.log(nav_available_height);
    
      $('#leftnav .navlist > li > ul').css('height','150px !important;');
    }

    //window resize
//    $(window).resize(function() {
//        $(".accordion-first-active, .accordion").accordion("refresh"); //refresh the accordions
//    });
$(".opt-btn-wrapper").hover(function(){$(this).children(".opt-btn-more-wrapper").animate({width:"300px"},150);},function(){$(this).children(".opt-btn-more-wrapper").animate({width:"50px"},150);});
}); 