           
             
            <div class='col-12'>
               <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                    {!! trans('langExplainShib', ['<em>' .$secureIndexPath. '</em>']) !!}</span>
                </div>  
            </div>       
            <div class='form-group mt-3'>
                <label for='dbfieldpass' class='col-sm-12 control-label-notes'>{{ trans('langShibEmail') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langShibEmail') }}..." name='shib_email' id='shib_email' type='text' value='{{ $shib_vars['email'] }}'>
                </div>
            </div>

          

            <div class='form-group mt-3'>
                <label for='shibuname' class='col-sm-12 control-label-notes'>{{ trans('langShibUsername') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langShibUsername') }}..." name='shib_uname' nid='shib_uname' type='text' value='{{ $shib_vars['uname'] }}'>
                </div>
            </div>

          

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibCn') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langShibCn') }}..." name='shib_cn' id='shib_cn' type='text' value='{{ $shib_vars['cn'] }}'>
                </div>
            </div>

            

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibSurname') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langShibSurname') }}..." name='shib_surname' id='shib_surname' type='text' value='{{ $shib_vars['surname'] }}'>
                </div>
            </div>

           

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibGivenname') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langShibGivenname') }}..." name='shib_givenname' id='shib_givenname' type='text' value='{{ $shib_vars['givenname'] }}'>
                </div>
            </div>

     

            <div class='form-group mt-3'>
                <label for='shibcn' class='col-sm-12 control-label-notes'>{{ trans('langShibStudentId') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langShibStudentId') }}..." name='shib_studentid' id='shib_studentid' type='text' value='{{ $shib_vars['studentid'] }}'>
                </div>
            </div>


            
            <div class='form-group mt-3'>
               <div class='col-12'>
                   <div class='checkbox'>
                    <label class='label-container mb-3' aria-label="{{ trans('langSelect') }}">
                          <input type='checkbox' name='checkseparator' value='on' {!! $checkedshib !!}>
                          <span class='checkmark'></span>{{ trans('langCharSeparator') }}
                     </label>
                   </div> 
                   <input class='form-control' placeholder="{{ trans('langCharSeparator') }}..." name='shibseparator' type='text' size='1' maxlength='2' value='{{ $shibseparator }}' />
               </div>
            </div>