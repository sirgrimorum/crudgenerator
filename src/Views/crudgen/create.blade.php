@if (Session::has(config("sirgrimorum.crudgenerator.status_messages_key")))
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    {!! Session::pull(config("sirgrimorum.crudgenerator.status_messages_key")) !!}
</div>
@endif
<?php $errores = false ?>
@if (count($errors->all())>0)
<?php $errores = true ?>
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<?php
$tabla = $config['tabla'];
$formId = \Illuminate\Support\Arr::get($config, 'formId', $tabla . "_" . \Illuminate\Support\Str::random(5));
$campos = $config['campos'];
$botones = $config['botones'];
if (isset($config['files'])) {
    $files = $config['files'];
} else {
    $files = false;
}
if (isset($config['relaciones'])) {
    $relaciones = $config['relaciones'];
}
$url = $config['url'];
$action = 'create';
?>
@include("sirgrimorum::crudgen.partials.includes")
<?php
echo str_replace(":formId", $formId, $config['pre_form_html']);
echo Form::open(array('url' => $url, 'class' => $config['class_form'], 'files' => $files, 'id' => $formId));
echo $config['pre_html'];
if (Request::has('_return')) {
    echo Form::hidden("_return", Request::get('_return'), array('id' => $tabla . '__return'));
}
echo Form::hidden("_action", "create", array('id' => $tabla . '__action'));
if (isset($config['parametros'])){
    echo Form::hidden("__parametros", $config['parametros'], array('id' => $tabla . '__parametros'));
}
$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
foreach ($campos as $columna => $datos) {
    ?>
    @include("sirgrimorum::crudgen.partials.create_inner",[
        "js_section" => $js_section,
        'css_section'=> $css_section, 
        'modelo'=> $modelo, 
        'action'=> $action,
        "tabla" => $tabla,
        "config" => $config,
        "columna" => $columna,
        "datos" => $datos,
        "registro" => null, 
        "errores" => $errores,
        "nameScriptLoader" => $nameScriptLoader,
    ])
    <?php
}
if (is_array($botones)){
    if (count($botones)==0){
        $botones = "";
    }
}
if ($botones != "") {
    if (is_array($botones)) {
        echo '<div class="form-group row">';
        foreach ($botones as $boton) {
            echo '<div class="' . $config['class_offset'] . ' ' . $config['class_divinput'] . '">';
            if (strpos($boton, "<") === false) {
                echo Form::submit($boton, array('class' => $config['class_button']));
            } else {
                echo $boton;
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div class="form-group row">';
        echo '<div class="' . $config['class_offset'] . ' ' . $config['class_divinput'] . '">';
        if (strpos($botones, "<") === false) {
            echo Form::submit($botones, array('class' => $config['class_button']));
        } else {
            echo $botones;
        }
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="' . $config['class_divbutton']. '">';
    echo '<div class="' . $config['class_offset'] . ' ' . $config['class_divinput'] . '">';
    echo Form::submit(trans('crudgenerator::crud.create.titulo'), array('class' => $config['class_button']));
    echo '</div>';
    echo '</div>';
}
echo $config['post_html'];
echo Form::close();
echo str_replace(":formId", $formId, $config['post_form_html']);
?>
@include("sirgrimorum::crudgen.partials.general_scripts", [
    'js_section' => $js_section,
])