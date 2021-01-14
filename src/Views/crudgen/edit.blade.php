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
$identificador = $config['id'];
$url = CrudGenerator::translateDato($config['url'], $registro, $config);
$config = CrudGenerator::loadDefaultClasses($config);
$action = 'edit';
?>
@include("sirgrimorum::crudgen.partials.includes")
<?php
echo str_replace(":formId", $formId, $config['pre_form_html']);
echo Form::open(array('url' => $url, 'class' => $config['class_form'], 'method' => 'PUT', 'files' => $files, 'id' => $formId));
echo $config['pre_html'];
//echo Form::model($registro, array('url' => $url, $registro->{$identificador}, array('class' => $config['class_form']), 'method' => 'PUT', 'files'=> $files))
if (Request::has('_return')) {
    echo Form::hidden("_return", Request::get('_return'), array('id' => $tabla . '__return'));
}
echo Form::hidden("_action", "edit", array('id' => $tabla . '__action'));
echo Form::hidden("_registro", $registro->{$identificador}, array('id' => $tabla . '__registro'));
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
        "registro" => $registro, 
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
?>

@if ($botones != "")
@if (is_array($botones))
<div class="form-group row">
    @foreach ($botones as $boton)
    <?php
    $boton = str_replace([":modelId", ":modelName"], [$registro->{$config['id']}, $registro->{$config['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$registro->{$config['id']}, $registro->{$config['nombre']}], $boton));
    ?>
    <div class="{{ $config['class_offset'] }} {{ $config['class_divinput'] }}">
        @if (strpos($boton,"<")===false)
        {{ Form::submit($boton, array('class' => $config['class_button'])) }}
        @else
        {{ $boton }}
        @endif
    </div>
    @endforeach
</div>
@else
<?php
$botones = str_replace([":modelId", ":modelName"], [$registro->{$config['id']}, $registro->{$config['nombre']}], str_replace([urlencode(":modelId"), urlencode(":modelName")], [$registro->{$config['id']}, $registro->{$config['nombre']}], $botones));
?>
<div class="form-group row">
    <div class="{{ $config['class_offset'] }} {{ $config['class_divinput'] }}">
        @if (strpos($botones,"<")===false)
        {{ Form::submit($botones, array('class' => $config['class_button'])) }}
        @else
        {{ $botones }}
        @endif
    </div>
</div>
@endif
@else
<div class="form-group row">
    <div class="{{ $config['class_offset'] }} {{ $config['class_divinput'] }}">
        {{ Form::submit(trans('crudgenerator::crud.create.titulo'), array('class' => $config['class_button'])) }}
    </div>
</div>
@endif
{!! $config['post_html'] !!}
{{ Form::close() }}
{!! str_replace(":formId", $formId, $config['post_form_html']); !!}
@include("sirgrimorum::crudgen.partials.general_scripts", [
    'js_section' => $js_section,
])