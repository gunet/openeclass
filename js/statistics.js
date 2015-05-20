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
//var views = {plots:{class: 'fa fa-bar-chart', title: '$langPlots'}, list:{class: 'fa fa-list', title: '$langDetails'}};
var selectedview = 'plots';
//var maxintervals = 20;
//var lang = $language;
var xAxisDateFormat = {1:'%d-%m-%Y', 7:'%d-%m-%Y', 30:'%m-%Y', 365:'%Y'};
var xAxisLabels = {1:'day', 7:'week', 30:'month', 365:'year'};
var xMinVal = null;
var xMaxVal = null;
var xTicks = null;

$(document).ready(function(){
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
    });
    $('#enddate').blur(function(){
        adjust_interval_options();
        refresh_plots();
    });
    $('#toggle-view').click(function(){
        $(this).children("i").attr('class', views[selectedview].class);
        $(this).children("i").attr('data-original-title', views[selectedview].title);
        if(selectedview == 'plots'){
            selectedview = 'list';
        }
        else{
            selectedview = 'plots';
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
            }
            refresh_plots();
        });
    }
    refresh_plots();
    
});

function refresh_plots(){
    xAxisTicksAdjust();
    if(stats == 'c'){
        refresh_generic_course_plot();
    }
    if(stats == 'u'){
        refresh_generic_user_plot();
    }
    if(stats == 'a'){
        refresh_department_user_plot(department);
        refresh_user_login_plot();
    }
}

function refresh_generic_course_plot(){
    $.getJSON('results.php',{t:'cg', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        c3.generate({
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
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:'hits', min: 0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#generic_stats'
        });
        refresh_module_pref_plot();
    });
}

function refresh_module_pref_plot(){
    $.getJSON('results.php',{t:'cmp', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        c3.generate({
            data: {
                json: data.chartdata,
                type:'pie',
                onclick: function (d,i){refresh_course_module_plot(data.modules[d.index]);}
                },
            bindto: '#modulepref_pie'
        });
        refresh_course_module_plot(data.pmid);
    });
}
function refresh_course_module_plot(mdl){
    module = mdl;
    $.getJSON('results.php',{t:'cm', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        $("#moduletitle").text(data.charttitle);
        c3.generate({
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
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:'hits', min:0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#module_stats'
        });
    });
}



function refresh_generic_user_plot(){
    $.getJSON('results.php',{t:'ug', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        c3.generate({
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
                }
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:'hits', min: 0,padding:{top:0, bottom:0}}, y2: {show: true, label:'duration sec', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#generic_userstats'
        });
        refresh_course_pref_plot();
    });
}

function refresh_course_pref_plot(){
    $.getJSON('results.php',{t:'ucp', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        if(data.pcid != null){
            c3.generate({
                data: {
                    json: data.chartdata,
                    type:'pie',
                    onclick: function (d,i){course = data.courses[d.index];refresh_user_course_plot();}
                    },
                bindto: '#coursepref_pie'
            });
            course = data.pcid;
        }
        else{
            encapsulateddata = data.chartdata;
            module = encapsulateddata.pmid;
            c3.generate({
                data: {
                    json: encapsulateddata.chartdata,
                    type:'pie',
                    onclick: function (d,i){module = encapsulateddata.modules[d.index];refresh_user_course_plot();}
                    },
                bindto: '#coursepref_pie'
            });    
        }
        refresh_user_course_plot();
    });
}
function refresh_user_course_plot(){
    $.getJSON('results.php',{t:'uc', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        if(data.chartdata.chartdata == null){
            $("#coursetitle").text(data.charttitle);
            chartdata = data.chartdata;
        }
        else{
            $("#coursetitle").text(data.charttitle+': '+data.chartdata.charttitle);
            chartdata = data.chartdata.chartdata;
        }
        c3.generate({
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
            },
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:'hits', min:0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#course_stats'
        });
    });
}

function refresh_department_user_plot(depid){
    department = depid;
    $.getJSON('results.php',{t:'du', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department},function(data){
        c3.generate({
            data: {
                json: data.chartdata,
                x: 'department',
                type:'bar',
                groups:[['status1','status5']],
                onclick: function (d,i){refresh_department_user_plot(data.deps[d.index]);}
            },
            size:{height:250},
            //bar:{width:50},
            axis:{ x: {type:'category', label:'department'}, y:{label:'users', min: 0, padding:{top:0, bottom:0}, tick:{format: d3.format('d')}}},
            bindto: '#depuser_stats'
        });
        refresh_department_course_plot(department);
    });
}
function refresh_department_course_plot(depid){
    department = depid;
    $.getJSON('results.php',{t:'dc', s:startdate, e:enddate, i:interval, u:user, c:course, m:module, d:department},function(data){
        c3.generate({
            data: {
                json: data.chartdata,
                x: 'department',
                type:'bar',
                groups:[['visibility1','visibility2','visibility3','visibility4']],
                onclick: function (d,i){console.log('department '+d.index+' was pressed');refresh_department_user_plot(data.deps[d.index]);}
            },
            size:{height:250},
            axis:{ x: {type:'category', label:'department'}, y:{label:'courses', min: 0, padding:{top:0, bottom:0}, tick:{format: d3.format('d')}}},
            //bar:{width:{ratio:0.3}},
            bindto: '#depcourse_stats'
        });
    });
}

function refresh_user_login_plot(){
    $.getJSON('results.php',{t:'ul', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        c3.generate({
            data: {
                json: data,
                x: 'time',
                xFormat: '%Y-%m-%d',
                type:'area',
                groups:[['logins']]
            },
            zoom:{enabled:true},
            size:{height:250},
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], rotate:60, values:xTicks, fit:false}, min: xMinVal, label: xAxisLabels[interval]}, y:{label:'logins', padding:{top:0, bottom:0}, tick:{format: d3.format('d')}}},
            bindto: '#userlogins_stats'
        });
    });
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
	xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-'+1;
	xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-'+xmax.getDate();
	dayMilliseconds = 24*60*60*1000;
        diffInDays = (edate-sdate)/dayMilliseconds;
        xTicks = [xMinVal];
	var tick = new Date(xmin);
        i = 0;
        cur = xmin.getMonth();
        if(interval == 1){
            while(tick < xmax)
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
            while(tick < xmax)
            {
                    tick.setDate(tick.getDate() + 7);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(i % 7 == 0)
                        xTicks.push(tickval);
                    i++;
            } 
        }
        else if(interval == 30){
            while(tick < xmax)
            {
                    tick.setMonth(tick.getMonth() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    xTicks.push(tickval);
            } 
        }
        else if(interval == 365){
            while(tick < xmax)
            {
                    tick.setFullYear(tick.getFullYear() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    xTicks.push(tickval);
            }     
        }
	xTicks.push(xMaxVal);
}




