<style>
    .table-responsive td { word-break: break-word; }
</style>

<div class='margin-bottom-thin mt-3'>
    <strong>{{ trans('langPeerSubmissions') }}:</strong>&nbsp; {{ $reviews_per_assignment }}
</div>
<div class='table-responsive'>
    <table class='table-default'>
        <tbody>
            <tr class='list-header'>
                <th style='width: 5px;'>&nbsp;</th>
                @if ($submission_type == 1)
                    <th>{{ trans('langWorkOnlineText') }}</th>
                @else
                    <th>{{ trans('langFileName') }}</th>
                @endif
                <th style='width: 10px;'>
                    {{ trans('langGradebookGrade') }}
                </th>
                <th style='width: 20px;'>
                    <i class='fa-solid fa-gears'></i>
                </th>
            </tr>
            {!! $html_content !!}
        </tbody>
    </table>
</div>


