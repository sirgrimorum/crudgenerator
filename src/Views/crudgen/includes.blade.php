<?php
if ($tieneSlider || $tieneDate || $tieneSelect || $tieneSearch || $tieneColor || $tieneFile || $tieneJson) {
    if ($css_section != "") {
        ?>
        @push($css_section)
        <?php
    }
    if ($tieneJson || $tieneSearch || $tieneFile) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.confirm_path"));
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(asset(config("sirgrimorum.crudgenerator.confirm_path") . "/css/jquery-confirm.min.css"));
        }
    }
    if ($tieneJson) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.jsonedtr_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.jsonedtr_path"));
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(asset(config("sirgrimorum.crudgenerator.jsonedtr_path") . "/JSONedtr.css"));
        }
    }
    if ($tieneSearch) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.typeahead_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.typeahead_path"). '/jquery.typeahead.min.css');
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml( asset(config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.css'));
        }
    }
    if ($tieneColor) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.colorpicker_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.colorpicker_path"). '/css/bootstrap-colorpicker.css');
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml( asset(config("sirgrimorum.crudgenerator.colorpicker_path") . '/css/bootstrap-colorpicker.css'));
        }
    }
    if ($tieneSelect) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.select2_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.select2_path") . '/css/select2.min.css');
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(asset(config("sirgrimorum.crudgenerator.select2_path") . '/css/select2.min.css'));
        }
        ?>
        <style>
            .select2-selection{
                min-height: 37px !important;
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
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.slider_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.slider_path"));
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(asset(config("sirgrimorum.crudgenerator.slider_path") . '/css/bootstrap-slider.css'));
        }
    }
    if ($tieneDate) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.datetimepicker_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(config("sirgrimorum.crudgenerator.datetimepicker_path"));
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addLinkTagLoaderHtml(asset(config("sirgrimorum.crudgenerator.datetimepicker_path") . '/css/bootstrap-datetimepicker.min.css'));
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
list($condiciones,$validadores)= Sirgrimorum\CrudGenerator\CrudGenerator::buildConditionalArray($config,$action);
//echo "<p>Condiciones</p><pre>" . print_r([$condiciones,$validadores], true) . "</pre>";
if ($tieneHtml || $tieneDate || $tieneSlider || $tieneSelect || $tieneSearch || $tieneColor || $tieneFile || $tieneJson || $tieneCheckeador || count($condiciones)>0) {
    if ($js_section != "") {
        ?>
        @push($js_section)
        <?php
    }
    if ($tieneSearch) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.typeahead_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.js',false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.typeahead_path") . '/jquery.typeahead.min.js'),false);
        }
    }
    if ($tieneCheckeador) {
        echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset("vendor/sirgrimorum/checkeador/checkeador.js"),false);
    }
    if ($tieneSearch || $tieneFile || $tieneJson) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.confirm_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.confirm_path"),false);
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset("vendor/sirgrimorum/confirm/js/rails.js"),false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/jquery-confirm.min.js"),false);
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.confirm_path") . "/js/rails.js"),false);
        }
    }
    if ($tieneJson) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.jsonedtr_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.jsonedtr_path"),false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.jsonedtr_path") . "/JSONedtr.js"),false);
        }
    }
    if ($tieneColor) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.colorpicker_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.colorpicker_path") . '/js/bootstrap-colorpicker.js',false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.colorpicker_path") . '/js/bootstrap-colorpicker.js'),false);
        }
    }
    if ($tieneSelect) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.select2_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.select2_path") . '/js/select2.min.js',false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.select2_path") . '/js/select2.min.js'),false);
        }
    }
    if ($tieneSlider) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.slider_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.slider_path"),false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.slider_path") . '/js/bootstrap-slider.js'),false);
        }
    }
    if ($tieneDate) {
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.datetimepicker_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset("vendor/sirgrimorum/datetimepicker/js/moment-with-locales.min.js"),false);
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.datetimepicker_path") ,false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.datetimepicker_path") . '/js/moment-with-locales.min.js'),false);
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.datetimepicker_path") . '/js/bootstrap-datetimepicker.min.js') ,false);
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
        echo "<script>var urlAssetsCkEditor = " . $csss . ";</script>";
        if (\Illuminate\Support\Str::contains(config("sirgrimorum.crudgenerator.ckeditor_path"), ['http', '://'])) {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(config("sirgrimorum.crudgenerator.ckeditor_path"),false);
        } else {
            echo Sirgrimorum\CrudGenerator\CrudGenerator::addScriptLoaderHtml(asset(config("sirgrimorum.crudgenerator.ckeditor_path")),false);
        }
    }
    if ($tieneFile) {
        ?>
        <script>
            function _isObject(variable){
                return typeof variable === 'object' && variable !== null;
            }
            function _isString(variable){
                return typeof variable === 'string' || variable instanceof String;
            }
            function _isNull(variable){
                return typeof variable === null || variable === NaN || variable === undefined ||  typeof variable === 'undefined';
            }
            $('body').on('change', 'input[type="file"][data-toggle="custom-file"]', function (ev) {
                var $input = $(this);
                //console.log("qui",$input.parent().parent().find('img').first());
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
                let name = _isObject(input)
                        && _isObject(input.files)
                        && _isObject(input.files[0])
                        && _isString(input.files[0].name) ? input.files[0].name : $input.val();
                let file = _isObject(input)
                        && _isObject(input.files)
                        && _isObject(input.files[0]) ? input.files[0] : $input.val();
                
                if (_isNull(name) || name === ''){
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

                if (_isNull(file) || file === ''){
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
            function toogleVideo(vid){
                var $collapse_video_cont = $(vid).parent().parent().parent().next('div[data-id="collapseVideoCont"]').first();
                $collapse_video_cont.collapse('toggle');
            }
            function toogleAudio(aud){
                var $collapse_audio_cont = $(aud).parent().parent().parent().next('div[data-id="collapseAudioCont"]').first();
                $collapse_audio_cont.collapse('toggle');
            }
            function tooglePdf(aud){
                var $collapse_pdf_cont = $(aud).parent().parent().parent().next('div[data-id="collapsePdfCont"]').first();
                $collapse_pdf_cont.collapse('toggle');
            }
            function toogleUrl(url){
                var $collapse_url_cont = $(url).parent().parent().parent().next('div[data-id="collapseUrlCont"]').first();
                var $url_text = $(url).parent().parent().parent().find('input[type="text"]').first();
                var $url_iframe = $collapse_url_cont.find('iframe').first();
                var link = $url_text.val();
                if (link.indexOf("youtube")>0){
                    //var regExp = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
                    var regExp = /^.*(?:youtube(?:-nocookie)?\.com\/(?:[^/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11}).*/;
                    var match = link.match(regExp);
                    console.log($url_text.val(),match);
                    link = 'https://www.youtube.com/embed/' + match[1];
                }
                $url_iframe.attr("src",link);
                $collapse_url_cont.collapse('toggle');
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
                if (tipo !="nuevo"){
                //$("#" + tipo + "_name_nuevo").attr("required","required");
                //console.log('quitar',idSelected,nameSelected);
                $nameText = $(objeto).parent().parent().find('input.nombre_file[type="text"]').first();
                confirmTitle = '{{trans('crudgenerator::admin.layout.labels.confirm_title')}}';
                confirmTitle = confirmTitle.replace(":modelName", $nameText.val());
                confirmContent = '{!!trans('crudgenerator::admin.messages.confirm_removefile')!!}';
                confirmContent = confirmContent.replace(":modelName", $nameText.val());
                $.confirm({
                    theme: '{{config('sirgrimorum.crudgenerator.confirm_theme')}}',
                    icon: '{!!config('sirgrimorum.crudgenerator.icons.confirm')!!}',
                    title: confirmTitle,
                    content: confirmContent,
                        buttons: {
                            ['{{trans('crudgenerator::admin.layout.labels.yes')}}']: function () {
                                $(objeto).parents(".input-group").remove();
                            },
                            ['{{trans('crudgenerator::admin.layout.labels.no')}}']: function () {

                            },
                        }
                    });
                }else{
                    $(objeto).parents(".input-group").remove();
                }
            }
        </script>
        <?php
    }
    if ($tieneJson) {
        ?>
        <script>
            function prettyPrint(idElement) {
                try {
                    var ugly = document.getElementById(idElement).value;
                    var obj = JSON.parse(ugly);
                    var pretty = JSON.stringify(obj, undefined, 4);
                    document.getElementById(idElement).value = pretty;
                    if (typeof window['json_' + idElement] !== 'undefined'){
                        window['json_' + idElement] = new JSONedtr(ugly, '#contenedor_json_' + idElement,{
                            'instantChange' : true,
                            'runFunctionOnUpdate' : 'json_' + idElement + '_onChange'
                        });
                    }
                }catch(err) {
                    confirmTitle = '{{trans('crudgenerator::admin.layout.labels.error_json_title')}}';
                    confirmContent = '{!!trans('crudgenerator::admin.messages.error_json')!!}<br>' + err.message;
                    $.alert({
                        theme: '{{config('sirgrimorum.crudgenerator.confirm_theme')}}',
                        title: confirmTitle,
                        content: confirmContent,
                    });
                }
            }
        </script>
        <?php
    }
    if (count($condiciones)>0){
    ?>
        <script>
        <?php
            foreach($condiciones as $idCampo=>$condicionados){
                ?>
                    $('body').on('change','#{{$idCampo}}', function(){
                        <?php
                        foreach ($condicionados as $condicionado){
                            ?>
                            evaluar_{{$condicionado}}();
                            <?php
                        }
                        ?>
                    });
                <?php
            }
            foreach($validadores as $idCampo => $validaciones){
                ?>
                    function evaluar_{{$idCampo}}(){
                        var mostrar = true;
                        <?php
                        foreach ($validaciones as $validador=>$valorVal){
                            if($valorVal=='{:empty}'){
                                ?>
                                if ($("#{{$validador}}").val()!="" && $("#{{$validador}}").val()!="-" && !($("#{{$validador}}").val() === null || $("#{{$validador}}").val() === undefined)){
                                    mostrar = false;
                                }
                                <?php
                            }elseif($valorVal == '{:notempty}'){
                                ?>
                                if ($("#{{$validador}}").val()=="" || $("#{{$validador}}").val()=="-" || ($("#{{$validador}}").val() === null || $("#{{$validador}}").val() === undefined)){
                                    mostrar = false;
                                }
                                <?php
                            }elseif(stripos($valorVal,':!')===0){
                                $valorVal = str_replace(":!", "", $valorVal);
                                ?>
                                if ($("#{{$validador}}").val()=="{{$valorVal}}"){
                                    mostrar = false;
                                }
                                <?php
                            }elseif(stripos($valorVal,':=')===0){
                                $valorVal = str_replace(":=", "", $valorVal);
                                ?>
                                if ($("#{{$validador}}").val()!="{{$valorVal}}"){
                                    mostrar = false;
                                }
                                <?php
                            }elseif(stripos($valorVal,':>')===0){
                                $valorVal = str_replace(":>", "", $valorVal);
                                ?>
                                if ($("#{{$validador}}").val()<="{{$valorVal}}"){
                                    mostrar = false;
                                }
                                <?php
                            }elseif(stripos($valorVal,':<')===0){
                                $valorVal = str_replace(":<", "", $valorVal);
                                ?>
                                if ($("#{{$validador}}").val()>="{{$valorVal}}"){
                                    mostrar = false;
                                }
                                <?php
                            }else{
                                ?>
                                if ($("#{{$validador}}").val()!="{{$valorVal}}"){
                                    mostrar = false;
                                }
                                <?php
                            }
                        }
                        ?>
                        var $contenedor = $('div[data-campo="{{$idCampo}}"][data-tipo="contenedor-campo"]').first();
                        if ($contenedor.is(":visible") && !mostrar){
                            $("#{{$idCampo}}").val("");
                        }
                        if (mostrar){
                            $contenedor.show();
                        }else{
                            $contenedor.hide();
                        }
                    }
                <?php
            }
            ?>
            window.addEventListener('load', function() {
                <?php
                foreach($validadores as $idCampo => $validaciones){
                ?>
                        evaluar_{{$idCampo}}();
                <?php
                }
                ?>
            });
            </script>
        <?php
    }
    if ($js_section != "") {
        ?>
        @endpush
        <?php
    }
}
