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
@include("sirgrimorum::crudgen.includes")
<?php
echo Form::open(array('url' => $url, 'class' => $config['class_form'], 'method' => 'PUT', 'files' => $files));
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
foreach ($campos as $columna => $datos) {
    if (CrudGenerator::inside_array($datos, "hide", "edit") === false) {
        if (isset($datos['readonly'])){
            if (is_array($datos['readonly'])){
                if (CrudGenerator::inside_array($datos, "readonly", "edit") !== false){
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
            @include("sirgrimorum::crudgen.templates." . $datos['tipo'],['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo, 'action'=>$action])
            <?php
        } else {
            ?>
            @include("sirgrimorum::crudgen.templates.text",['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo])
            <?php
        }
        if (isset($datos['post_html'])){
            echo $datos['post_html'];
        }
    }
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
@include("sirgrimorum::crudgen.general_scripts", [
    'js_section' => $js_section,
])