
@foreach ($items as $result)
    <tr class='{{ $result->row_class }}'>
        <td class='nocategory-link' style='line-height: 16px;'>{!! $result->link_href !!}{!! $result->extradescription !!}</td>
        <td style='width:15%;'>{{ format_locale_date(strtotime($result->myrow->date), 'short', false) }}</td>
        @if (!$is_in_tinymce)
            @if ($display_tools)
            <td class='option-btn-cell text-end' style='width:10%;'>
                {!!
                action_button(array(
                    array('title' => $GLOBALS['langEditChange'],
                        'url' => $urlAppend . "modules/video/edit.php"
                            . "?course=" . $course_code
                            . "&amp;id=" . $result->myrow->id
                            . "&amp;table_edit=" . $result->table,
                        'icon' => 'fa-edit',
                        'show' => (!$is_in_tinymce && $is_editor)),

                    array('title' => $result->myrow->visible ? $GLOBALS['langViewHide'] : $GLOBALS['langViewShow'],
                        'url' => $urlAppend . "modules/video/index.php"
                            . "?course=" . $course_code
                            . "&amp;vid=" . $result->myrow->id
                            . "&amp;table=" . $result->table
                            . "&amp;vis=" . ($result->myrow->visible ? '0' : '1'),
                        'icon' => $result->myrow->visible ? 'fa-eye-slash' : 'fa-eye'),

                    array('title' => $result->myrow->public ? $GLOBALS['langResourceAccessLock'] : $GLOBALS['langResourceAccessUnlock'],
                        'url' => $urlAppend . "modules/video/index.php"
                            . "?course=" . $course_code
                            . "&amp;vid=" . $result->myrow->id
                            . "&amp;table=" . $result->table
                            . "&amp;" . ($result->myrow->public ? 'limited=1' : 'public=1'),
                        'icon' => $result->myrow->public ? 'fa-lock' : 'fa-unlock',
                        'show' => (!$is_in_tinymce && $is_editor && course_status($course_id) == COURSE_OPEN)),

                    array('title' => $GLOBALS['langDownload'],
                        'url' => $result->link_to_save,
                        'icon' => 'fa-download'),

                    array('title' => $GLOBALS['langDelete'],
                        'url' => $urlAppend . "modules/video/index.php"
                            . "?course=" . $course_code
                            . "&amp;id=" . $result->myrow->id
                            . "&amp;delete=yes&amp;table=" . $result->table,
                        'icon' => 'fa-xmark',
                        'confirm' => $GLOBALS['langConfirmDelete'],
                        'class' => 'delete')))
                !!}
            </td>
            @else
            <td class='text-end'>
                {!! ($result->table == 'video') ? icon('fa-download', $GLOBALS['langDownload'], $result->link_to_save) : '&nbsp;' !!}
            </td>
            @endif
        @endif
    </tr>
@endforeach
