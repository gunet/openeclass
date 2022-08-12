@foreach ($items as $result)
    <tr class='{{ $result->row_class }}'>
        <td class='nocategory-link'>{!! $result->link_href !!}{!! $result->extradescription !!}</td>
        <td class='text-center'>{{ nice_format(date('Y-m-d', strtotime($result->myrow->date))) }}</td>
    </tr>
@endforeach