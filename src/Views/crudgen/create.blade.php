@if (Session::has('message'))
<div class="alert alert-info">{{ Session::pull('message') }}</div>
@endif
<?php $errores = false ?>
@if (count($errors->all())>0)
<?php $errores = true ?>
<div class="alert alert-danger">
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
    $config['class_form'] = 'form-horizontal';
}
if (!isset($config['class_label'])) {
    $config['class_label'] = 'col-xs-12 col-sm-4 col-md-2';
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


if (isset($config['render'])) {
    $selects = array('column_name as field', 'column_type as type', 'is_nullable as null', 'column_key as key', 'column_default as default', 'extra as extra');
    $table_describes = DB::table('information_schema.columns')
            ->where('table_name', '=', $tabla)
            ->get($selects);
    foreach ($table_describes as $k => $v) {
        if (($kt = array_search($v, $table_describes)) !== false and $k != $kt) {
            unset($table_describes[$kt]);
        }
    }
}
if ($tieneSlider || $tieneDate ) {
    if (config("sirgrimorum.crudgenerator.css_section") != "") {
        ?>
        @section(config("sirgrimorum.crudgenerator.css_section"))
        @parent
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
    if (config("sirgrimorum.crudgenerator.css_section") != "") {
        ?>
        @stop
        <?php
    }
}
if ($tieneHtml || $tieneSlider || $tieneDate) {
    if (config("sirgrimorum.crudgenerator.js_section") != "") {
        ?>
        @section(config("sirgrimorum.crudgenerator.js_section"))
        @parent
        <?php
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
    if (config("sirgrimorum.crudgenerator.js_section") != "") {
        ?>
        @stop
        <?php
    }
}

echo Form::open(array('url' => $url, 'class' => $config['class_form'], 'files' => $files));
if ( Request::has('_return')) {
    echo Form::hidden("_return",  Request::get('_return'), array('id' => $tabla . '__return'));
}


    //echo "<pre>" . print_r($campos, true) . "</pre>";

    foreach ($campos as $columna => $datos) {
        if (View::exists("sirgrimorum::crudgen.templates." . $datos['tipo'])) {
            ?>
            @include("sirgrimorum::crudgen.templates." . $datos['tipo'], ['datos'=>$datos])
            <?php
        } else {
            ?>
            @include("sirgrimorum::crudgen.templates.text", ['datos'=>$datos])
            <?php
        }
    }/**/

if (count($botones) > 0) {
    if (is_array($botones)) {
        echo '<div class="form-group">';
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
        echo '<div class="form-group">';
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
    echo '<div class="form-group">';
    echo '<div class="' . $config['class_offset'] . ' ' . $config['class_divinput'] . '">';
    echo Form::submit(trans('crudgenerator::crud.create.titulo'), array('class' => $config['class_button']));
    echo '</div>';
    echo '</div>';
}

echo Form::close();
