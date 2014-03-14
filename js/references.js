/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== 
 */
$(document).ready(function (){
    $('#refobjgentype').change(function(){
        if($('#refobjgentype').val() == 0) {
           $('#refcoursecont').hide();
           $('#refcourse').val("0");
           $('#refobjtypecont').hide();
           $('#refobjtype').val("0");
           $('#refobjidcont').hide();
           $('#refobjid').val("0");
           
        }
        else if($('#refobjgentype').val() == -1){
           $('#refobjtypecont').hide();
           $('#refobjtype').val("0");
           $('#refobjidcont').hide();
           $('#refobjid').val("0");
           show_ref_courses();
        }
        else{
           $('#refcoursecont').hide();
           $('#refcourse').val("0");
           $('#refobjtypecont').hide();
           $('#refobjtype').val("0");
           show_ref_genobjects($('#refobjgentype').val()); 
        }
    });
    $('#refcourse').change(function(){
        if($('#refcourse').val() != '0'){
            $('#refobjidcont').hide();
            $('#refobjid').val("0");
            show_ref_obj_types($('#refcourse').val());    
        }
        else{
            $('#refobjtypecont').hide();
            $('#refobjtype').val("0");
            $('#refobjidcont').hide();
            $('#refobjid').val("0");
        }
    });
    $('#refobjtype').change(function(){
        if($('#refobjtype').val()!= '0'){
            show_ref_courseobjects($('#refcourse').val(), $('#refobjtype').val());
        }
        else{
           $('#refobjidcont').hide();
           $('#refobjid').val("0");
        }
    });
    
})

function show_ref_courses(){
    $('#refcoursecont').show();
}

function show_ref_obj_types(course){
    $.getJSON('../references_data.php',{cid:course},function(data){
        $('#refobjtypecont').hide();
        $('#refobjtype').val("0");
        zerooption = $('option[value="0"]', $('#refcourse')).text();
        $('#refobjtype').empty();
        $('#refobjtype').append('<option value="0">'+zerooption+'</option>');
        $.each(data, function(k,v){
            $('#refobjtype').append('<option value="'+k+'">'+v+'</option>');
        });
        $('#refobjtypecont').show();
        
    });
}

function show_ref_genobjects(type){
    $.getJSON('../references_data.php',{tid:type},function(data){
        $('#refobjidcont').hide();
        $('#refobjid').val("0");
        zerooption = $('option[value="0"]', $('#refcourse')).text();
        $('#refobjid').empty();
        $('#refobjid').append('<option value="0">'+zerooption+'</option>');
        $.each(data, function(k,v){
            $('#refobjid').append('<option value="'+k+'">'+v+'</option>');
        });
        $('#refobjidcont').show();
    });    
}

function show_ref_courseobjects(course, type){
    $.getJSON('../references_data.php',{cid:course, tid:type},function(data){
        zerooption = $('option[value="0"]', $('#refcourse')).text();
        $('#refobjid').empty();
        $('#refobjid').append('<option value="0">'+zerooption+'</option>');
        $.each(data, function(k,v){
            $('#refobjid').append('<option value="'+k+'">'+v+'</option>');
        });
        $('#refobjidcont').show();
    });
}

