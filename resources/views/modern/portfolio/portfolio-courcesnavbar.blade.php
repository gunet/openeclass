<div class="row justify-content-between">
    <div class="col-6">
        <div style="display:flex" class="cources-{{$paging_type}}-num-group" >
            <div type="button" onclick="cources_paging_left('{{$paging_type}}')"> 
                <div class="btn_left_rigth"><i class="fas fa-chevron-left  cources-paging-arrow arrow-left"></i></div> 
            </div>&emsp;
            @for($i=0; $i<$cource_pages; $i++ )
                @if( $i==1 )
                <div class="cources-paging-ellipsis" id="cources-{{$paging_type}}-num-ellipsis-left" style="display:none">. . .</div>&nbsp;
                @endif
                
                
                
                <div type="button" onclick="cources_paging_goto('{{$paging_type}}',{{$i+1}})" 
                    class="cources-paging-num @if( $i==0 ) text-primary @endif "
                    @if( $i>2 && $i!=$cource_pages-1 )
                        style="display:none"
                    @endif 
                    id="cources-{{$paging_type}}-num-{{$i+1}}" >{{$i+1}}&nbsp;</div>
                @if( $i==$cource_pages-2 )
                <div class="cources-paging-ellipsis" id="cources-{{$paging_type}}-num-ellipsis-right" 
                    @if($i<3) style="display:none" @endif
                >. . .</div>&nbsp;
                @endif
            @endfor&emsp;
            <div type="button" onclick="cources_paging_right('{{$paging_type}}')"> 
                <div class="btn_left_rigth"><i class="fas fa-chevron-right cources-paging-arrow arrow-right"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 text-primary text-end"><a class="all_courses_a" href="{{ $urlAppend }}main/my_courses.php">ΟΛΑ ΤΑ ΜΑΘΗΜΑΤΑ</a><i class="fas fa-chevron-right"></i></div>
</div>

<script>
    function cources_paging_right(type) {

        var active_page = cources_paging_get_active(type);
        console.log("cources_paging_right: "+type+" - "+active_page+" - "+user_cource_pages);
        if( active_page < user_cource_pages ) {
            cources_paging_switch_active(type,active_page,(active_page+1));
        }
    }

    function cources_paging_left(type) {

        var active_page = cources_paging_get_active(type);
        console.log("cources_paging_left: "+type+" - "+active_page+" - "+user_cource_pages);
        if( active_page > 0 ) {
            cources_paging_switch_active(type,active_page,(active_page-1));
        }
    }

    function cources_paging_goto(type,page_num) {
        var active_page = cources_paging_get_active(type);

        if( active_page != page_num ) {
            cources_paging_switch_active(type,active_page,page_num);
        }
    }

    function cources_paging_get_active(type) {

        var page_elems = $('#cources-'+type).children();
        for (var i = 0; i < page_elems.length-1; i++) {
            if($(page_elems[i]).is(':visible') ) {  // active_page = i; // console.log(i);
                return (i+1);
            }
        }
        return 1;
    }

    function cources_paging_switch_active(type,old_active_index,new_active_index) {
        old_active_id = '#cources-'+type+'-page-'+old_active_index;
        new_active_id = '#cources-'+type+'-page-'+new_active_index;
        $(old_active_id).hide();
        $(new_active_id).show();
        old_active_num = '#cources-'+type+'-num-'+old_active_index;
        new_active_num = '#cources-'+type+'-num-'+new_active_index;
        $(old_active_num).removeClass('text-primary');
        $(new_active_num).addClass('text-primary');

        cources_paging_fix_selection_bar(type,new_active_index-1);
    }


    function cources_paging_fix_selection_bar(type,index) {

        var num_elems = $( '.cources-'+type+'-num-group').children('.cources-paging-num'); 

        console.log(1-index);
        console.log((num_elems.length-1)-index );

        if( index==1 ) { index--; }
        if(index == num_elems.length-1 ) { index--; }
        
        for(var i=1; i<num_elems.length-1; i++) {
            if(i-index < -1) {
                $(num_elems[i]).hide();
            } else if(i-index > 2) {
                $(num_elems[i]).hide();
            } else {
                $(num_elems[i]).show();
            }
        }

        if( 1-index < -1 ) {
            $('#cources-'+type+'-num-ellipsis-left').show();
        } else {
            $('#cources-'+type+'-num-ellipsis-left').hide();
        }
        
        if( (num_elems.length-1)-index > 3 ) {
            $('#cources-'+type+'-num-ellipsis-right').show();
        } else {
            $('#cources-'+type+'-num-ellipsis-right').hide();
        }
    }
</script>