<?php
if (!isset($nameScriptLoader)){
    $nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
}
if ((($action == "create" && !isset($datos['nodb'])) || $action != "create") && CrudGenerator::inside_array($datos, "hide", $action) === false) {
    if (isset($datos['readonly'])){
        if (is_array($datos['readonly'])){
            if (CrudGenerator::inside_array($datos, "readonly", $action) !== false){
                $datos['readonly'] = 'readonly';
            }else{
                unset($datos['readonly']);
            }
        }
    }
    if (isset($datos['nodb']) && !isset($datos['readonly'])){
        $datos['readonly'] = 'readonly';
    }
    if (isset($datos['pre_html'])){
        echo $datos['pre_html'];
    }
    if (View::exists("sirgrimorum::crudgen.templates." . $datos['tipo'])) {
        ?>
        @include("sirgrimorum::crudgen.templates." . $datos['tipo'], ['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'registro' => $registro, 'errores' => $errores, 'modelo'=>$modelo, 'action'=>$action])
        <?php
    } else {
        ?>
        @include("sirgrimorum::crudgen.templates.text", ['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'registro' => $registro, 'errores' => $errores, 'modelo'=>$modelo, 'action'=>$action])
        <?php
    }
    if (($inputFilter = \Illuminate\Support\Arr::get($datos,'inputfilter', "")) != ""){
        if (isset($config["extraId"])) {
            $extraId = $config['extraId'];
        } else {
            $extraId = $columna;
        }
        if ($js_section != "") {
            ?>
            @push($js_section)
            <?php
        }
        ?>
        <script>
            {{ $nameScriptLoader }}('setinputfilter_js',"setInputFilter(document.getElementById('{{ $tabla . '_' . $extraId }}'),{!! str_replace('"','\"', $inputFilter) !!}");
        </script>
        <?php
        if ($js_section != "") {
            ?>
            @endpush
            <?php
        }
    }
    if (isset($datos['post_html'])){
        echo $datos['post_html'];
    }
}