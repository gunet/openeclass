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

var startdate = null;
var sdate = null;
var interval = 30;
var enddate = null;
var edate = null;
var course = null;
var module = null;
var user = null;
var department = 1;
var stats = 'u';
var plotsgenerated = false;
//var views = {plots:{class: 'fa fa-bar-chart', title: '$langPlots'}, list:{class: 'fa fa-list', title: '$langDetails'}};
var selectedview = 'plots';
//var maxintervals = 20;
//var lang = $language;
var xAxisDateFormat = {1:'%d-%m-%Y', 7:'%d-%m-%Y', 30:'%m-%Y', 365:'%Y'};
var xAxisLabels = {1:langDay, 7:langWeek, 30:langMonth, 365:langYear};
var xMinVal = null;
var xMaxVal = null;
var xTicks = null;
var department_details = new Array();
var tableOptions = {
    'a': {
        1:{'pageLength': 5, sumCols:[3,4,5,6], colDefs:[], durCol:null}, 
        2:{'pageLength': 5, sumCols:[], colDefs:[{"visible": false, "targets": 4}, {"visible": false, "targets": 5}, {'targets':1, 'className':'mynowrap', 'render': function ( data, type, row ) {return userEmailLink(data, row[5], row[4]);} }], durCol:null}
    }, 
    'u':{
        1:{'pageLength': 5, sumCols:[3,4], durCol:4, colDefs:[{'targets':4, 'render': function ( data, type, full, meta ) {return type === 'display' ? userFriendlyDuration(data): data;} }]}
    }, 
    'c':{
        1:{'pageLength': 5, sumCols:[3,4], durCol:4, colDefs:[{'targets':4, 'render': function ( data, type, full, meta ) {return type === 'display' ? userFriendlyDuration(data): data;}}, {'targets':2, 'className':'mynowrap', 'render': function ( data, type, row ) {return userEmailLink(data, row[6], row[5]);} }, {"visible": false, "targets": 5}, {"visible": false, "targets": 6}]},
        2:{'pageLength': 5, sumCols:[], durCol:null, colDefs:[{'targets':1, 'className':'mynowrap', 'render': function ( data, type, row ) {return userEmailLink(data, row[4], row[3]);} }, {"visible": false, "targets": 3}, {"visible": false, "targets": 4}]},
        3:{'pageLength': 50, sumCols:[], durCol:null, colDefs:[{'targets':1, 'className':'mynowrap', 'render': function ( data, type, row ) {return userEmailLink(data, row[7], row[6]);} }, {'targets':3, 'className':'action', 'render': function ( data, type, row ) {return actionWithDetails(data, row[4]);} }, {"visible": false, "targets": 6}, {"visible": false, "targets": 7}, {"visible": false, "targets": 4}]}
    }
};

    //3:{'pageLength': 50, sumCols:[], durCol:null, colDefs:[{'targets':1, 'className':'mynowrap', 'render': function ( data, type, row ) {return userEmailLink(data, row[7], row[6]);} }, {'targets':3, 'className':'action', 'render': function ( data, type, row ) {return actionWithDetails(data, row, 4);} }, {"visible": false, "targets": 6}, {"visible": false, "targets": 7}, {"visible": false, "targets": 4}]}
    
charts = new Object();
piecourse = -1;
piemodule = -1;
logs_refresh_required = true;

$(document).ready(function(){
    $("#toggle-view").children("i").attr('class', views['list'].class);
    $("#toggle-view").children("i").attr('data-original-title', views['list'].title);   
    
    $('#startdate').datepicker({
        format: 'dd-mm-yyyy',
        pickerPosition: 'bottom-left',
        language: lang,
        autoclose: true    
    }); 
    $('#enddate').datepicker({
        format: 'dd-mm-yyyy',
        pickerPosition: 'bottom-left',
        language: lang,
        autoclose: true
    });
    $('#startdate').change(function(){
        sdate = $(this).datepicker("getDate");
        startdate = sdate.getFullYear()+"-"+(sdate.getMonth()+1)+"-"+sdate.getDate();
        $('#enddate').focus();
    });
    $('#enddate').change(function(){
        edate = $(this).datepicker("getDate");
        enddate = edate.getFullYear()+"-"+(edate.getMonth()+1)+"-"+edate.getDate();
        adjust_interval_options();
        refresh_plots();
    });
    $('#enddate').blur(function(){
        setTimeout(function(){
            adjust_interval_options();
            refresh_plots();}, 1);
    });
   $('#plots-view').click(function(){
        if(selectedview != 'plots'){
            $('#list-view').removeClass("active");            
            $('#logs-view').removeClass("active");
            $(this).addClass("active");
            selectedview = 'plots';
            $('#module').parent().hide();
            $('#interval').parent().show();
            $('#interval').prop('disabled', false);
            $('.plotscontainer').show();
            for(var c in charts){
                charts[c].resize();
            }
            $('.detailscontainer').hide();            
            $('.logscontainer').hide();
        }
    });
    $('#list-view').click(function(){
        if(selectedview != 'list'){
            $('#plots-view').removeClass("active");            
            $('#logs-view').removeClass("active");
            $(this).addClass("active");
            selectedview = 'list';
            $('#module').parent().hide();
            $('#interval').parent().show();
            $('#interval').prop('disabled', true);
            $('.detailscontainer').show();            
            $('.logscontainer').hide();
            $('.plotscontainer').hide();
        }
    });
    $('#logs-view').click(function(){
        if(selectedview != 'logs'){
            refresh_users_activity_table();
            $('#plots-view').removeClass("active");
            $('#list-view').removeClass("active");
            $(this).addClass("active");
            selectedview = 'logs';
            $('#interval').prop('disabled', true);
            $('#interval').parent().hide();
            $('#module').parent().show();
            $('.logscontainer').show();
            $('.detailscontainer').hide();
            $('.plotscontainer').hide();
        }
    });
    
    sdate = $('#startdate').datepicker("getDate");
    startdate = sdate.getFullYear()+"-"+(sdate.getMonth()+1)+"-"+sdate.getDate();
    edate = $('#enddate').datepicker("getDate");
    enddate = edate.getFullYear()+"-"+(edate.getMonth()+1)+"-"+edate.getDate();
    adjust_interval_options();
    if($('#interval').length){
        interval = $('#interval option:selected').val();
        $('#interval').change(function(){
            interval = $('#interval option:selected').val();
            refresh_plots();
        });
    }
    if($('#user').length){
        user = $('#user option:selected').val();
        $('#user').change(function(){
            user = $('#user option:selected').val();
            refresh_plots();
        });
    
    }
    if($('#course').length){
        course = $('#course option:selected').val();
        $('#course').change(function(){
            course = $('#course option:selected').val();
            if(course == 0){
                module = null;
                piemodule = null;
                piecourse = null;
                $('#coursepref_pie_title').text(langFavouriteCourse);
            }
            else{
                $('#coursepref_pie_title').text(langFavouriteModule);
            
            }
            refresh_plots();
        });
    }
    if($('#department').length){
        department = $('#department option:selected').val();
        $('#department').change(function(){
            department = $('#department option:selected').val();
            refresh_plots();
        });
    }
    if($('#module').length){
        module = $('#module option:selected').val();
        $('#module').change(function(){
            module = $('#module option:selected').val();
            refresh_plots();
        });
    }
    detailsTables = new Object();
    tableTools = new Object();
    
    /*******************/
    
    for(tableid in tableOptions[stats]){
        tableElId = stats+'details'+tableid;
        colDefs = tableOptions[stats][tableid].colDefs;
        pLength = tableOptions[stats][tableid].pageLength;
        detailsTables[tableElId] = $('#'+tableElId).DataTable({
           'sPaginationType': 'full_numbers',
           'pageLength': pLength,
           'lengthMenu': [ 5, 10, 25, 50, 75, 100 ],
           'buttons': [{
                        extend:'print',
                        text: langPrint},
                    {
                        extend:'copyHtml5',
                        text: langCopy}, 
                    {
                        extend: 'collection',
                        text: langExport+'...',
                        buttons: ['csvHtml5','excelHtml5', 'pdfHtml5']
                    }
            ],
            columnDefs: colDefs,
            'autoWidth': true,                
            'footerCallback': footerCB(tableid, tableElId),
            'columnDefs': colDefs,
            'processing': true,
            'oLanguage': {
            'sLengthMenu':   langDisplay +' _MENU_ '+ langResults,
            'sZeroRecords':  langNoResult,
            'sInfo':         langDisplayed+' _START_ '+langTill+' _END_ '+langFrom+' _TOTAL_ '+langTotalResults,
            'sInfoEmpty':    langDisplayed+' 0 '+langTill+' 0 '+langFrom+' 0 '+langResults,
            'sInfoFiltered': '',
            'sInfoPostFix':  '',
            'sSearch':       langSearch+' ',
            'searchDelay' : 1000,
            'sUrl':          '',
            'oPaginate': {
                'sFirst':    '&laquo;',
                'sPrevious': '&lsaquo;',
                'sNext':     '&rsaquo;',
                'sLast':     '&raquo;'
            }
           }
        });
        detailsTables[tableElId].buttons().container().appendTo( '#'+tableElId+'_buttons');
    }
    
    $('.detailscontainer').hide();    
    $('.logscontainer').hide();
    refresh_plots();
    $('#cdetails3 tbody').on('mouseover', 'td.action', function(){ 
        var tr = $(this).closest('tr');
        var row =  detailsTables['cdetails3'].row( tr );
        $(this).css('cursor','pointer');
    });
    $('#cdetails3 tbody').on('click', 'td.action', function () {
        var tr = $(this).closest('tr');
        var row =  detailsTables['cdetails3'].row( tr );
        if ( row.child.isShown() ) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('span').removeClass('fa-caret-down');
            $(this).find('span').addClass('fa-caret-right');
        }
        else {
            row.child( cellHover(row.data()[4]), tr.attr('class') ).show();
            tr.addClass('shown');
            $(this).find('span').removeClass('fa-caret-right');
            $(this).find('span').addClass('fa-caret-down');
        }
    } );
    
    /*$('#cdetails3 tbody').on('mouseout', 'td.action', function () {
        var tr = $(this).closest('tr');
        var row =  detailsTables['cdetails3'].row( tr );
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
    } );*/
    
});//document ready   

function refresh_plots(){
    xAxisTicksAdjust();        
    if(stats === 'c'){
        logs_refresh_required = true;
        if(selectedview == 'logs'){
            refresh_users_activity_table();
        }
        refresh_generic_course_plot();
    }
    if(stats === 'u'){
        refresh_generic_user_plot();
    }
    if(stats === 'a'){        
        refresh_user_login_plot();
        refresh_popular_courses_plot();
        refresh_department_user_plot(department, 0);
    }
    plotsgenerated = true;
}

function refresh_generic_course_plot(){
    $.getJSON('results.php',{t:'cg', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        var options = {
            data: {
                json: data,
                x: 'time',
                xFormat: '%Y-%m-%d',
                axes: {
                    hits: 'y',
                    duration: 'y2'
                },
                types:{
                    hits: 'bar',
                    duration: 'spline'
                },
                names:{
                    hits: langHits,
                    duration: langDuration
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min: 0, padding:{top:0, bottom:0}}, y2: {show: true, label: langHours, min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#generic_stats'
        };
        /*if(typeof charts.gc !== "undefined"){
           charts.gc.destroy();
        }*/
        charts.gc = refreshChart("cp", options);
        refresh_module_pref_plot();
    });
}

function refresh_module_pref_plot(){
    $.getJSON('results.php',{t:'cmp', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        var options = {
            data: {
                json: data.chartdata,
                names: data.modules,
                type:'pie',
                onclick: function (d,i){ refresh_course_module_plot(d.id);}
                },
            bindto: '#modulepref_pie',
            tooltip: {
                format: {
                    value: function (value, ratio, id, index) { return Math.round(ratio*1000,1)/10+'% ('+value+' '+langHits+')'; }
                }
            }
        };
        /*if(typeof charts.mp !== "undefined"){
             charts.mp.destroy();
        }*/
        charts.mp = refreshChart("mp", options);
        refresh_course_module_plot(data.pmid);
    });
}

function refresh_course_module_plot(mdl){
    piemodule = mdl;
    $.getJSON('results.php',{t:'cm', s:startdate, e:enddate, i:interval, u:user, c:course, m:piemodule},function(data){
        var options = {
            data: {
                json: data.chartdata,
                x: 'time',
                xFormat: '%Y-%m-%d',
                axes: {
                    hits: 'y',
                    duration: 'y2'
                },
                types:{
                    hits: 'bar',
                    duration: 'spline'
                },
                names:{
                    hits: langHits,
                    duration: langDuration
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false, rotate:60}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min:0, padding:{top:0, bottom:0}}, y2: {show: true, label: langHours, min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#module_stats'
        };
        $("#module_stats_title").text(data.charttitle);
        charts.cm = refreshChart("cm", options);
        refresh_course_reg_plot();
        $.getJSON('results.php',{t:'cd', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
            refreshDataTable($('#cdetails1'), data);
        });
    });
}

function refresh_course_reg_plot(){
    $.getJSON('results.php',{t:'crs', s:startdate, e:enddate, i:interval, c:course},function(data){
        var options = {
            data: {
                json: data.chartdata,
                x: 'time',
                xFormat: '%Y-%m-%d',
                type:'bar',
                groups:[['regs','unregs']],
                names:{
                    regs: langRegs,
                    unregs: langUnregs
                }
            },
            size:{height:250},
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min: 0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#coursereg_stats'
        };
        charts.cr = refreshChart("cr", options);
        
        $.getJSON('results.php',{t:'crd', s:startdate, e:enddate, c:course},function(data){
            refreshDataTable($('#cdetails2'), data);
        });
    });

}

function refresh_generic_user_plot(){
    $.getJSON('results.php',{t:'ug', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        var options = {
            data: {
                json: data,
                x: 'time',
                xFormat: '%Y-%m-%d',
                axes: {
                    hits: 'y',
                    duration: 'y2'
                },
                types:{
                    hits: 'bar',
                    duration: 'spline'
                },
                names:{
                    hits: langHits,
                    duration: langDuration
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min: 0,padding:{top:0, bottom:0}}, y2: {show: true, label: langHours, min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#generic_userstats'
        };
        /*if(typeof charts.gu !== "undefined"){
             charts.gu.destroy();
        }*/
        charts.gu = refreshChart("gu", options);
        refresh_course_pref_plot();
    });
}

function refresh_course_pref_plot(){
    piecourse = -1;
    $.getJSON('results.php',{t:'ucp', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        if(data.pcid != null){
            var options = {
                data: {
                    json: data.chartdata,
                    names: data.courses,
                    type:'pie',
                    onclick: function (d,i){piecourse = d.id;refresh_user_course_plot();}
                    },
                bindto: '#coursepref_pie',
                tooltip: {
                    format: {
                        value: function (value, ratio, id, index) { return Math.round(ratio*1000,1)/10+'% ('+value+' '+langHits+')'; }
                    }
                }
            };
            piecourse = data.pcid;
        }
        else{
            encapsulateddata = data.chartdata;
            piemodule = encapsulateddata.pmid;
            var options = {
                data: {
                    json: encapsulateddata.chartdata,
                    type:'pie',
                    names: encapsulateddata.modules,
                    onclick: function (d,i){piemodule = d.id; refresh_user_course_plot();}
                    },
                bindto: '#coursepref_pie'
            };
        }
        charts.cp = refreshChart("cp", options);
        
        piecourse = (piecourse < 0)? course:piecourse;
        piemodule = (piemodule < 0)? module:piemodule;
        refresh_user_course_plot();
    });
}

function refresh_user_course_plot(){        
    $.getJSON('results.php',{t:'uc', s:startdate, e:enddate, i:interval, u:user, c:piecourse, m:piemodule},function(data){
        if(data.chartdata.chartdata == null){
            $("#course_stats_title").text(data.charttitle);
            chartdata = data.chartdata;
        }
        else{
            $("#course_stats_title").text(data.charttitle+': '+data.chartdata.charttitle);
            chartdata = data.chartdata.chartdata;
        }
        var options = {
            data: {
                json: chartdata,
                x: 'time',
                xFormat: '%Y-%m-%d',
                axes: {
                    hits: 'y',
                    duration: 'y2'
                },
                types:{
                    hits: 'bar',
                    duration: 'spline'
                },
                names:{
                    hits: langHits,
                    duration: langDuration
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false, rotate:60}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min:0, padding:{top:0, bottom:0}}, y2: {show: true, label: langHours, min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#course_stats'
        };
        charts.uc = refreshChart("uc", options);
        $.getJSON('results.php',{t:'ud', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
            refreshDataTable($('#udetails1'), data);
        });
    });
}

function refresh_user_login_plot(){
    $.getJSON('results.php',{t:'ul', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department},function(data){
        var options = {
            data: {
                json: data,
                x: 'time',
                xFormat: '%Y-%m-%d',
                type:'area',
                names:{
                    logins: langLoginUser,
                    visits: langHits
                },
                axes:{
                    logins: 'y'
                }
            },
            zoom:{enabled:true},
            size:{height:250},
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], rotate:60, values:xTicks, fit:false}, min: xMinVal, label: xAxisLabels[interval]}, y:{padding:{top:0, bottom:0}}},
            bindto: '#userlogins_stats'
        };
        /*if(typeof charts.ul !== "undefined"){
           charts.ul.destroy();
        }*/
        charts.ul = refreshChart("ul", options);
        
    });
}

function refresh_popular_courses_plot(){
    $.getJSON('results.php',{t:'pcs', s:startdate, e:enddate, d:department},function(data){
        var options = {
            data: {
                json: data,
                x: 'courses',
                axes: {
                    hits: 'y'                },
                types:{
                    hits: 'bar'
                },
                names:{
                    hits: langHits
                }
            },
            axis:{ rotated:false, x: {type:'category', tick:{inner:true}}, y:{show:false}},
            size:{height:250},
            bar:{width:{ratio:0.9}},
            legend:{show: false},
            bindto: '#popular_courses'
        };
        charts.pcs = refreshChart("pcs", options);
    });
}

function refresh_department_user_plot(depid, leafdepartment){
    if(leafdepartment>0){
        return null;
    }
    department = depid;
    $.getJSON('results.php',{t:'du', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department},function(data){
        var options = {
            data: {
                json: data.chartdata,
                x: 'department',
                type:'bar',
                groups:[['status1','status5']],
                onclick: function (d,i){refresh_department_user_plot(data.deps[d.index], data.leafdeps[d.index]);}
            },
            size:{height:250},
            bar:{width:{ratio:0.9}},
            axis:{ x: {type:'category', label:langDepartment}, y:{label:langUsers, min: 0, padding:{top:0, bottom:0}, tick:{format: d3.format('d')}}},
            bindto: '#depuser_stats'
        };
        charts.du = refreshChart("du", options);
        
        department_details = new Array();
        
        for(i=0;i<data.chartdata.department.length;i++){
            department_details.push(new Array);
        }
        for(var column in data.chartdata){
            $.each(data.chartdata[column], function(rowid, val){
                department_details[rowid].push(val);
            });
        }
        refresh_department_course_plot(department, leafdepartment);
        $.getJSON('results.php',{t:'uld', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department},function(data){
            refreshDataTable($('#adetails2'), data);
        });
    });
}

function refresh_department_course_plot(depid, leafdepartment){
    if(leafdepartment>0){
        return null;
    }
    department = depid;
    $.getJSON('results.php',{t:'dc', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department},function(data){
       var options = {
            data: {
                json: data.chartdata,
                x: 'department',
                type:'bar',
                groups:[['visibility1','visibility2','visibility3','visibility4']],
                onclick: function (d,i){refresh_department_user_plot(data.deps[d.index], data.leafdeps[d.index]);}
            },
            size:{height:250},
            bar:{width:{ratio:0.9}},
            axis:{ x: {type:'category', label:langDepartment}, y:{label:langCourses, min: 0, padding:{top:0, bottom:0}, tick:{format: d3.format('d')}}},
            //bar:{width:{ratio:0.3}},
            bindto: '#depcourse_stats'
        };
        charts.dc = refreshChart("dc", options);
        
        for(var column in data.chartdata){
            $.each(data.chartdata[column], function(rowid, val){
                if(column !== 'department'){
                    department_details[rowid].push(val);
                }
            });
        }
        refreshDataTable($('#adetails1'), department_details);
        fillTableTotalUsers();
    });
}
            
function refresh_users_activity_table(){
    if(logs_refresh_required){
        $.getJSON('results.php',{t:'cad', s:startdate, e:enddate, u:user, c:course, m:module},function(data){
            refreshDataTable($('#cdetails3'), data);
        });
        logs_refresh_required = false;
    }
}

function adjust_interval_options(){
    if($('#interval').length){
        dayMilliseconds = 24*60*60*1000;
        diffInDays = (edate-sdate)/dayMilliseconds;
        $('#interval > option').each(function(){
            intervalsNumber = diffInDays/$(this).val();
            if(intervalsNumber<=0 || intervalsNumber>maxintervals){
                $(this).hide();
            }
           else{
                $(this).show();
            }
        });
    }
}

function xAxisTicksAdjust()
{
	var xmin = sdate;
	var xmax = edate;
	
        dayMilliseconds = 24*60*60*1000;
        diffInDays = (edate-sdate)/dayMilliseconds;
        xTicks = new Array();
	var tick = new Date(xmin);
        cur = xmin.getMonth();
        if(interval == 1){
            xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-'+tick.getDate();
            xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-'+xmax.getDate();
            if(tick.getDate() == 1){
                xTicks.push(xMinVal);
            }
            while(tick <= xmax)
            {
                    tick.setDate(tick.getDate() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(cur != tick.getMonth()){
                        xTicks.push(tickval);
                        cur = tick.getMonth();
                    }
            }    
        }
        else if(interval == 7){
            xminMonday = new Date(xmin.getTime() - xmin.getUTCDay()*dayMilliseconds);
            xMinVal = xminMonday.getFullYear()+'-'+(xminMonday.getMonth()+1)+'-'+xminMonday.getDate();
            xmaxMonday = new Date(xmax.getTime() + (7-xmax.getUTCDay())*dayMilliseconds);
            xMaxVal = xmaxMonday.getFullYear()+'-'+(xmaxMonday.getMonth()+1)+'-'+xmaxMonday.getDate();
            xTicks.push(xMinVal);
            tick = new Date(xminMonday);
            i = 1;
            while(tick <= xmaxMonday)
            {
                    tick.setDate(tick.getDate() + 7);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(i % 2 == 0){
                        xTicks.push(tickval);
                    }
                    i++;
                    
            } 
        }
        else if(interval == 30){
            xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-15';
            xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-15';
            xTicks.push(xMinVal);
            while(tick <= xmax)
            {
                    tick.setMonth(tick.getMonth() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-15';
                    xTicks.push(tickval);
            } 
        }
        else if(interval == 365){
            xMinVal = xmin.getFullYear()+'-06-30';
            xMaxVal = xmax.getFullYear()+'-06-30';
            xTicks.push(xMinVal);
            while(tick <= xmax)
            {
                    tick.setFullYear(tick.getFullYear() + 1);
                    tickval = tick.getFullYear()+'-06-30';
                    xTicks.push(tickval);
            }     
        }
}

function refreshDataTable(datatableel, datarows)
{
    datatableel.DataTable().clear();
    datatableel.DataTable().rows.add(datarows).draw();
}

function refreshChart(c, opt){
    if(typeof charts[c] !== "undefined"){
           charts[c].destroy();
    }
    return c3.generate(opt);
}

function fillTableTotalUsers(){
    $.getJSON('results.php',{t:'du', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department, o:1},function(data){
        detailsTable1api = $('#adetails1').dataTable().api();
        header = $(detailsTable1api.columns(1).header()).text();
        $(detailsTable1api.columns(1).footer()).html(data.chartdata[header][0]);
        header = $(detailsTable1api.columns(2).header()).text();
        $(detailsTable1api.columns(2).footer()).html(data.chartdata[header][0]);
    });
}

function footerCB(tabId,tabEl){
    return function(){
        tabApi = $('#'+tabEl).dataTable().api();
        for(i=0;i<tableOptions[stats][tabId].sumCols.length;i++){
            c = tableOptions[stats][tabId].sumCols[i];
            if(c == tableOptions[stats][tabId].durCol){
                $( tabApi.columns( c ).footer() ).html( userFriendlyDuration(
                    tabApi.column( c ).data().reduce( function ( a, b ) {
                        return parseInt(a) + parseInt(b);
                    }, 0 )
                ));    
            }
            else{
                $( tabApi.columns( c ).footer() ).html(
                    tabApi.column( c ).data().reduce( function ( a, b ) {
                        return parseInt(a) + parseInt(b);
                    }, 0 )
                );
            }
        }
    }
}
    
function userFriendlyDuration(seconds){    
    seconds = Math.abs(seconds);
    hours = Math.floor(seconds / 3600);
    mins = Math.floor((seconds - (hours*3600)) / 60);    
    secs = Math.floor(seconds % 60);
    fd = (hours<10)? '0'+hours:''+hours;
    fd += ':';
    fd += (mins<10)? '0'+mins:mins;
    fd += ':';
    fd += (secs<10)? '0'+secs:secs;
    return fd;
}

function userEmailLink(user, email, username){
    return "<a href='mailto:"+email+"' title='"+username+"'>"+user+"</a>";
}

function actionWithDetails(action, row, indexWithDetails){
    return "<a>"+action+" <span class='fa fa-caret-right'></span> </a>";
}

function cellHover(text){
    return text;
}
