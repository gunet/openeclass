           
             
            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
               <div class='alert alert-info'>
                    {!! trans('langExplainShib', ['<em>' .$secureIndexPath. '</em>']) !!}
                </div>  
            </div>       
            <div class='form-group mt-3'>
                <label for='dbfieldpass' class='col-sm-12 control-label-notes'>{{ trans('langShibEmail') }}:</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='shib_email' id='shib_email' type='text' value='{{ $shib_vars['email'] }}'>
                </div>
            </div>

          

            <div class='form-group mt-3'>
                <label for='shibuname' class='col-sm-12 control-label-notes'>{{ trans('langShibUsername') }}:</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='shib_uname' nid='shib_uname' type='text' value='{{ $shib_vars['uname'] }}'>
                </div>
            </div>

          

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibCn') }}:</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='shib_cn' id='shib_cn' type='text' value='{{ $shib_vars['cn'] }}'>
                </div>
            </div>

            

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibSurname') }}:</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='shib_surname' id='shib_surname' type='text' value='{{ $shib_vars['surname'] }}'>
                </div>
            </div>

           

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibGivenname') }}:</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='shib_givenname' id='shib_givenname' type='text' value='{{ $shib_vars['givenname'] }}'>
                </div>
            </div>

     

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibStudentId') }}:</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='shib_studentid' id='shib_studentid' type='text' value='{{ $shib_vars['studentid'] }}'>
                </div>
            </div>


            
            <div class='form-group form-inline mt-3'>
               <div class='col-sm-10 col-sm-offset-2'>
                   <div class='checkbox'>
                     <label>
                          <input type='checkbox' name='checkseparator' value='on' {!! $checkedshib !!}>&nbsp;{{ trans('langCharSeparator') }}&nbsp;
                          <input class='form-control' name='shibseparator' type='text' size='1' maxlength='2' value='{{ $shibseparator }}' />
                     </label>
                   </div>
               </div>
            </div>