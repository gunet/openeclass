

$(document).ready( function () {

	/* Enable DataTable js for all .datatable tables */
    $('.datatable').dataTable({
      "aoColumnDefs": [
          { 'bSortable': false, 'aTargets': [ 2 ] }
       ],
       "aaSorting": [[ 1, "desc" ]]
    });


    // Sidebar switcher - custom CSS
    $('.toggle-sidebar').on('click',function(e){
      $('body').toggleClass('sidebar-opened');
      $('#header .sidebutton').toggleClass('displaynone');
    });

    // Lesson accordion - jQuery ui
    $( ".accordion" ).accordion({
      heightStyle: "content",
      active: false,
      collapsible: true,
      animate: 200
    });

    $( ".accordion-first-active" ).accordion({
      heightStyle: "content",
      collapsible: true,
      animate: 200
    });


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



});