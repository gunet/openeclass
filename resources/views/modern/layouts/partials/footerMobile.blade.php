<footer id="bgr-cheat-footer" class="w-100 ms-0 mt-auto site-footerMobile"> 

    <div class='col-12 d-flex justify-content-center p-2'>

        <div class="btn-group w-100" role="group" aria-label="Basic example">
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/about.php"><span class='fa fa-credit-card fa-fw text-white'></span></a>
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/contact.php"><span class='fa fa-phone fa-fw fa-fw text-white'></span></a>
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/faq.php"><span class='fa fa-question-circle fa-fw text-white'></span></a>
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/manual.php"><span class='fa fa-file-video-o fa-fw text-white'></span></a>
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/terms.php"><span class='fa fa-gavel text-white'></span></a>
            @if (get_config('activate_privacy_policy_text'))
            <a class="btn btn-transparent text-white" href="{{$urlAppend}}info/privacy_policy.php"><span class='fas fa-shield-alt text-white'></span></a>
            @endif
        </div>
        
    </div>
</footer>