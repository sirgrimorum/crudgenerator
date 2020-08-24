<?php
$modelo = strtolower(class_basename($config["modelo"]));
$modeloUCF = ucfirst(strtolower(class_basename($config["modelo"])));
$base_url = route('sirgrimorum_home', App::getLocale());
$plural = $modelo . 's';
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.plural")) {
    $plurales = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
} else {
    $plurales = ucfirst($plural);
}
if (Lang::has("crudgenerator::" . strtolower($modelo) . ".labels.singular")) {
    $singulares = trans("crudgenerator::" . strtolower($modelo) . ".labels.singular");
} else {
    $singulares = ucfirst($modelo);
}
if (!isset($config['class_button'])) {
    $config['class_button'] = 'btn btn-primary';
}
$siOld = false;
if (old("__parametros","") != ""){
    $parametrosOld = json_decode(old("__parametros"), true);
    if (strtolower(class_basename($parametrosOld["modelo"])) == $modelo){
        $siOld = true;
    }
}
$botones = $config['botones'];
if (is_array($botones)){
    if (count($botones)==0){
        $botones = "";
    }
}
if ($botones == ""){
    $botones = trans("crudgenerator::$modelo.labels.name");
}
if ($botones != "") {
    if (is_array($botones)) {
        echo '<div class="btn-group">';
        foreach ($botones as $boton) {
            if (strpos($boton, "<") === false) {
                echo "<button onclick='$(\"#{$modeloUCF}_create_modal\").modal(\"show\");' class='" . $config['class_button'] . "'>$boton</button>";
            } else {
                echo $boton;
            }
        }
        echo '</div>';
    } else {
        if (strpos($botones, "<") === false) {
            echo "<button onclick='$(\"#{$modeloUCF}_create_modal\").modal(\"show\");' class='" . $config['class_button'] . "' >$botones</button>";
        } else {
            echo $botones;
        }
    }
} else {
    echo "<button onclick='$(\"#{$modeloUCF}_create_modal\").modal(\"show\");' class='" . $config['class_button'] . "'>" . trans('crudgenerator::admin.layout.labels.create') . "</button>";
}
?>

@if (config("sirgrimorum.crudgenerator.modal_section") != "")
@push(config("sirgrimorum.crudgenerator.modal_section"))
@endif
<!-- Modal for create {{ $modeloUCF }} -->
@include('sirgrimorum::admin.create.modal', ["modelo" => $modeloUCF,"base_url" => $base_url, "plural" =>$plural,"config" => $config])
@if (config("sirgrimorum.crudgenerator.modal_section") != "")
@endpush
@endif

@if ($siOld)
@if ($js_section != "")
@push($js_section)
@endif
<script>
    $(document).ready(function() {
        $("#{{$modeloUCF}}_create_modal").modal("show");
    });
</script>                                  
@if ($js_section != "")
@endpush
@endif
@endif
