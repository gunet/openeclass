<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';

$toolName = $langAutoEnroll;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);


if (isset($_REQUEST['add'])) {
    $data['type'] = $type = intval($_REQUEST['add']);
    if (!in_array($type, array(USER_STUDENT, USER_TEACHER))) {
        forbidden();
    }
}

if (isset($_GET['delete'])) {
    if (!($rule = getDirectReference($_GET['delete']))) {
        forbidden();
    }
    if (Database::get()->query('DELETE FROM autoenroll_rule WHERE id = ?d', $rule)->affectedRows) {
        Session::Messages($langAutoEnrollDeleted);
    }
    redirect_to_home_page('modules/admin/autoenroll.php');
} elseif (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (isset($_POST['id'])) {
        if (!($rule = getDirectReference($_POST['id']))) {
            forbidden();
        }
        Database::get()->query('DELETE FROM autoenroll_rule_department WHERE rule = ?d', $rule);
        Database::get()->query('DELETE FROM autoenroll_department WHERE rule = ?d', $rule);
        Database::get()->query('DELETE FROM autoenroll_course WHERE rule = ?d', $rule);
    }

    if (isset($_POST['courses']) or isset($_POST['rule_deps'])) {
        if (!isset($rule)) {
            $rule = Database::get()->query('INSERT INTO autoenroll_rule
                SET status = ?d', $type)->lastInsertID;
        }
        if (isset($_POST['department'])) {
            multiInsert('autoenroll_rule_department',
                'rule, department', $rule, $_POST['department']);
        }
        if (isset($_POST['courses']) and !empty($_POST['courses'])) {
            $courses = explode(',', $_POST['courses']);
            multiInsert('autoenroll_course',
                'rule, course_id', $rule, $courses);
        }
        if (isset($_POST['rule_deps'])) {
            multiInsert('autoenroll_department',
                'rule, department_id', $rule, $_POST['rule_deps']);
        }
    }
    Session::Messages($langAutoEnrollAdded, 'alert-success');
    if (isset($_POST['apply'])) {
        apply_autoenroll($rule);
        Session::Messages($langRuleApplied, 'alert-info');
    }
    redirect_to_home_page('modules/admin/autoenroll.php');
} elseif (isset($_GET['add']) or isset($_GET['edit'])) {
    load_js('jstree3');
    load_js('select2');

    $data['deps'] = $department = array();
    $courses = '';
    if (isset($_GET['edit'])) {
        if (!($rule = getDirectReference($_GET['edit']))) {
            forbidden();
        }

        $q = Database::get()->querySingle('SELECT * FROM autoenroll_rule WHERE id = ?d', $rule);
        $data['type'] = $type = $q->status;

        $department = array_map(function ($item) { return $item->department; },
            Database::get()->queryArray(
                'SELECT department FROM autoenroll_rule_department WHERE rule = ?d', $rule));
     

        $courses = implode(',',
            array_map(function ($course) { 
                return "{id: '" . getIndirectReference($course->course_id) . "', text: '" .
                    js_escape($course->title . ' (' . $course->public_code . ')') .
                    "'}";
                },
                Database::get()->queryArray(
                    'SELECT course_id, title, public_code FROM autoenroll_course, course
                         WHERE autoenroll_course.course_id = course.id AND
                               rule = ?d', $rule)));
        

        $data['deps'] = array_map(function ($dep) { return getIndirectReference($dep->department_id); },
            Database::get()->queryArray('SELECT department_id
                FROM autoenroll_department
                WHERE rule = ?d', $rule));
    }

    $data['tree'] = $tree = new Hierarchy();
    list($jsTree, $data['htmlTree']) = $tree->buildUserNodePickerIndirect(array('defaults' => $department, 'multiple' => true));

    // The following code is modified from Hierarchy::buildJSNodePicker()
    $options = array('defaults' => $data['deps'], 'where' => 'AND node.allow_course = true');
    $joptions = json_encode($options);


    $head_content .= $jsTree . "
      <script>
        $(function () {
          $('#courses').select2({
            minimumInputLength: 2,
            tags: true,
            tokenSeparators: [', '],
            ajax: {
              url: 'coursefeed.php',
              dataType: 'json',
              data: function(term, page) {
                return { q: term };
              },
              results: function(data, page) {
                return { results: data };
              }
            }
          }).select2('data', [$courses]);

          $('#ndAdd2').click(function() {
            $('#treeCourseModal').modal('show');
          });

          $('#nodCnt2').on('click', \"a[href='#nodCnt2']\", function (e) {
            e.preventDefault();
            $(this).find('span').tooltip('destroy')
              .closest('p').remove();
            $('#dialog-set-key').val(null);
            $('#dialog-set-value').val(null);
          });

          $('.treeCourseModalClose').click(function() {
            $('#treeCourseModal').modal('hide');
          });

          $('#treeCourseModalSelect').click(function() {
            var newnode = $( '#js-tree-course' ).jstree('get_selected', true)[0];
            if (newnode !== undefined) {
                var newnodeid = newnode.id;
                var newnodename = newnode.text;
            }

            jQuery.getJSON('{$urlAppend}modules/hierarchy/nodefullpath.php', {nodeid : newnodeid})
              .done(function(data) {
                if (data.nodefullpath !== undefined && data.nodefullpath.length > 0) {
                  newnodename = data.nodefullpath;
                }
            })
            .always(function(dataORjqXHR, textStatus, jqXHRORerrorThrown) {
                if (newnode === undefined) {
                    alert('" . js_escape($langEmptyNodeSelect) . "');
                } else {
                    countnd += 1;
                    $('#nodCnt2').append('<p id=\"nd_' + countnd + '\">'
                                     + '<input type=\"hidden\" name=\"rule_deps[]\" value=\"' + newnodeid + '\">'
                                     + newnodename
                                     + '&nbsp;<a href=\"#nodCnt2\"><span class=\"fa fa-times\" data-toggle=\"tooltip\" data-original-title=\"" . js_escape($langNodeDel) . "\" data-placement=\"top\" title=\"$langNodeDel\"><\/span><\/a>'
                                     + '<\/p>');

                    $('#dialog-set-value').val(newnodename);
                    $('#dialog-set-key').val(newnodeid);
                    document.getElementById('dialog-set-key').onchange();
                    $('#treeCourseModal').modal('hide');
                }
            });
          });

          $( '#js-tree-course' ).jstree({
              'plugins' : ['sort'],
              'core' : {
                  'data' : {
                      'url' : '{$urlAppend}modules/hierarchy/nodes.php',
                      'type' : 'POST',
                      'data' : function(node) {
                          return { 'id' : node.id, 'options' : $joptions };
                      }
                  },
                  'multiple' : false,
                  'themes' : {
                      'name' : 'proton',
                      'dots' : true,
                      'icons' : false
                  }
              },
              'sort' : function (a, b) {
                  priorityA = this.get_node(a).li_attr.tabindex;
                  priorityB = this.get_node(b).li_attr.tabindex;

                  if (priorityA == priorityB) {
                      return (this.get_text(a) > this.get_text(b)) ? 1 : -1;
                  } else {
                      return (priorityA < priorityB) ? 1 : -1;
                  }
              }
          }); 

        });
      </script>";
                      
    $pageName = isset($_GET['add']) ? $langAutoEnrollNew : $langEditChange;
    $navigation[] = array('url' => 'autoenroll.php', 'name' => $langAutoEnroll);
    $data['action_bar'] = action_bar([
            [
                'title' => $langBack,
                'url' => 'autoenroll.php',
                'icon' => 'fa-reply',
                'level' => 'primary-label'
            ]
        ]);
    
    $view = 'admin.users.autoenroll.create';
} else {

    $data['action_bar'] = action_bar(array(
        array('title' => "$langAutoEnrollNew ($langStudents)",
              'url' => 'autoenroll.php?add=' . USER_STUDENT,
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => "$langAutoEnrollNew ($langTeachers)",
              'url' => 'autoenroll.php?add=' . USER_TEACHER,
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
        array('title' => $langBack,
              'url' => 'index.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
    
    $data['rules'] = false;
    $i = 0;
    Database::get()->queryFunc('SELECT * FROM autoenroll_rule',
        function ($item) use (&$data, &$i) {
            $data['rules'][$i] = (array) $item;

            $data['rules'][$i]['deps'] = Database::get()->queryArray('SELECT hierarchy.id, name
                FROM autoenroll_rule_department, hierarchy
                WHERE autoenroll_rule_department.department = hierarchy.id AND
                      rule = ?d', $item->id);


            $data['rules'][$i]['courses'] = Database::get()->queryArray('SELECT code, title, public_code
                FROM autoenroll_course, course
                WHERE autoenroll_course.course_id = course.id AND
                      rule = ?d', $item->id);


            $data['rules'][$i]['auto_enroll_deps'] = Database::get()->queryArray('SELECT hierarchy.id, name
                FROM autoenroll_department, hierarchy
                WHERE autoenroll_department.department_id = hierarchy.id AND
                      rule = ?d', $item->id);
            $i++;
        });
    $view = 'admin.users.autoenroll.index';
        
}

$data['menuTypeID'] = 3;
view($view, $data);


function multiInsert($table, $signature, $key, $values) {
    $terms = array();
    $count = 0;
    foreach ($values as $value) {
        $count++;
        $terms[] = $key;
        $terms[] = getDirectReference($value);
    }
    Database::get()->query("INSERT INTO `$table` ($signature) VALUES " .
            implode(', ', array_fill(0, $count, '(?d, ?d)')),
        $terms);
}

function apply_autoenroll($rule) {
    $status = Database::get()->querySingle('SELECT status
        FROM autoenroll_rule WHERE id = ?d', $rule)->status;
    $deps = Database::get()->queryArray('SELECT department
        FROM autoenroll_rule_department WHERE rule = ?d',
        $rule);
    if (!$deps) {
        Database::get()->query('INSERT IGNORE INTO course_user
            (course_id, user_id, status, reg_date, document_timestamp)
            (SELECT course_id, user.id, ?d, NOW(), NOW()
                FROM autoenroll_course, user
                WHERE rule = ?d AND status = ?d)',
            USER_STUDENT, $rule, $status);
        Database::get()->query("INSERT IGNORE INTO course_user
            (course_id, user_id, status, reg_date, document_timestamp)
            (SELECT course_id, user.id, ?d, NOW(), NOW()
                FROM autoenroll_department, course_department, user
                WHERE department_id = course_department.department AND
                      rule = ?d AND status = ?d)",
            USER_STUDENT, $depsParam, $rule, $status);
    } else {
        $depsSQL = implode(', ', array_fill(0, count($deps), '?d'));
        $depsParam = array_map(function ($d) { return $d->department; }, $deps);
        Database::get()->query("INSERT IGNORE INTO course_user
            (course_id, user_id, status, reg_date, document_timestamp)
            (SELECT course_id, user.id, ?d, NOW(), NOW()
                FROM autoenroll_course, user, user_department
                WHERE user.id = user_department.user AND
                      user_department.department IN ($depsSQL) AND
                      rule = ?d AND status = ?d)",
            USER_STUDENT, $depsParam, $rule, $status);
        Database::get()->query("INSERT IGNORE INTO course_user
            (course_id, user_id, status, reg_date, document_timestamp)
            (SELECT course, user.id, ?d, NOW(), NOW()
                FROM autoenroll_department, course_department, user, user_department
                WHERE user.id = user_department.user AND
                      department_id = course_department.department AND
                      user_department.department IN ($depsSQL) AND
                      rule = ?d AND status = ?d)",
            USER_STUDENT, $depsParam, $rule, $status);
    } 
}
