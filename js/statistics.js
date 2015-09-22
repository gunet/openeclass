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
        1:{sumCols:[3,4,5,6], colDefs:[], durCol:null}, 
        2:{sumCols:[], colDefs:[], durCol:null}
    }, 
    'u':{
        1:{sumCols:[3,4], durCol:4, colDefs:[{'targets':4, 'render': function ( data, type, full, meta ) {return type === 'display' ? userFriendlyDuration(data): data;} }]}
    }, 
    'c':{
        1:{sumCols:[3,4], durCol:4, colDefs:[{'targets':4, 'render': function ( data, type, full, meta ) {return type === 'display' ? userFriendlyDuration(data): data;}}]},
        2:{sumCols:[], durCol:null, colDefs:[]}
    }
};
charts = new Object();

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
    });
    $('#enddate').blur(function(){
        adjust_interval_options();
        refresh_plots();
    });
   $('#plots-view').click(function(){
        if(selectedview == 'list'){
            $('#list-view').removeClass("active");
            $(this).addClass("active");
            selectedview = 'plots';
            $('.plotscontainer').show();
            $('.detailscontainer').hide();
        }
    });
    $('#list-view').click(function(){
        if(selectedview == 'plots'){
            $('#plots-view').removeClass("active");
            $(this).addClass("active");
            selectedview = 'list';
            $('.detailscontainer').show();
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
    detailsTables = new Object();
    tableTools = new Object();
    
    /*******************/
    
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
    
    for(tableid in tableOptions[stats]){
        tableElId = stats+'details'+tableid;
        colDefs = tableOptions[stats][tableid].colDefs;
        detailsTables[tableElId] = $('#'+tableElId).DataTable({
           'sPaginationType': 'full_numbers',
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
            dom: 'Bfrtip',
            columnDefs: colDefs,
            'bAutoWidth': true,                
            'footerCallback': footerCB(tableid, tableElId),
            'columnDefs': colDefs,
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
        
    }
    /**************/
    /*************
    intColumns = [];
    detailsTables[stats+'details1']= $('#'+stats+'details1').DataTable ({                                
        'sPaginationType': 'full_numbers',
        'bAutoWidth': true,                
        'footerCallback': function() {
               for(i=0;i<intColumns.length;i++){
                   c = intColumns[i];
                   console.log('sum col:'+c+' for table '+stats+'details1');
                   console.log('το καλό: '+$(this.api().column( c ).header()).text()+' rows are '+this.api().column( c ).data().length);
                   $( this.api().columns( c ).footer() ).html(
                        this.api().column( c ).data().reduce( function ( a, b ) {
                            console.log('reduce');
                            return parseInt(a) + parseInt(b);
                        }, 0 )
                    );
                }
        },
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
    tableTools[stats+'details1'] = new $.fn.dataTable.TableTools( detailsTables[stats+'details1'], {
        "sSwfPath": "http://eclass.test.noc.ntua.gr/newui/js/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
    } );
    //$( tableTools1.fnContainer() ).insertAfter('#toggle-view');
    $( tableTools[stats+'details1'].fnContainer() ).insertBefore('#'+stats+'details1');
    detailsTables[stats+'details2'] = $('#'+stats+'details2').DataTable ({                                
        'sPaginationType': 'full_numbers',
        'bAutoWidth': true,                
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
    tableTools[stats+'details2'] = new $.fn.dataTable.TableTools( detailsTables[stats+'details2'], {
        "sSwfPath": "http://eclass.test.noc.ntua.gr/newui/js/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
    } );
    $( tableTools[stats+'details2'].fnContainer() ).insertBefore('#'+stats+'details2');
    *************/
    $('.detailscontainer').hide();
    refresh_plots();
    
    
});//document ready   

function refresh_plots(){
    xAxisTicksAdjust();
    if(stats === 'c'){
        refresh_generic_course_plot();
    }
    if(stats === 'u'){
        refresh_generic_user_plot();
    }
    if(stats === 'a'){
        refresh_user_login_plot();
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
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min: 0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
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
                type:'pie',
                onclick: function (d,i){refresh_course_module_plot(data.modules[d.index]);}
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
    module = mdl;
    $.getJSON('results.php',{t:'cm', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
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
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false, rotate:60}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min:0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
            bar:{width:{ratio:0.3}},
            bindto: '#module_stats'
        };
        $("#moduletitle").text(data.charttitle);
        /*if(typeof charts.cm !== "undefined"){
             charts.cm.destroy();
        }*/
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
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min: 0,padding:{top:0, bottom:0}}, y2: {show: true, label:langDuration+' (sec)', min: 0, padding:{top:0, bottom:0}}},
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
    $.getJSON('results.php',{t:'ucp', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        if(data.pcid != null){
            var options = {
                data: {
                    json: data.chartdata,
                    type:'pie',
                    onclick: function (d,i){course = data.courses[d.index];refresh_user_course_plot();}
                    },
                bindto: '#coursepref_pie',
                tooltip: {
                    format: {
                        value: function (value, ratio, id, index) { return Math.round(ratio*1000,1)/10+'% ('+value+' '+langHits+')'; }
                    }
                }
            };
            course = data.pcid;
        }
        else{
            encapsulateddata = data.chartdata;
            module = encapsulateddata.pmid;
            var options = {
                data: {
                    json: encapsulateddata.chartdata,
                    type:'pie',
                    onclick: function (d,i){module = encapsulateddata.modules[d.index];refresh_user_course_plot();}
                    },
                bindto: '#coursepref_pie'
            };
        }
        charts.cp = refreshChart("cp", options);
           
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
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], values:xTicks, fit:false, rotate:60}, label: xAxisLabels[interval], min: xMinVal}, y:{label:langHits, min:0, padding:{top:0, bottom:0}}, y2: {show: true, label:'sec', min: 0, padding:{top:0, bottom:0}}},
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
    $.getJSON('results.php',{t:'ul', s:startdate, e:enddate, i:interval, u:user, c:course, m:module},function(data){
        var options = {
            data: {
                json: data,
                x: 'time',
                xFormat: '%Y-%m-%d',
                type:'area'
            },
            zoom:{enabled:true},
            size:{height:250},
            axis:{ x: {type:'timeseries', tick:{format: xAxisDateFormat[interval], rotate:60, values:xTicks, fit:false}, min: xMinVal, label: xAxisLabels[interval]}, y:{label:'logins', padding:{top:0, bottom:0}}},
            bindto: '#userlogins_stats'
        };
        /*if(typeof charts.ul !== "undefined"){
           charts.ul.destroy();
        }*/
        charts.ul = refreshChart("ul", options);
        
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
            //bar:{width:50},
            axis:{ x: {type:'category', label:'department'}, y:{label:langUsers, min: 0, padding:{top:0, bottom:0}, tick:{format: d3.format('d')}}},
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

/*"sDom": 'lfrtip<"clear spacer">T',
      "oTableTools": {
            "aButtons": [
                {
                    "sExtends": "csv",
                    "fnClick": function( nButton, oConfig, flash ) {
                            var s = '';
                            var a = TableTools.fnGetMasters();
                            for ( var i=0, iLen=a.length ; i<iLen ; i++ ) {
                                s += a[i].fnGetTableData( oConfig ) +"\n";
                            }
                            this.fnSetText(flash, s);  
                    }
                }      
            ]
        }
       }*/
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
    
function userFriendlyDuration(seconds){
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


