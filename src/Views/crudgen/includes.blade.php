<?php
if ($tieneSlider || $tieneDate || $tieneSelect || $tieneSearch || $tieneFile) {
    if ($css_section != "") {
        ?>
        @push($css_section)
        <?php
    }
    if ($tieneSearch) {
        if (str_contains(config("sirgrimorum.crudgenerator.typeahead_path"), ['http', '://'])) {
            echo '<link href="' . config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.css" rel="stylesheet" type="text/css">';
        } else {
            echo '<link href="' . asset(config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.css') . '" rel="stylesheet">';
        }
        if (str_contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
            echo '<link href="' . config("sirgrimorum.crudgenerator.confirm_path") . '" rel="stylesheet" type="text/css">';
        } else {
            echo '<link href="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/css/jquery-confirm.min.css") . '" rel="stylesheet" type="text/css">';
        }
    }
    if ($tieneSelect) {
        if (str_contains(config("sirgrimorum.crudgenerator.select2_path"), ['http', '://'])) {
            echo '<link href="' . config("sirgrimorum.crudgenerator.select2_path") . '/css/select2.min.css" rel="stylesheet" type="text/css">';
        } else {
            echo '<link href="' . asset(config("sirgrimorum.crudgenerator.select2_path") . '/css/select2.min.css') . '" rel="stylesheet">';
        }
        ?>
        <style>
            .select2-selection{
                height: 37px !important;
            }
            span.select2-selection__rendered{
                line-height: 37px !important;
            }
            .select2-selection__arrow{
                height: 34px !important;
            }
            select[readonly].select2-hidden-accessible + .select2-container {
                pointer-events: none;
                touch-action: none;
            }
            .select2-selection {
                background: #eee;
                box-shadow: none;
            }

            .select2-selection__arrow,
            .select2-selection__clear {
                display: none;
            }
        </style>
        <?php
    }
    if ($tieneSlider) {
        if (str_contains(config("sirgrimorum.crudgenerator.slider_path"), ['http', '://'])) {
            echo '<link href="' . config("sirgrimorum.crudgenerator.slider_path") . '" rel="stylesheet" type="text/css">';
        } else {
            echo '<link href="' . asset(config("sirgrimorum.crudgenerator.slider_path") . '/css/bootstrap-slider.css') . '" rel="stylesheet">';
        }
    }
    if ($tieneDate) {
        if (str_contains(config("sirgrimorum.crudgenerator.datetimepicker_path"), ['http', '://'])) {
            echo '<link href="' . config("sirgrimorum.crudgenerator.datetimepicker_path") . '" rel="stylesheet" type="text/css">';
        } else {
            echo '<link href="' . asset(config("sirgrimorum.crudgenerator.datetimepicker_path") . '/css/bootstrap-datetimepicker.min.css') . '" rel="stylesheet">';
        }
    }
    if ($tieneFile) {
        ?>
        <style>
            .custom-file-name:after {
                content: attr(data-content)!important;
                position: absolute;
                top: 0px;
                left: 0px;
                display: block;
                height: 100%;
                overflow: hidden;
                padding: 0.5rem 1rem;
            }
            .input-group img{
                max-height: 35px !important;
            }
        </style>
        <?php
    }
    if ($css_section != "") {
        ?>
        @endpush
        <?php
    }
}
if ($tieneHtml || $tieneDate || $tieneSlider || $tieneSelect || $tieneSearch || $tieneFile) {
    if ($js_section != "") {
        ?>
        @push($js_section)
        <?php
    }
    if ($tieneSearch) {
        if (str_contains(config("sirgrimorum.crudgenerator.typeahead_path"), ['http', '://'])) {
            echo '<script src="' . config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.js"></script>';
        } else {
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.js') . '"></script>';
        }
        if (str_contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
            echo '<script src="' . config("sirgrimorum.crudgenerator.confirm_path") . '"></script>';
            echo '<script src="' . asset("vendor/sirgrimorum/confirm/js/rails.js") . '"></script>';
        } else {
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/jquery-confirm.min.js") . '"></script>';
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/rails.js") . '"></script>';
        }
    }
    if ($tieneSelect) {
        if (str_contains(config("sirgrimorum.crudgenerator.select2_path"), ['http', '://'])) {
            echo '<script src="' . config("sirgrimorum.crudgenerator.select2_path") . '/js/select2.min.js"></script>';
        } else {
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.select2_path") . '/js/select2.min.js') . '"></script>';
        }
    }
    if ($tieneSlider) {
        if (str_contains(config("sirgrimorum.crudgenerator.slider_path"), ['http', '://'])) {
            //echo '<script src="' . config("sirgrimorum.crudgenerator.slider_path") . '"></script>';
        } else {
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.slider_path") . '/js/bootstrap-slider.js') . '"></script>';
        }
    }
    if ($tieneDate) {
        if (str_contains(config("sirgrimorum.crudgenerator.datetimepicker_path"), ['http', '://'])) {
            //echo '<script src="' . config("sirgrimorum.crudgenerator.datetimepicker_path") . '"></script>';
        } else {
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.datetimepicker_path") . '/js/moment-with-locales.min.js') . '"></script>';
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.datetimepicker_path") . '/js/bootstrap-datetimepicker.min.js') . '"></script>';
        }
    }
    if ($tieneHtml) {
        $csss = config("sirgrimorum.crudgenerator.principal_css");
        if (($left = (stripos($csss, '__asset__'))) !== false) {
            while ($left !== false) {
                $right = stripos($csss, '__', $left + strlen('__asset__'));
                $piece = asset(substr($csss, $left + strlen('__asset__'), $right - ($left + strlen('__asset__'))));
                $csss = substr($csss, 0, $left) . $piece . substr($csss, $right + 2);
                //echo "<pre>" . print_r(['left' => $left, 'rigth' => $right, 'piece' => $piece, 'lenpiece'=>strlen($piece), 'csss' => $csss], true) . "</pre>";
                $left = (stripos($csss, '__asset__'));
            }
        }
        echo "<script>var urlAssetsCkEditor = [" . $csss . "];</script>";
        if (str_contains(config("sirgrimorum.crudgenerator.ckeditor_path"), ['http', '://'])) {
            echo '<script src="' . config("sirgrimorum.crudgenerator.ckeditor_path") . '"></script>';
        } else {
            echo '<script src="' . asset(config("sirgrimorum.crudgenerator.ckeditor_path")) . '"></script>';
        }
    }
    if ($tieneFile) {
        ?>
        <script>
            $('body').on('change', 'input[type="file"][data-toggle="custom-file"]', function (ev) {
                var $input = $(this);
                console.log("qui",$input.parent().parent().find('img').first());
                var $target_name = $input.parent().parent().children('input[type="text"]').first();
                var $target_image = $input.parent().parent().find('img').first();
                var $target = $input.parent().children('label').first();
                if (!$target.length)
                    return console.error('Invalid target for custom file', $input);

                // set original content so we can revert if user deselects file
                if (!$target.attr('data-original-content'))
                    $target.attr('data-original-content', $target.text());
                
                previo_nombre = $target.html();
                const input = $input.get(0);
                let name = _.isObject(input)
                        && _.isObject(input.files)
                        && _.isObject(input.files[0])
                        && _.isString(input.files[0].name) ? input.files[0].name : $input.val();
                let file = _.isObject(input)
                        && _.isObject(input.files)
                        && _.isObject(input.files[0]) ? input.files[0] : $input.val();
                
                if (_.isNull(name) || name === ''){
                    name = $target.attr('data-original-content');
                }
                showImage($target_image,file);
                
                $target.text(name);
                if ($target.text()==$target.attr('data-original-content')){
                    if ($target_name.val()==previo_nombre.replace(/\.[^/.]+$/, ""))
                    $target_name.removeAttr("required");
                    
                }else{
                    $target_name.attr("required","required");
                    if ($target_name.val()=="" || $target_name.val()==previo_nombre.replace(/\.[^/.]+$/, "")){
                        $target_name.val($target.text().replace(/\.[^/.]+$/, ""));
                        
                    }
                }

            });
            
            function showImage($target_image, file){
                console.log("file",file);
                var $target_image_companion = $target_image.parent().parent().children('div').eq(1);
                var $target_icon = $target_image.parent().find('i').first().parent();
                var $collapse_image = $target_image.parent().parent().parent().next('div[data-id="collapseImageCont"]').first().find('img').first();

                if (_.isNull(file) || file === ''){
                    $target_image_companion.removeClass("rounded-left").addClass("rounded-left");
                    $target_image.removeClass("d-none").addClass("d-none");
                    $target_icon.removeClass("d-none").addClass("d-none");
                    $target_image.parent().removeClass("d-none").addClass("d-none");
                }else{
                    var imageType = /image.*/
                    if(!file.type.match(imageType)){
                        console.log("Not an Image");
                        $target_image.removeClass("d-none").addClass("d-none");
                        $target_image.parent().removeClass("d-none");
                        $target_image_companion.removeClass("rounded-left");
                        $target_icon.removeClass("d-none");
                    }else{
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $collapse_image.attr('src', e.target.result);
                            $target_image.attr('src', e.target.result);
                            $target_icon.removeClass("d-none").addClass("d-none");
                            $target_image.removeClass("d-none");
                            $target_image.parent().removeClass("d-none");
                            $target_image_companion.removeClass("rounded-left");
                        }
                        reader.readAsDataURL(file);
                    }
                }
            }
            function toogleImagen(img){
                var $collapse_image_cont = $(img).parent().parent().parent().next('div[data-id="collapseImageCont"]').first();
                $collapse_image_cont.collapse('toggle');
            }
            function addFile(tipo,columna){
                //var html = $("#" + tipo +"_clone").clone().removeClass('d-none').wrap('<div/>').parent().html();
                var html = $("#" + tipo +"_clone").clone().html();
                //console.log(tipo,html,$("#" + tipo +"_container >div").last());
                $html = $(html);
                $html.find('input[type="text"]').first().attr("required","required");
                $html.find('input[type="text"]').first().attr("name",columna + "_name[]");
                $("#" + tipo +"_container >div").last().after($html);
            }
            function removeFile(objeto,tipo){
                $(objeto).parents(".input-group").remove();
            }
        </script>
        <?php
    }
    if ($js_section != "") {
        ?>
        @endpush
        <?php
    }
}
