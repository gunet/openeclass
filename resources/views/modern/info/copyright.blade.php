@extends('layouts.default')

@section('content')
<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                
                <div class="row">

                    <div class="col-12">
                        <div class='text-center'>
                            <span class='courseInfoText TextExtraBold blackBlueText'>Copyright</span>
                        </div>
                    
                        <div class='border-cols-default mt-5 Borders bg-white p-lg-5 p-3'>{!! trans('langCopyrightNotice') !!}</div>
                    </div>
                </div>
            </div>
        </div>
</div>

@endsection
