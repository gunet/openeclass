@extends('layouts.default')

@section('content')
{!! $action_bar !!}
<div class='row'>
    <div class='col-md-12'>
        <div class='form-wrapper'>
        	<form class='form-horizontal' role='form' action='save.php' method='post' enctype='multipart/form-data'>
        		<label for='userFile' class='col-sm-2 control-label'>Αρχείο : </label>
                                <div class='col-sm-10'>
                                    <input type='file' id='userFile' name='userFile'>
                                </div>
        		<button class='btn btn-primary' type='submit'>Εισαγωγή</button>
        	</form>
        </div>
    </div>
</div>
@endsection
