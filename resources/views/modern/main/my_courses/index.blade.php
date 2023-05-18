@extends('layouts.default')

@section('content')

        <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

            <div class="container-fluid main-container">


                <div class="row rowMedium">

                    @include('layouts.partials.all_my_courses_view',['myCourses' => $myCourses])

                </div>

            </div>
        </div>

        <script>

            var arrayLeftRight = [];
            
            // init page1
            if(arrayLeftRight.length == 0){
                var totalCourses = $('#KeyallCourse').val();
                
                for(j=1; j<=totalCourses; j++){
                    if(j!=1){
                        $('.cardCourse'+j).removeClass('d-block');
                        $('.cardCourse'+j).addClass('d-none');
                    }else{
                        $('.page-item-previous').addClass('disabled');
                        $('.cardCourse'+j).removeClass('d-none');
                        $('.cardCourse'+j).addClass('d-block');
                        $('#Keypage1').addClass('active');
                    }
                }
                var totalPages = $('#KeypagesCourse').val();
                if(totalPages == 1){
                    $('.page-item-previous').addClass('disabled');
                    $('.page-item-next').addClass('disabled');
                }
            }


            // prev-button
            $('.page-item-previous .page-link').on('click',function(){

                var prevPage;

                $('.page-item-pages .page-link.active').each(function(index, value){
                    var IDCARD = this.id;
                    var number = parseInt(IDCARD.match(/\d+/g));
                    prevPage = number-1;

                    arrayLeftRight.push(number);
    
                    var totalCourses = $('#KeyallCourse').val();
                    var totalPages = $('#KeypagesCourse').val();
                    for(i=1; i<=totalCourses; i++){
                        if(i == prevPage){
                            $('.cardCourse'+i).removeClass('d-none');
                            $('.cardCourse'+i).addClass('d-block');
                            $('#Keypage'+prevPage).addClass('active');
                        }else{
                            $('.cardCourse'+i).removeClass('d-block');
                            $('.cardCourse'+i).addClass('d-none');
                            $('#Keypage'+i).removeClass('active');
                        }
                    }

                    if(prevPage == 1){
                        $('.page-item-previous').addClass('disabled');
                    }else{
                        if(prevPage < totalPages){
                            $('.page-item-next').removeClass('disabled');
                        }
                        $('.page-item-previous').removeClass('disabled');
                    }


                    //create page-link in center
                    if(number <= totalPages-3 && number >= 6 && totalPages>=12){

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');
                        
                        for(i=2; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

                        var currentPage = number-1;
                        $('#KeypageCenter'+currentPage).removeClass('d-none');
                        $('#KeypageCenter'+currentPage).addClass('d-block');

                        var prevPage = number-2;
                        $('#KeypageCenter'+prevPage).removeClass('d-none');
                        $('#KeypageCenter'+prevPage).addClass('d-block');

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                    }else if(number <= 5 && totalPages>=12){

                        $('#KeystartLi').removeClass('d-block');
                        $('#KeystartLi').addClass('d-none');

                        for(i=6; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                        
                        for(i=1; i<=number; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }

                    }

                });

            });




            // next-button
            $('.page-item-next .page-link').on('click',function(){

                $('.page-item-pages .page-link.active').each(function(index, value){
                    var IDCARD = this.id;
                    var number = parseInt(IDCARD.match(/\d+/g));
                    arrayLeftRight.push(number);
                    var nextPage = number+1;

                    var delPageActive = nextPage-1;
                    $('#Keypage'+delPageActive).removeClass('active');
                    $('#Keypage'+nextPage).addClass('active');
                
                    var totalCourses = $('#KeyallCourse').val();
                    var totalPages = $('#KeypagesCourse').val();
                    
                    for(i=1; i<=totalCourses; i++){
                        if(i == nextPage){
                            $('.cardCourse'+i).removeClass('d-none');
                            $('.cardCourse'+i).addClass('d-block');
                            // $('#Keypage'+nextPage).addClass('active');
                        }else{
                            $('.cardCourse'+i).removeClass('d-block');
                            $('.cardCourse'+i).addClass('d-none');
                            //$('#Keypage'+i).removeClass('active');
                        }
                    }

                    if(totalPages > 1){
                        $('.page-item-previous').removeClass('disabled');
                    }
                    if(nextPage == totalPages){
                        $('.page-item-next').addClass('disabled');
                    }else{
                        $('.page-item-next').removeClass('disabled');
                    }


                    //create page-link in center
                    if(number >= 4 && number < totalPages-5 && totalPages>=12){//5-7

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');
                        
                        for(i=2; i<=totalPages-1; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-none');
                        $('#KeypageCenter'+arrayLeftRight[arrayLeftRight.length-1]).removeClass('d-block');

                        var currentPage = number+1;
                        $('#KeypageCenter'+currentPage).removeClass('d-none');
                        $('#KeypageCenter'+currentPage).addClass('d-block');

                        var nextPage = number+2;
                        $('#KeypageCenter'+nextPage).removeClass('d-none');
                        $('#KeypageCenter'+nextPage).addClass('d-block');

                        $('#KeycloseLi').removeClass('d-none');
                        $('#KeycloseLi').addClass('d-block');

                    }else if(arrayLeftRight[arrayLeftRight.length-1] >= totalPages-5 && totalPages>=12){//>=8

                        $('#KeystartLi').removeClass('d-none');
                        $('#KeystartLi').addClass('d-block');

                        for(i=2; i<=totalPages-5; i++){
                            $('#KeypageCenter'+i).removeClass('d-block');
                            $('#KeypageCenter'+i).addClass('d-none');
                        }

                        $('#KeycloseLi').removeClass('d-block');
                        $('#KeycloseLi').addClass('d-none');

                        var nextPage = arrayLeftRight[arrayLeftRight.length-1] + 1;
                        console.log('nextPage:'+nextPage);
                        for(i=nextPage; i<=totalPages; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }

                    }else if(number>=1 && number<=4 && totalPages>=12){
                        $('#KeystartLi').removeClass('d-block');
                        $('#KeystartLi').addClass('d-none');

                        for(i=1; i<=4; i++){
                            $('#KeypageCenter'+i).removeClass('d-none');
                            $('#KeypageCenter'+i).addClass('d-block');
                        }
                    }

                    
                });
            });




            // page-link except prev-next button
            $('.page-item-pages .page-link').on('click',function(){
                
                var IDCARD = this.id;
                var number = parseInt(IDCARD.match(/\d+/g));

                arrayLeftRight.push(number);

                var totalPages = $('#KeypagesCourse').val();
                var totalCourses = $('#KeyallCourse').val();
                for(i=1; i<=totalCourses; i++){
                    if(i!=number){
                        $('.cardCourse'+i).removeClass('d-block');
                        $('.cardCourse'+i).addClass('d-none');
                    }else{
                        $('.cardCourse'+i).removeClass('d-none');
                        $('.cardCourse'+i).addClass('d-block');
                    }

                    // about prev-next button
                    if(number>1){
                        $('.page-item-previous').removeClass('disabled');
                        $('.page-item-next').removeClass('disabled');
                    }if(number == 1){
                        if(totalPages == 1){
                            $('.page-item-previous').addClass('disabled');
                            $('.page-item-next').addClass('disabled');
                        }
                        if(totalPages > 1){
                            $('.page-item-previous').addClass('disabled');
                            $('.page-item-next').removeClass('disabled');
                        }
                    }if(number == totalPages){
                        $('.page-item-next').addClass('disabled');
                    }if(number < totalPages-1){
                        $('.page-item-next').removeClass('disabled');
                    }
                }

               
                if(number>=1 && number<=4 && totalPages>=12){

                    $('#KeystartLi').removeClass('d-block');
                    $('#KeystartLi').addClass('d-none');

                    for(i=1; i<=5; i++){
                        $('#KeypageCenter'+i).removeClass('d-none');
                        $('#KeypageCenter'+i).addClass('d-block'); 
                    }
                    for(i=6; i<=totalPages-1; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    $('#KeycloseLi').removeClass('d-none');
                    $('#KeycloseLi').addClass('d-block');
                }
                if(number>=5 && number<=totalPages-5 && totalPages>=12){

                    for(i=5; i<=totalPages-1; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    var prevPage = number-1;
                    var nextPage = number+1;
                    var currentPage = number;

                    $('#KeystartLi').removeClass('d-none');
                    $('#KeystartLi').addClass('d-block');

                    for(i=2; i<=4; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    $('#KeypageCenter'+prevPage).removeClass('d-none');
                    $('#KeypageCenter'+prevPage).addClass('d-block');

                    $('#KeypageCenter'+currentPage).removeClass('d-none');
                    $('#KeypageCenter'+currentPage).addClass('d-block');

                    $('#KeypageCenter'+nextPage).removeClass('d-none');
                    $('#KeypageCenter'+nextPage).addClass('d-block');

                    $('#KeycloseLi').removeClass('d-none');
                    $('#KeycloseLi').addClass('d-block');

                }
                if(number>=totalPages-4 && totalPages>=12){

                    $('#KeystartLi').removeClass('d-none');
                    $('#KeystartLi').addClass('d-block');

                    for(i=2; i<=totalPages-5; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    for(i=totalPages-4; i<=totalPages; i++){
                        $('#KeypageCenter'+i).removeClass('d-none');
                        $('#KeypageCenter'+i).addClass('d-block');
                    }


                    $('#KeycloseLi').removeClass('d-block');
                    $('#KeycloseLi').addClass('d-none');
                }
                if(number==totalPages-4 && arrayLeftRight[arrayLeftRight.length-2]>number && totalPages>=12){

                    $('#KeystartLi').removeClass('d-none');
                    $('#KeystartLi').addClass('d-block');

                    for(i=2; i<=totalPages-1; i++){
                        $('#KeypageCenter'+i).removeClass('d-block');
                        $('#KeypageCenter'+i).addClass('d-none');
                    }

                    var prevPage = number+1;
                    var nextPage = number-1;
                    var currentPage = number;

                    $('#KeypageCenter'+prevPage).removeClass('d-none');
                    $('#KeypageCenter'+prevPage).addClass('d-block');

                    $('#KeypageCenter'+currentPage).removeClass('d-none');
                    $('#KeypageCenter'+currentPage).addClass('d-block');

                    $('#KeypageCenter'+nextPage).removeClass('d-none');
                    $('#KeypageCenter'+nextPage).addClass('d-block');

                    $('#KeycloseLi').removeClass('d-none');
                    $('#KeycloseLi').addClass('d-block');
                }


                // about active page-item
                $('.page-item-pages .page-link').each(function(index, value){
                    $('.page-item-pages .page-link').removeClass('active');
                });
                $(this).addClass('active');

            });
        </script>

@endsection


