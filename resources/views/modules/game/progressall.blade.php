@extends('layouts.default')

@section('content')
    <div>
        <strong>Certificates</strong><hr/>
        
        @foreach ($game_certificate as $key => $certificate)
            certificate id: {{ $certificate->certificate }} <br/>
            certificate title: {{ $certificate->title }} <br/>
            certificate description: {{ $certificate->description }} <br/>
            user id: {{ $certificate->user }} <br/>
            user username: {{ $certificate->username }} <br/>
            user surname: {{ $certificate->surname }} <br/>
            user givenname: {{ $certificate->givenname }} <br/>
            completed: {{ $certificate->completed }} <br/>
            completed criteria: {{ $certificate->completed_criteria }} <br/>
            total criteria: {{ $certificate->total_criteria }} <br/>
            percentage: {{ round($certificate->completed_criteria / $certificate->total_criteria * 100, 2) }}% <br/>
            created: {{ $certificate->created }} <br/>
            updated: {{ $certificate->updated }} <br/>
            assigned: {{ $certificate->assigned }} <br/>
            <br/> <br/>
        @endforeach
    </div>
    
    <div>
        <strong>Badges</strong><hr/>
        
        @foreach ($game_badge as $key => $badge)
            badge id: {{ $badge->badge }} <br/>
            badge title: {{ $badge->title }} <br/>
            badge description: {{ $badge->description }} <br/>
            user id: {{ $badge->user }} <br/>
            user username: {{ $badge->username }} <br/>
            user surname: {{ $badge->surname }} <br/>
            user givenname: {{ $badge->givenname }} <br/>
            completed : {{ $badge->completed }} <br/>
            completed criteria: {{ $badge->completed_criteria }} <br/>
            total criteria: {{ $badge->total_criteria }} <br/>
            percentage: {{ round($badge->completed_criteria / $badge->total_criteria * 100, 2) }}% <br/>
            created: {{ $badge->created }} <br/>
            updated: {{ $badge->updated }} <br/>
            assigned: {{ $badge->assigned }} <br/>
            <br/> <br/>
        @endforeach
    </div>
@endsection