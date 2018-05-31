@extends('layouts.default')

@section('content')

    <div id="claroBody">
        <form id="quiz">
            <table class="table-default">
                <tr>
                    <td>

                    <?php $questionCount = 0; ?>
                    @foreach ($questions as $question)
                        <?php
                            $questionCount++;
                        ?>
                        <table class="table-default">
                            <tr>
                                <th valign="top" colspan="2">{{ trans('langQuestion') }}&nbsp;{{ $questionCount }}</th>
                            </tr>
                            <tfoot>
                                <tr>
                                    <td valign="top" colspan="2">{{ $question->selectTitle() }}</td>
                                </tr>
                                <tr>
                                    <td valign="top" colspan="2"><i>{!! parse_user_text($question->selectDescription()) !!}</i></td>
                                </tr>

                                {{-- answers --}}


                            </tfoot>
                        </table>
                    @endforeach

                    </td>
                </tr>
                <tr>
                    <td align="center"><br><input class="btn btn-primary" type="button" value="{{ trans('langOk') }}" onClick="calcScore()"></td>
                </tr>
            </table>
        </form>
    </div>

@endsection


