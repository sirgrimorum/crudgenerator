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
$url = $config['url'];
if (!isset($config['class_form'])) {
    $config['class_form'] = '';
}
if (!isset($config['class_labelcont'])) {
    $config['class_labelcont'] = 'col-xs-12 col-sm-3 col-md-2';
}
if (!isset($config['class_label'])) {
    $config['class_label'] = 'col-form-label font-weight-bold mb-0 pb-0';
}
if (!isset($config['class_divinput'])) {
    $config['class_divinput'] = 'col-xs-12 col-sm-8 col-md-10';
}
if (!isset($config['class_input'])) {
    $config['class_input'] = '';
}
if (!isset($config['class_offset'])) {
    $config['class_offset'] = 'offset-xs-0 offset-sm-4 offset-md-2';
}
if (!isset($config['class_button'])) {
    $config['class_button'] = 'btn btn-primary';
}
if (!isset($config['class_formgroup'])) {
    $config['class_formgroup'] = '';
}
if (!isset($config['pre_html'])){
    $config['pre_html']="";
}
if (!isset($config['post_html'])){
    $config['post_html']="";
}
$action = 'create';
?>
@include("sirgrimorum::crudgen.includes")
<?php
echo Form::open(array('url' => $url, 'class' => $config['class_form'], 'files' => $files));
echo $config['pre_html'];
if (Request::has('_return')) {
    echo Form::hidden("_return", Request::get('_return'), array('id' => $tabla . '__return'));
}
echo Form::hidden("_action", "create", array('id' => $tabla . '__action'));
if (isset($config['parametros'])){
    echo Form::hidden("__parametros", $config['parametros'], array('id' => $tabla . '__parametros'));
}

foreach ($campos as $columna => $datos) {
    if (!isset($datos['nodb']) && CrudGenerator::inside_array($datos, "hide", "create") === false) {
        if (isset($datos['pre_html'])){
            echo $datos['pre_html'];
        }
        if (View::exists("sirgrimorum::crudgen.templates." . $datos['tipo'])) {
            ?>
            @include("sirgrimorum::crudgen.templates." . $datos['tipo'], ['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo, 'action'=>$action])
            <?php
        } else {
            ?>
            @include("sirgrimorum::crudgen.templates.text", ['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo])
            <?php
        }
        if (isset($datos['post_html'])){
            echo $datos['post_html'];
        }
    }
}/**/

if (count($botones) > 0) {
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
    echo '<div class="form-group row">';
    echo '<div class="' . $config['class_offset'] . ' ' . $config['class_divinput'] . '">';
    echo Form::submit(trans('crudgenerator::crud.create.titulo'), array('class' => $config['class_button']));
    echo '</div>';
    echo '</div>';
}
echo $config['post_html'];
echo Form::close();
?>