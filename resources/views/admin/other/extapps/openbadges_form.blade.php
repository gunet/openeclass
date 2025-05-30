@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert')

                    <div class="d-lg-flex gap-4 mt-4">
                        <div class="flex-grow-1">
                            <div class="form-wrapper form-edit border-0 px-0">
                                <form class="form-horizontal" role="form" name="backpackProviderForm" action="{{ $formAction }}" method="{{ $method }}">
                                    @if($provider && $provider->id)
                                        <input type="hidden" name="provider_id" value="{{ $provider->id }}">
                                    @endif
                                    
                                    <fieldset>
                                        <legend class="mb-0" aria-label="{{ trans('langForm') }}"></legend>
                                        
                                        <div class="form-group">
                                            <label for="provider_name" class="col-sm-12 control-label-notes">
                                                {{ trans('langBackpackProvider') }} 
                                                <span class="asterisk Accent-200-cl">(*)</span>
                                            </label>
                                            <div class="col-sm-12">
                                                <input 
                                                    class="form-control" 
                                                    type="text" 
                                                    name="provider_name" 
                                                    id="provider_name" 
                                                    value="{{ $provider ? q($provider->name) : '' }}" 
                                                    required
                                                >
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mt-4">
                                            <label for="api_url" class="col-sm-12 control-label-notes">
                                                {{ trans('langBackpackProviderUrl') }} 
                                                <span class="asterisk Accent-200-cl">(*)</span>
                                            </label>
                                            <div class="col-sm-12">
                                                <input 
                                                    class="form-control" 
                                                    type="url" 
                                                    name="api_url" 
                                                    id="api_url" 
                                                    value="{{ $provider ? q($provider->api_url) : '' }}" 
                                                    required
                                                >
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mt-4">
                                            <label for="version" class="col-sm-12 control-label-notes">
                                                {{ trans('langOpenBadgeVersion') }} <span class="asterisk Accent-200-cl">(*)</span>
                                            </label>
                                            <div class="col-sm-12">
                                                <select class="form-select" name="version" id="version" required>
                                                    <option value="OpenBadge v2.0" 
                                                        @if($provider && $provider->ob_version === 'OpenBadge v2.0') selected @endif>
                                                        OpenBadge v2.0
                                                    </option>
                                                    <option value="OpenBadge v2.1" 
                                                        @if($provider && $provider->ob_version === 'OpenBadge v2.1') selected @endif>
                                                        OpenBadge v2.1
                                                    </option>
                                                    <option value="OpenBadge v3" 
                                                        @if($provider && $provider->ob_version === 'OpenBadge v3') selected @endif>
                                                        OpenBadge v3
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        @if($provider)
                                            <div class="form-group mt-4">
                                                <label for="active" class="col-sm-12 control-label-notes">
                                                    {{ trans('langActive') }}
                                                </label>
                                                <div class="col-sm-12">
                                                    <div class="form-check">
                                                        <input 
                                                            class="form-check-input" 
                                                            type="checkbox" 
                                                            name="active" 
                                                            id="active" 
                                                            value="1"
                                                            @if($provider->isEnabled()) checked @endif
                                                        >
                                                        <label class="form-check-label" for="active">
                                                            {{ trans('langEnableProvider') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <div class="form-group mt-5">
                                            <div class="col-12 d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                                <input class="btn submitAdminBtn" type="submit" value="{{ $submitLabel }}">
                                                @if($action === 'edit')
                                                    <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn cancelAdminBtn">{{ trans('langCancel') }}</a>
                                                @endif
                                            </div>
                                        </div>
                                    </fieldset>
                                    
                                    {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                    </div>

        </div>
</div>
</div>

<script type="text/javascript">
//<![CDATA[
    let chkValidator = new Validator("backpackProviderForm");
    chkValidator.addValidation("provider_name", "req", "{{ trans('langProviderNameRequired') }}");
    chkValidator.addValidation("api_url", "req", "{{ trans('langApiUrlRequired') }}");
    chkValidator.addValidation("version", "req", "{{ trans('langVersionRequired') }}");
//]]>
</script>

@endsection 