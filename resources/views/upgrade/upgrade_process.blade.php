@extends('layouts.default')

@push('head_styles')
    <style>
        #upgrade-container { padding: 1em; overflow-y: scroll; width: 100%;}
        .upgrade-header { font-weight: bold; border-bottom: 1px solid black; }
    </style>
@endpush

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>

            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                @include('layouts.partials.legend_view')

                <div class='row row-cols-lg-2 row-cols-1 g-4 mt-4 mb-3'>
                    <div class='col-md-7 col-lg-8'>
                        <div class='alert alert-info text-center'>
                            {{ trans('langUpgradeBase') }}<br>
                            <em>{{ trans('langPreviousVersion') }} {{ $previous_version }} </em>
                        </div>
                        <div class='text-center'>
                            <button class='btn btn-success' id='submit_upgrade'>
                                <span class='fa fa-refresh space-after-icon'></span> {{ trans('langUpgrade') }}
                            </button>
                        </div>

                        <div class='col-sm-12' id='upgrade-container'></div>
                    </div>

                    @include('upgrade.upgrade_menu', [ 'upgrade_menu' => upgrade_menu() ] )
                </div>

                <script>
                    $(document).ready(function() {
                        $('#submit_upgrade').click(function (e) {
                            var upgradeContainer = $('#upgrade-container');
                            e.preventDefault();
                            $('#submit_upgrade').prop('disabled', true);
                            $('#submit_upgrade').find('.fa').addClass('fa-spin');
                            upgradeContainer.html('<div class=\"text-center upgrade-header\">{{ trans('langUpgradeStart') }}</div>');
                            var maxHeight = $('#background-cheat').height() - upgradeContainer.position().top;
                            upgradeContainer.height(maxHeight - 100);
                            var feedback = function () {
                                $.post('upgrade.php', {
                                    token: '{!! $_SESSION['csrf_token'] !!}'
                                }, function (data) {
                                    if (!data) {
                                        setTimeout(feedback, 100);
                                    } else {
                                        if (data.error) {
                                            data.message += '<br><em>' + data.error + '</em>';
                                        }
                                        if (data.status == 'ok' || data.status == 'wait') {
                                            if (data.message) {
                                                upgradeContainer.append('<p>' + data.message + '</p>');
                                            }
                                            setTimeout(feedback, (data.status == 'ok')? 100: 1000);
                                        } else if (data.status == 'error') {
                                            upgradeContainer.append('<div class=\"alert alert-danger\">' + data.message + '</div>');
                                            $('#submit_upgrade').find('.fa').removeClass('fa-spin');
                                        } else if (data.status == 'done') {
                                            upgradeContainer.append('<div class=\"alert alert-success\">{{ trans('langUpgradeSuccess') }}<br>{{ trans('langUpgReady') }}</div>');
                                            upgradeContainer.append('<p>{{ trans('langLogOutput') }}: <a href=\"{{ $urlAppend }}courses/{{ $logfile }}\">{{ $logfile }}</a></p>');
                                            $('#submit_upgrade').find('.fa').removeClass('fa-spin');
                                        }
                                    }
                                    upgradeContainer.scrollTop(upgradeContainer.prop('scrollHeight'));
                                }, 'json');
                            };
                            feedback();
                        });
                    });
                </script>
            </div>
        </div>
    </div>
@endsection
