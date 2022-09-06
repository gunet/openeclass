@foreach ($items as $result)
    <tr class='{{ $result->row_class }}'>
        <td class='nocategory-link'>{!! $result->link_href !!}{!! $result->extradescription !!}</td>
        <td class='text-center'>{{ format_locale_date(strtotime($result->myrow->date), 'short', false) }}</td>
    </tr>
@endforeach
