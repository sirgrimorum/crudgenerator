@if (Session::has(config("sirgrimorum.crudgenerator.status_messages_key")))
<div class="alert alert-info alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
    {!! Session::pull(config("sirgrimorum.crudgenerator.status_messages_key")) !!}
</div>
@endif
@if (count($errors->all())>0)
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
if (!isset($config['class_form'])) {
    $config['class_form'] = 'form';
}
if (!isset($config['class_labelcont'])) {
    $config['class_labelcont'] = 'col-xs-12 col-md-12 col-lg-2';
}
if (!isset($config['class_label'])) {
    $config['class_label'] = 'col-form-label font-weight-bold mb-0 pb-0';
}
if (!isset($config['class_divinput'])) {
    $config['class_divinput'] = 'col-xs-12 col-md-12 col-lg-10 border-left border-light pl-15';
}
if (!isset($config['class_formgroup'])) {
    $config['class_formgroup'] = 'border border-light';
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
$tabla = $config['tabla'];
$campos = $config['campos'];
$botones = $config['botones'];
$nombre = $config['nombre'];
if (isset($config['relaciones'])) {
    $relaciones = $config['relaciones'];
}
$identificador = $config['id'];
?>
<div class="card border-dark">
    <h2 class='card-header text-center'>{{ $registro->{$nombre} }}</h2>
    <div class="card-body">

        <?php
        foreach ($campos as $columna => $datos) {
            if (isset($datos['pre_html'])) {
                echo $datos['pre_html'];
            }
            ?>
            <div class="form-group row {{$config['class_formgroup']}}">
                <?php if (CrudGenerator::inside_array($datos, "hide", "show") === false) { ?>
                    <div class='{{$config['class_labelcont']}}'>
                        {{ Form::label($columna, ucfirst($datos['label']), ['class'=>$config['class_label']]) }}
                        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna }}_help">
                            <?php
                            if (isset($datos['description'])) {
                                echo $datos['description'];
                            }
                            ?>
                        </small>
                    </div>

                    <div class="{{ $config['class_divinput'] }}" id="{{$tabla . "_" . $columna}}_container">
                        <div class="form-control-plaintext {{$config['class_input']}}">
                            <?php
                            if (isset($datos["pre"])) {
                                echo $datos["pre"];
                            }
                            if ($datos['tipo'] == "files") {
                                try {
                                    $auxprevios = json_decode($registro->{$columna});
                                    if (!is_array($auxprevios)) {
                                        $auxprevios = [];
                                    }
                                } catch (Exception $ex) {
                                    $auxprevios = [];
                                }
                                if (count($auxprevios) == 0) {
                                    echo "-";
                                } else {
                                    ?>
                                    <div class="row">
                                        <?php
                                        foreach ($auxprevios as $datoReg) {
                                            if (is_object($datoReg)) {
                                                ?>
                                                <div class="col-md-6 col-sm-12 col-xs-12">
                                                    <?php
                                                    $filename = \Illuminate\Support\Str::start($datoReg->file, \Illuminate\Support\Str::finish($datos['path'], '\\'));
                                                    $tipoFile = CrudGenerator::filenameIs($datoReg->file, $datos);
                                                    if ($tipoFile == 'image') {
                                                        ?>
                                                        <div class="card text-center">
                                                            <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                                                                <img class="card-img-top" src="{{ route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}">
                                                            </a>
                                                            <div class="card-body" >
                                                                <h5 class="card-title">{{$datoReg->name}}</h5>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    } elseif ($tipoFile == 'video') {
                                                        ?>
                                                        <div class="card text-center" >
                                                            <video class="card-img-top" controls preload="auto" height="300" >
                                                                <source src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" type="video/mp4" />
                                                            </video>
                                                            <div class="card-body" >
                                                                <h5 class="card-title">{{$datoReg->name}}</h5>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    } elseif ($tipoFile == 'audio') {
                                                        ?>
                                                        <div class="card text-center" >
                                                            <audio class="card-img-top" controls preload="auto" >
                                                                <source src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" type="audio/mpeg" />
                                                            </audio>
                                                            <div class="card-body" >
                                                                <h5 class="card-title">{{$datoReg->name}}</h5>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    } elseif ($tipoFile == 'pdf') {
                                                        ?>
                                                        <div class="card text-center" >
                                                            <iframe class="card-img-top" height="300" src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" style="border: none;"></iframe>
                                                            <div clas="card-body" >
                                                                <h5 class="card-title">{{$datoReg->name}}</h5>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <div class="card text-center" >
                                                            <div class="card-header">
                                                                <?php
                                                                switch ($tipoFile) {
                                                                    case('text'):
                                                                        echo '<i class="fa fa-file-text-o fa-3x" aria-hidden="true"></i>';
                                                                        break;
                                                                    case('office'):
                                                                        echo '<i class="fa fa-file-word-o fa-3x" aria-hidden="true"></i>';
                                                                        break;
                                                                    case('compressed'):
                                                                        echo '<i class="fa fa-file-archive-o fa-3x" aria-hidden="true"></i>';
                                                                        break;
                                                                    case('other'):
                                                                        echo '<i class="fa fa-file-o fa-3x" aria-hidden="true"></i>';
                                                                        break;
                                                                }
                                                                ?>
                                                            </div>
                                                            <div clas="card-body" >
                                                                <h5 class="card-title">
                                                                    <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                                                                        {{$datoReg->name}}
                                                                    </a>
                                                                </h5>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                    <?php
                                }
                            } elseif ($datos['tipo'] == "relationship") {
                                if (CrudGenerator::hasRelation($registro, $columna)) {
                                    if (array_key_exists('enlace', $datos)) {
                                        ?>
                                        <a href="{{ str_replace([":modelId",":modelName"],[$registro->{$columna}->{$datos['id']},$registro->{$columna}->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$registro->{$columna}->{$datos['id']},$registro->{$columna}->{$datos['nombre']}],$datos['enlace'])) }}">
                                            <?php
                                        }
                                        echo CrudGenerator::getNombreDeLista($registro->{$columna}, $datos['campo']);
                                        if (array_key_exists('enlace', $datos)) {
                                            ?>
                                        </a>
                                        <?php
                                    }
                                } else {
                                    echo print_r($registro->{$columna}, true);
                                }
                            } elseif ($datos['tipo'] == "relationships") {
                                if (CrudGenerator::hasRelation($registro, $columna)) {
                                    ?>
                                    <ul>
                                        <?php
                                        foreach ($registro->{$columna}()->get() as $sub) {
                                            ?>
                                            <li>
                                                <?php
                                                if (array_key_exists('enlace', $datos)) {
                                                    ?>
                                                    <a href="{{ str_replace([":modelId",":modelName"],[$sub->{$datos['id']},$sub->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$sub->{$datos['id']},$sub->{$datos['nombre']}],$datos['enlace'])) }}">
                                                        <?php
                                                    }
                                                    echo CrudGenerator::getNombreDeLista($sub, $datos['campo']);
                                                    if (array_key_exists('enlace', $datos)) {
                                                        ?>
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                    <?php
                                } else {

                                }
                            } elseif ($datos['tipo'] == "relationshipssel") {
                                if (CrudGenerator::hasRelation($registro, $columna)) {
                                    ?>
                                    <dl class="row border-top border-secondary">
                                        <?php
                                        foreach ($registro->{$columna}()->get() as $sub) {
                                            ?>
                                            <dt class="col-sm-3 border-bottom border-secondary pt-2">
                                                <?php
                                                if (array_key_exists('enlace', $datos)) {
                                                    ?>
                                                    <a href="{{ str_replace([":modelId",":modelName"],[$sub->{$datos['id']},$sub->{$datos['nombre']}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$sub->{$datos['id']},$sub->{$datos['nombre']}],$datos['enlace'])) }}">
                                                        <?php
                                                    }
                                                    echo CrudGenerator::getNombreDeLista($sub, $datos['campo']);
                                                    if (array_key_exists('enlace', $datos)) {
                                                        ?>
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                            </dt>
                                            <?php
                                            if (array_key_exists('columnas', $datos)) {
                                                if (is_array($datos['columnas'])) {
                                                    if (is_object($sub->pivot)) {
                                                        ?>
                                                        <dd class="col-sm-9 border-bottom border-secondary mb-0 pb-2">
                                                            <ul class="mb-0">
                                                                <?php
                                                                foreach ($datos['columnas'] as $infoPivote) {
                                                                    if ($infoPivote['type'] != "hidden" && $infoPivote['type'] != "label") {
                                                                        ?>
                                                                        <li>
                                                                            <?php
                                                                            if ($infoPivote['type'] == "number" && isset($infoPivote['format'])) {
                                                                                echo number_format($sub->pivot->{$infoPivote['campo']}, $infoPivote['format'][0], $infoPivote['format'][1], $infoPivote['format'][2]);
                                                                            } elseif ($infoPivote['type'] == "select" && isset($infoPivote['opciones'])) {
                                                                                echo $infoPivote['opciones'][$sub->pivot->{$infoPivote['campo']}];
                                                                            } else {
                                                                                echo $sub->pivot->{$infoPivote['campo']} . ', ';
                                                                            }
                                                                            ?>
                                                                        </li>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </ul>
                                                        </dd>
                                                        <?php
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </dl>
                                    <?php
                                } else {

                                }
                            } elseif ($datos['tipo'] == "select") {
                                if (array_key_exists($registro->{$columna}, $datos['opciones'])) {
                                    echo $datos['opciones'][$registro->{$columna}];
                                }
                            } elseif ($datos['tipo'] == "checkbox" || $datos['tipo'] == "radio") {
                                if (is_array($datos['value'])) {
                                    if (array_key_exists($registro->{$columna}, $datos['value'])) {
                                        echo $datos['value'][$registro->{$columna}];
                                    } else {
                                        if ($registro->{$columna} === true) {
                                            echo trans('crudgenerator::admin.layout.labels.yes');
                                        } else {
                                            echo trans('crudgenerator::admin.layout.labels.no');
                                        }
                                    }
                                } else {
                                    if ($datos['value'] == $registro->{$columna} && $registro->{$columna} == true) {
                                        echo trans('crudgenerator::admin.layout.labels.yes');
                                    } elseif ($registro->{$columna} == $datos['value']) {
                                        echo $datos['value'];
                                    } elseif ($registro->{$columna} == true) {
                                        echo $datos['value'];
                                    } else {
                                        echo trans('crudgenerator::admin.layout.labels.no');
                                    }
                                }
                            } elseif ($datos['tipo'] == "date" || $datos['tipo'] == "datetime" || $datos['tipo'] == "time") {

                                $format = "Y-m-d H:i:s";
                                if ($datos['tipo'] == "date") {
                                    $format = "Y-m-d";
                                } elseif ($datos['tipo'] == "time") {
                                    $format = "H:i:s";
                                }
                                if (isset($datos["format"]["carbon"])) {
                                    $format = $datos["format"]["carbon"];
                                } elseif (isset(trans("crudgenerator::admin.formats.carbon")[$datos['tipo']])) {
                                    $format = trans("crudgenerator::admin.formats.carbon." . $datos['tipo']);
                                }
                                $dato = $registro->{$columna};

                                if ($dato != "") {
                                    if (isset($datos["timezone"])) {
                                        $timezone = $datos["timezone"];
                                    } else {
                                        $timezone = config("app.timezone");
                                    }
                                    $date = new \Carbon\Carbon($dato, $timezone);
                                    if (stripos($format, "%")!==false){
                                        setlocale(LC_TIME, App::getLocale());
                                        Carbon\Carbon::setUtf8(true);
                                        $dato = $date->formatLocalized($format);
                                    }else{
                                        $dato = $date->format($format);
                                    }
                                }
                                echo $dato;
                            } elseif ($datos['tipo'] == "function") {
                                if (isset($datos['format'])) {
                                    if (is_array($datos['format'])) {
                                        echo number_format($registro->{$columna}(), $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                                    } else {
                                        echo number_format($registro->{$columna}());
                                    }
                                } else {
                                    echo $registro->{$columna}();
                                }
                            } elseif ($datos['tipo'] == "url") {
                                if (CrudGenerator::urlType($registro->{$columna}) == "youtube") {
                                    $youtubeId = CrudGenerator::getYoutubeId($registro->{$columna});
                                    ?>
                                    <div class="card text-center" >
                                        <iframe class="card-img-top" height="400" src="https://www.youtube.com/embed/{{$youtubeId}}" style="border: none;"></iframe>
                                        <div clas="card-body" >
                                            <h5 class="card-title">{{$registro->{$columna} }}</h5>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <a class='btn' href='{{ $registro->{$columna} }}' target='_blank'><i class="mt-2 fa fa-link fa-lg" aria-hidden="true"></i></a> {{ $registro->{$columna} }}
                                    <?php
                                }
                            } elseif ($datos['tipo'] == "file") {
                                if ($registro->{$columna} == "") {
                                    echo '-';
                                } else {
                                    $auxprevio = $registro->{$columna};
                                    $filename = \Illuminate\Support\Str::start($auxprevio, \Illuminate\Support\Str::finish($datos['path'], '\\'));
                                    $tipoFile = CrudGenerator::filenameIs($auxprevio, $datos);
                                    $auxprevioName = substr($auxprevio, stripos($auxprevio, '__') + 2, stripos($auxprevio, '.', stripos($auxprevio, '__')) - (stripos($auxprevio, '__') + 2));
                                    if ($tipoFile == 'image') {
                                        ?>
                                        <div class="card text-center">
                                            <img class="card-img-top" src="{{ route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}">
                                            <div class="card-body" >
                                                <h5 class="card-title">{{$auxprevioName}}</h5>
                                            </div>
                                        </div>
                                    <?php } elseif ($tipoFile == 'video') { ?>
                                        <div class="card text-center" >
                                            <video class="card-img-top" controls preload="auto" height="300" >
                                                <source src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" type="video/mp4" />
                                            </video>
                                            <div class="card-body" >
                                                <h5 class="card-title">{{$auxprevioName}}</h5>
                                            </div>
                                        </div>
                                    <?php } elseif ($tipoFile == 'audio') { ?>
                                        <div class="card text-center" >
                                            <audio class="card-img-top" controls preload="auto" >
                                                <source src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" type="audio/mpeg" />
                                            </audio>
                                            <div class="card-body" >
                                                <h5 class="card-title">{{$auxprevioName}}</h5>
                                            </div>
                                        </div>
                                    <?php } elseif ($tipoFile == 'pdf') { ?>
                                        <div class="card text-center" >
                                            <iframe class="card-img-top" height="300" src="{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" style="border: none;"></iframe>
                                            <div clas="card-body" >
                                                <h5 class="card-title">{{$auxprevioName}}</h5>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <ul class="fa-ul">
                                            <li class="pl-2">
                                                <?php
                                                switch ($tipoFile) {
                                                    case('text'):
                                                        echo '<i class="fa fa-file-text-o fa-lg fa-li" aria-hidden="true"></i>';
                                                        break;
                                                    case('office'):
                                                        echo '<i class="fa fa-file-word-o fa-lg fa-li" aria-hidden="true"></i>';
                                                        break;
                                                    case('compressed'):
                                                        echo '<i class="fa fa-file-archive-o fa-lg fa-li" aria-hidden="true"></i>';
                                                        break;
                                                    case('other'):
                                                        echo '<i class="fa fa-file-o fa-lg fa-li" aria-hidden="true"></i>';
                                                        break;
                                                }
                                                ?>
                                                <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                                                    {{$auxprevioName}}
                                                </a>
                                            </li>
                                        </ul>
                                        <?php
                                    }
                                }
                            } elseif ($datos['tipo'] == "number" && isset($datos['format'])) {
                                if (is_array($datos['format'])) {
                                    echo number_format($registro->{$columna}, $datos['format'][0], $datos['format'][1], $datos['format'][2]);
                                } else {
                                    echo number_format($registro->{$columna});
                                }
                            } elseif ($datos['tipo'] == "article" && class_exists(config('sirgrimorum.transarticles.default_articles_model'))) {
                                echo $registro->get($columna);
                            } elseif ($datos['tipo'] == "json") {
                                echo '<pre>' . print_r($registro->{$columna},true) . '</pre>';
                            } else {
                                echo $registro->get($columna);
                            }
                            if (isset($datos["post"])) {
                                echo " " . $datos["post"];
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
            if (isset($datos['post_html'])) {
                echo $datos['post_html'];
            }
        }
        ?>

    </div>
</div>
