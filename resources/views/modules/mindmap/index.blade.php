@extends('layouts.default')

@push('head_styles')
    <link href="{{ $urlAppend }}js/jsmind/jsmind.css" type='text/css' rel='stylesheet'>
    <style>
        #jsmind_container{
            width:100%;
            height:650px;
        }

        jmnodes.theme-greensea jmnode.selected{
            color: black ;
        }

        canvas.jsmind {
            z-index: 1;
        }

        jmnodes {
            z-index: 1;
        }
    </style>
@endpush

@section('content')

    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <aside class='aside-sidebar'>
                @include('layouts.partials.left_menu')
            </aside>
            <main id="main" class="col-12 main-maincontent col_maincontent_active">
                <div class="row">
                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @include('layouts.partials.show_alert')

                    <div id='layout'>
                        <div id='jsmiin-nav-horizontal'>
                            <div class='col-12 d-flex justify-content-end align-items-center mt-3'>
                                <div class='btn-group btn-group-justified gap-2' role='group'>

                                    <div class='btn-group' role='group'>
                                        <button id='Open' type='button' class='btn submitAdminBtn rounded-2' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                            <span class='TextBold hidden-xs pe-2'>{{ trans('langOpenMind') }}</span>
                                            <span class='fa-solid fa-chevron-down fa-lg mt-3'></span>
                                        </button>
                                        <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border' aria-labelledby='Open'>
                                            <ul class='list-group list-group-flush'>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button' onclick='open_json();'>{{ trans('langOpenEx') }}</a></li>
                                                <li><strong>{{ trans('langPleaseChooseFile') }}</strong><input class='py-3' id='file_input' type='file' onchange='open_file();'/></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class='btn-group' role='group'>
                                        <button id='Alter' type='button' class='btn submitAdminBtn rounded-2' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                            <span class='TextBold hidden-xs pe-2'>{{ trans('langEditMind') }}</span>
                                            <span class='fa-solid fa-chevron-down fa-lg mt-3'></span>
                                        </button>
                                        <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border' aria-labelledby='Alter'>
                                            <ul class='list-group list-group-flush'>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button' onclick='toggle_editable(this);'>{{ trans('langEditDis') }}</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button' onclick='add_node();'>{{ trans('langAddNode') }}</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button' onclick='remove_node();'>{{ trans('langRemoveNode') }}</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button' onclick='reset();'>{{ trans('langResetMap') }}</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class='btn-group' role='group'>
                                        <button id='ChooseTheme' type='button' class='btn submitAdminBtn rounded-2' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                            <span class='TextBold hidden-xs pe-2'>{{ trans('langThemes') }}</span>
                                            <span class='fa-solid fa-chevron-down fa-lg mt-3'></span>
                                        </button>
                                        <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border' aria-labelledby='ChooseTheme'>
                                            <ul class='list-group list-group-flush'>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='' role='button' onclick='set_theme(this);'>Default</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='primary' role='button' onclick='set_theme(this);'>Primary</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='warning' role='button' onclick='set_theme(this);'>Warning</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='danger' role='button' onclick='set_theme(this);'>Danger</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='success' role='button' onclick='set_theme(this);'>Success</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='info' role='button' onclick='set_theme(this);'>Info</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='greensea' role='button' onclick='set_theme(this);'>Greensea</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='nephrite' role='button' onclick='set_theme(this);'>Nephrite</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='belizehole' role='button' onclick='set_theme(this);'>Belizehole</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='wisteria' role='button' onclick='set_theme(this);'>Wisteria</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='asphalt' role='button' onclick='set_theme(this);'>Asphalt</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='orange' role='button' onclick='set_theme(this);'>Orange</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='pumpkin' role='button' onclick='set_theme(this);'>Pumpkin</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='pomegranate' role='button' onclick='set_theme(this);'>Pomegranate</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='clouds' role='button' onclick='set_theme(this);'>Clouds</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' data-theme='asbestos' role='button' onclick='set_theme(this);'>Asbestos</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class='btn-group' role='group'>
                                        <button id='Save' type='button' class='btn submitAdminBtn rounded-2' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                                            <span class='TextBold hidden-xs pe-2'>{{ trans('langSave') }}</span>
                                            <span class='fa-solid fa-chevron-down fa-lg mt-3'></span>
                                        </button>
                                        <div class='m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border' aria-labelledby='Save'>
                                            <ul class='list-group list-group-flush'>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button'  onclick='screen_shot();'>{{ trans('langScreenshot') }}</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button'  onclick='save_file();'>{{ trans('langSaveFile') }}</a></li>
                                                <li><a class='list-group-item d-flex justify-content-start align-items-start py-3' href='javascript:void(0)' role='button' onclick='save_file_in_doc();'>{{ trans('langSaveInDoc') }}c</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class='col-12 mt-4'>
                                <div id='jsmind_container'></div>
                            </div>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script type="text/javascript" src="{{ $urlAppend }}js/jsmind/jsmind.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/jsmind/dom-to-image.min.js"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/jsmind/jsmind.screenshot.js"></script>
    <script type="text/javascript">
        var _jm = null;
        new_node=1;
        function open_empty() {
            var options = {
                container: "jsmind_container",
                theme: "greensea",
                editable: true,
                support_html:false
            };
            var mind = {
                "meta":{
                    "name":"jsMind",
                },
                "format":"node_tree",
                "data":{"id":"root","topic":"Κεντρική ιδέα","children":[
                        {"id":"easy","topic":"Κόμβος 1","direction":"left","children":[
                                {"id":"easy1","topic":"Χαρακτηριστικό 1"},
                                {"id":"easy2","topic":"Χαρακτηριστικό 2"},
                                {"id":"easy3","topic":"Χαρακτηριστικό 3"}
                            ]},
                        {"id":"open","topic":"Κόμβος 2","direction":"right","children":[
                                {"id":"open1","topic":"Χαρακτηριστικό 1"},
                                {"id":"open2","topic":"Χαρακτηριστικό 2"}
                            ]}
                    ]}
            }
            _jm = new jsMind(options);

            var rawJson = {!! json_encode($arr) !!};
            var mindData = mind;
            if (rawJson && rawJson.trim() !== "") {
                try {
                    mindData = JSON.parse(rawJson);
                } catch (e) {
                    console.error("Invalid JSON provided for jsMind:", e);
                }
            }
            _jm.show(mindData);
        }

        function open_json(){
            var mind = {
                "meta":{
                    "name":"jsMind",
                },
                "format":"node_tree",
                "data":{"id":"root","topic":"Κεντρική ιδέα","children":[
                        {"id":"easy","topic":"Κόμβος 1","direction":"left","children":[
                                {"id":"easy1","topic":"Χαρακτηριστικό 1"},
                                {"id":"easy2","topic":"Χαρακτηριστικό 2"},
                                {"id":"easy3","topic":"Χαρακτηριστικό 3"},
                                {"id":"easy4","topic":"Χαρακτηριστικό 4"}
                            ]},
                        {"id":"open","topic":"Κόμβος 2","direction":"right","children":[
                                {"id":"open1","topic":"Χαρακτηριστικό 1"},
                                {"id":"open2","topic":"Χαρακτηριστικό 2"}
                            ]},
                        {"id":"other","topic":"Δοκιμαστικός κόμβος","direction":"left","children":[
                                {"id":"other1","topic":"Δοκιμή 1"},
                                {"id":"other2","topic":"Δοκιμή 2"}
                            ]}
                    ]}
            }
            _jm.show(mind);
        }

        function toggle_editable(btn){
            var editable = _jm.get_editable();
            if(editable){
                _jm.disable_edit();
                btn.innerHTML = "{{ trans('langEditEn') }}";
            }else{
                _jm.enable_edit();
                btn.innerHTML = "{{ trans('langEditDis') }}";
            }
        }

        function reset(){
            var mind = {
                "meta":{
                    "name":"jsMind",
                },
                "format":"node_tree",
                "data":{"id":"root","topic":"jsMind Example"}
            };
            _jm.show(mind);
        }

        function screen_shot(){
            _jm.shoot();
        }

        function show_data(){
            var mind_data = _jm.get_data();
            var mind_string = jsMind.util.json.json2string(mind_data);
            prompt_info(mind_string);
        }

        function save_file(){
            var mind_data = _jm.get_data();
            var mind_name = prompt("{{ trans('langPleaseEnterName') }}", "Name");
            if (mind_name!=null){
                var mind_str = jsMind.util.json.json2string(mind_data);
                jsMind.util.file.save(mind_str,"text/jsmind",mind_name+".jm");
            }
        }

        function save_file_in_doc() {
            var x = prompt("{{ trans('langPleaseEnterName') }}", "Name");
            if (x != null && x.trim() !== "") {
                _jm.mind.name = x;

                // Get the jsMind DOM container
                var node = _jm.view.e_panel || document.querySelector(".jsmind-inner") || _jm.container;

                domtoimage.toPng(node)
                    .then(function(dataUrl) {
                        save_file_as_image(dataUrl, x);
                    })
                    .catch(function(error) {
                        console.error("Failed to generate image:", error);
                    });
            }
        }

        function save_file_as_image(urldat, imagename) {
            $.ajax({
                type: "POST",
                url: "../document/index.php",
                data: {
                    imgBase64: urldat,
                    imgname: imagename
                }
            })
                .done(function(response, textStatus, jqXHR) {
                    var mind_data = _jm.get_data();
                    var dataString = jsMind.util.json.json2string(mind_data);

                    window.location.href = "../document/index.php?mindmap=" +
                        encodeURIComponent(dataString) +
                        "&mindtitle=" +
                        encodeURIComponent(imagename);
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Upload failed:", textStatus, errorThrown);
                });
        }


        function open_file(){
            var file_input = document.getElementById("file_input");
            var files = file_input.files;
            if (files.length > 0) {
                var file_data = files[0];
                jsMind.util.file.read(file_data,function(jsmind_data, jsmind_name){
                    var mind = jsMind.util.json.string2json(jsmind_data);
                    if (!!mind) {
                        _jm.show(mind);
                    } else {
                        prompt_info("can not open this file as mindmap");
                    }
                });
            } else {
                prompt_info("{{ trans('langPleaseChooseFile') }}")
            }
        }

        function select_node(){
            var nodeid = "other";
            _jm.select_node(nodeid);
        }

        function show_selected(){
            var selected_node = _jm.get_selected_node();
            if(!!selected_node){
                prompt_info(selected_node.topic);
            }else{
                prompt_info("nothing");
            }
        }

        function toggle_node(){
            var selected_id = get_selected_nodeid();
            if (!selected_id) {
                prompt_info("{{ trans('langPleaseSelectNode') }}");
            }

            _jm.toggle_node(selected_id);
        }

        function get_selected_nodeid(){
            var selected_node = _jm.get_selected_node();
            if(!!selected_node){
                return selected_node.id;
            }else{
                return null;
            }
        }

        function add_node(){
            var selected_node = _jm.get_selected_node(); // as parent of new node
            if(!selected_node) {
                prompt_info("{{ trans('langPleaseSelectNode') }}");
            }
            var nodeid = jsMind.util.uuid.newid();
            var topic = "Node "+new_node+"";
            var node = _jm.add_node(selected_node, nodeid, topic);
            new_node=new_node+1;
        }


        function remove_node(){
            var selected_id = get_selected_nodeid();
            if(!selected_id){prompt_info("{{ trans('langPleaseSelectNode') }}");}

            _jm.remove_node(selected_id);
        }

        function set_theme(node){
            _jm.set_theme(node.getAttribute("data-theme"));
        }

        function prompt_info(string) {
            alert(string);
        }

        open_empty();
    </script>
@endsection