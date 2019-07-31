@extends('layouts.default')

@section('content')
@if($is_editor)
	{!! $action_bar !!}
@endif

@if ($content)
	<table class="table-default">
        <thead>
        	<tr class="list-header">
        		<th class="text-left">H5P</th>
                <th class="text-center" style="width:109px;">
                	<span class="fa fa-gears"></span>
                </th>
            </tr>
        </thead>
        <tbody>
			@foreach ($content as $item)
				<tr>
					<td>
						<a href='view.php?course={{ $course_code }}&amp;id={{ $item->id }}'>{{ $item->title }}</a>
					</td>
					<td class='text-center'>
						@if ($is_editor)
							{!! action_button([
								[ 'icon' => 'fa-times',
								  'title' => trans('langDelete'),
								  'url' => "delete.php?course=$course_code&amp;id=$item->id",
								  'class' => 'delete',
								  'confirm' => trans('langConfirmDelete') ]
								], false) !!}
						@else
							&nbsp;
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	<div class='alert alert-warning'>
		{{ trans('langNoH5PContent') }}
	</div>
@endif

@endsection