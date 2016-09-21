@extends('layouts.default')

@section('content')
	@if ($is_editor)
		<div class='row'>
			<div class='col-md-12'>
				{!! action_bar(array(
					array('title' => trans('langEditUnitSection'),
						  'url' => $editUrl,
						  'icon' => 'fa fa-edit',
						  'level' => 'primary-label',
						  'button-class' => 'btn-success'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertExercise'),
						  'url' => $insertBaseUrl . 'exercise',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
						  'url' => $insertBaseUrl . 'doc',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertText'),
						  'url' => $insertBaseUrl . 'text',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertLink'),
						  'url' => $insertBaseUrl . 'link',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langLearningPath1'),
						  'url' => $insertBaseUrl . 'lp',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertVideo'),
						  'url' => $insertBaseUrl . 'video',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertForum'),
						  'url' => $insertBaseUrl . 'forum',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertEBook'),
						  'url' => $insertBaseUrl . 'ebook',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertWork'),
						  'url' => $insertBaseUrl . 'work',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertPoll'),
						  'url' => $insertBaseUrl . 'poll',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					array('title' => trans('langAdd') . ' ' . trans('langInsertWiki'),
						  'url' => $insertBaseUrl . 'wiki',
						  'icon' => 'fa fa-paste',
						  'level' => 'secondary'),
					)) !!}
			</div>
		</div>
	@endif

	@if ($previousLink or $nextLink) {
		<div class='row'>
			<div class='col-md-12'>
				<div class='panel panel-default'>
					<div class='panel-body'>
						@if ($previousLink)
							<a class='btn btn-default pull-left' title='{{ $previousTitle }}' href='{{ $previousLink}}'>
								<span class='fa fa-arrow-left space-after-icon'></span>{{ ellipsize($previousTitle, 30) }}
							</a>
						@endif
						@if ($nextLink)
							<a class='btn btn-default pull-right' title='{{ $nextTitle }}' href='{{ $nextLink}}'>
								{{ ellipsize($nextTitle, 30) }}<span class='fa fa-arrow-right space-before-icon'></span>
							</a>
						@endif
					</div>
				</div>
			</div>
		</div>
	@endif

    <div class='row margin-bottom'>
		<div class='col-md-12'>

		</div>
    </div>

	<div class='row'>
		<div class='col-md-12'>
			<div class='panel panel-default'>
				<div class='panel-heading'>
					<div class='panel-title h3'>{{ $pageName }}</div>
				</div>
				<div class='panel-body'>
					{!! $comments !!}
					@if ($tags)
						<div>
							{{ trans('langTags') }}: {!! $tags !!}
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>

    <div class='row'>
		<div class='col-md-12'>
			<div class='panel padding'>
				{!! show_resources($unitId) !!}
			</div>
		</div>
	</div>

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/' method='get'>
                        <div class='form-group'>
                            <label class='col-sm-8 control-label'>{{ trans('langCourseUnits') }}</label>
                            <div class='col-sm-4'>
                                <label class='sr-only' for='id'>{{ trans('langCourseUnits') }}</label>
                                <select name='id' id='id' class='form-control' onchange='document.unitselect.submit()'>
                                    @foreach ($units as $unit)
                                        <option value='{{ $unit->id }}' {{ $unit->id == $unitId ? 'selected' : '' }}>
                                            {{ ellipsize($unit->title, 50) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

