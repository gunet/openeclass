@foreach ($category as $data)
    <tr>
       <?php
            $title = empty($data->title) ? $data->url : $data->title;
        ?>
       <td class='nocategory-link'>
           <a href='{{ $data->url }}' target='_blank'> {{ $title }}
               <i class='fa fa-external-link' style='color:#444'></i>
           </a>
       @if (!empty($data->description))
           <br> {!! standard_text_escape($data->description) !!}
       @endif
       </td>
    </tr>
@endforeach