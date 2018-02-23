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
$url = $config['url'];
if (!isset($config['class_form'])) {
    $config['class_form'] = 'form-horizontal';
}
if (!isset($config['class_label'])) {
    $config['class_label'] = 'col-xs-12 col-sm-4 col-md-2 col-form-label';
}
if (!isset($config['class_divinput'])) {
    $config['class_divinput'] = 'col-xs-12 col-sm-8 col-md-10';
}
if (!isset($config['class_input'])) {
    $config['class_input'] = '';
}
if (!isset($config['class_offset'])) {
    $config['class_offset'] = 'col-xs-offset-0 col-sm-offset-4 col-md-offset-2';
}
if (!isset($config['class_button'])) {
    $config['class_button'] = 'btn btn-primary';
}

if ($tieneSlider|| $tieneDate || $tieneSelect) {
    if ($css_section != "") {
        ?>
        @push($css_section)
        <?php
    }
    if ($tieneSelect) {
        if (str_contains(config("sirgrimorum.crudgenerator.select2_path"), ['http', '://'])) {
            echo '<link href="' . config("sirgrimorum.crudgenerator.select2_path") . '/css/select2.min.css" rel="stylesheet" type="text/css">';
        } else {
            echo '<link href="' . asset(config("sirgrimorum.crudgenerator.select2_path") . '/css/select2.min.css') . '" rel="stylesheet">';
        }
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
    if ($css_section != "") {
        ?>
        @endpush
        <?php
    }
}
if ($tieneHtml || $tieneDate || $tieneSlider || $tieneSelect) {
    if ($js_section != "") {
        ?>
        @push($js_section)
        <?php
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
    if ($js_section != "") {
        ?>
        @endpush
        <?php
    }
}
echo Form::open(array('url' => $url, 'class' => $config['class_form'], 'method' => 'PUT', 'files' => $files));
//echo Form::model($registro, array('url' => $url, $registro->{$identificador}, array('class' => $config['class_form']), 'method' => 'PUT', 'files'=> $files))
if ( Request::has('_return')) {
    echo Form::hidden("_return",  Request::get('_return'), array('id' => $tabla . '__return'));
}
echo Form::hidden("_action",  "edit", array('id' => $tabla . '__action'));
echo Form::hidden("_registro",  $registro->{$identificador}, array('id' => $tabla . '__registro'));
?>

@foreach($campos as $columna => $datos)
@if (CrudLoader::inside_array($datos,"hide","edit")===false)
@if (View::exists("sirgrimorum::crudgen.templates." .$datos['tipo']))
@include("sirgrimorum::crudgen.templates." . $datos['tipo'],['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section])
@else
@include("sirgrimorum::crudgen.templates.text",['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section])
@endif
@endif
@endforeach

@if (count($botones)>0)
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
{{ Form::close() }}
