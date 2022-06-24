                            @foreach ($category->links as $key => $link)                                
                                <tr>    
                                    <td class='nocategory-link'>
                                        <a href='{!! $link->url !!}' {!! $is_in_tinymce ? " class='fileURL' " : '' !!} target='_blank'>
                                            {{ $link->title ?: $link->url }}
                                            <i class='fa fa-external-link' style='color:#444'></i>
                                        </a>
                                        @if ($category->id == -2 && $link->user_id != 0)
                                            <small> - {{ trans('langLinkSubmittedBy') }} {!! display_user($link->user_id, false, false) !!}</small>
                                        @endif
                                        @if (!empty($link->description))
                                            <br>
                                            {!! standard_text_escape($link->description) !!}
                                        @endif   
                                        @if ($category->id == -2)
                                            {!! (new Rating('thumbs_up', 'link', $link->id))->put($is_editor, $uid, $course_id) !!}
                                        @endif                                    
                                    </td>
                                    @if (!$is_in_tinymce)
                                        @if ($is_editor)   
                                            <td class='option-btn-cell'>
                                                {!! action_button(array(
                                                    array('title' => trans('langEditChange'),
                                                          'icon' => 'fa-edit',
                                                          'url' => "index.php?course=$course_code&amp;action=editlink&amp;id=". getIndirectReference($link->id) ."&amp;urlview=$urlview$socialview_param"),
                                                    array('title' => trans('langUp'),
                                                          'level' => 'primary',
                                                          'icon' => 'fa-arrow-up',
                                                          'disabled' => $key == 0,
                                                          'url' => "index.php?course=$course_code&amp;urlview=". $urlview ."&amp;up=". getIndirectReference($link->id) . $socialview_param,
                                                          ),
                                                    array('title' => trans('langDown'),
                                                          'level' => 'primary',
                                                          'icon' => 'fa-arrow-down',
                                                          'disabled' =>  $key >= count($category->links)-1,
                                                          'url' => "index.php?course=$course_code&amp;urlview=". $urlview ."&amp;down=". getIndirectReference($link->id) . $socialview_param,
                                                          ),
                                                    array('title' => trans('langDelete'),
                                                          'icon' => 'fa-times',
                                                          'class' => 'delete',
                                                          'url' => "index.php?course=$course_code&amp;action=deletelink&amp;id=". getIndirectReference($link->id) ."&amp;urlview=$urlview$socialview_param",
                                                          'confirm' => trans('langLinkDelconfirm'))
                                                )) !!}
                                            </td>
                                        @elseif ($category->id == -2)
                                            @if (isset($uid))
                                                @if (is_link_creator($link->id))
                                                    <td class='option-btn-cell'>
                                                    {!! action_button(array(
                                                            array('title' => trans('langEditChange'),
                                                                    'icon' => 'fa-edit',
                                                                    'url' => "index.php?course=". $course_code ."&amp;action=editlink&amp;id=" . getIndirectReference($link->id) . "&amp;urlview=".$urlview.$socialview_param),
                                                            array('title' => trans('langDelete'),
                                                                    'icon' => 'fa-times',
                                                                    'class' => 'delete',
                                                                    'url' => "index.php?course=". $course_code ."&amp;action=deletelink&amp;id=" . getIndirectReference($link->id) . "&amp;urlview=".$urlview.$socialview_param,
                                                                    'confirm' => trans('langLinkDelconfirm'))
                                                    )) !!}
                                                    </td>
                                                @else                                              
                                                    @if (abuse_report_show_flag('link', $link->id , $course_id, $is_editor))                                                       
                                                        <?php
                                                        $flag_arr = abuse_report_action_button_flag('link', $link->id, $course_id);
                                                        ?>
                                                        <td class='option-btn-cell'>
                                                            {!! action_button(array($flag_arr[0])).$flag_arr[1] !!}
                                                        </td>
                                                    @else
                                                        <td>&nbsp;</td>
                                                    @endif
                                                @endif
                                            @endif                                    
                                        @endif
                                    @endif
                                </tr>
                            @endforeach