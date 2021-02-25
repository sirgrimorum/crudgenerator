@if (!class_exists(config('sirgrimorum.transarticles.default_articles_model')))
@include("sirgrimorum::crudgen.templates.html", ['datos'=>$datos,'js_section'=>$js_section,'css_section'=>$css_section, 'modelo'=>$modelo, 'action'=>$action])
@else
<?php
if (!isset($datos['es_html'])){
    $datos['es_html']=true;
}
if (isset($datos['extraId'])) {
    $extraId = $datos['extraId'];
} else {
    $extraId = $columna;
}
$dato = old($extraId);
if ($dato == "" && $registro != null) {
    try {
        $dato = [];
        $modelClass = config('sirgrimorum.transarticles.default_articles_model');
        $langColumn = config('sirgrimorum.transarticles.default_lang_column');
        $findArticle = config('sirgrimorum.transarticles.default_findarticle_function_name');
        foreach (config("sirgrimorum.crudgenerator.list_locales") as $localeCode) {
            $articles = $modelClass::{$findArticle}($datos['scope'] . "." . $registro->getKey())->where($langColumn, "=", $localeCode)->first();
            if (isset($articles)) {
                $dato[$localeCode] = $articles->content;
            } else {
                if (isset($datos["valor"])) {
                    if (is_array($datos["valor"])) {
                        if (isset($datos["valor"][$localeCode])) {
                            $dato[$localeCode] = $datos["valor"][$localeCode];
                        } else {
                            $dato[$localeCode] = "";
                        }
                    } else {
                        $dato[$localeCode] = $datos["valor"];
                    }
                } else {
                    $dato[$localeCode] = "";
                }
            }
            //$dato[$localeCode] = \Sirgrimorum\TransArticles::get($datos['scope'] . "." . $registro->getKey());
        }
    } catch (Exception $ex) {
        $dato = "";
    }
}
if ($dato == "") {
    if (isset($datos["valor"])) {
        $dato = $datos["valor"];
    }
}
$error_campo = false;
$claseError = '';
if ($errores == true) {
    if ($errors->has($extraId)) {
        $error_campo = true;
        $claseError = 'is-invalid';
    } else {
        $claseError = 'is-valid';
    }
}
if (isset($datos["readonly"])) {
    $readonly = $datos["readonly"];
} else {
    $readonly = "";
}
$extraClassDiv = \Illuminate\Support\Arr::get($datos, 'extraClassDiv', "");
$extraClassInput = \Illuminate\Support\Arr::get($datos, 'extraClassInput', "");
$extraDataInput = \Illuminate\Support\Arr::get($datos, 'extraDataInput', []);
$help = \Illuminate\Support\Arr::get($datos, 'help', "");
?>
<div class="form-group row {{$config['class_formgroup']}} {{ $extraClassDiv }}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}">
        <ul class="nav nav-pills mb-3" id="{{$tabla . '_' . $extraId . '_nav'}}" role="tablist">
            @foreach(config("sirgrimorum.crudgenerator.list_locales") as $localeCode)
            <li class="nav-item">
                <a class="nav-link {{ ($loop->first) ? 'active': ''}}" id="{{$tabla . '_' . $extraId . '_nav_' . $localeCode}}" data-toggle="tab" href="#{{$tabla . '_' . $extraId . '_tab_' . $localeCode}}" role="tab" aria-controls="{{$localeCode}}" aria-selected="true">{{ trans('crudgenerator::admin.layout.labels.'.$localeCode) }}</a>
            </li>
            @endforeach
        </ul>
        <div class="tab-content {{ $claseError }}" id="{{$tabla . '_' . $extraId . '_tabContent_' . $localeCode}}">
            @foreach(config("sirgrimorum.crudgenerator.list_locales") as $localeCode)
            <div class="tab-pane fade {{ ($loop->first) ? 'show active': ''}}" id="{{$tabla . '_' . $extraId . '_tab_' . $localeCode}}" role="tabpanel" aria-labelledby="{{$tabla . '_' . $extraId . '_nav_' . $localeCode}}">
                @if(is_array($dato))
                {{ Form::textarea($extraId. "[" . $localeCode ."]", $dato[$localeCode], array_merge(
                    $extraDataInput,
                    ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId . "_" . $localeCode,$readonly])) }}
                @else
                {{ Form::textarea($extraId. "[" . $localeCode ."]", $dato, array_merge(
                    $extraDataInput,
                    ['class' => "form-control {$config['class_input']} $claseError $extraClassInput", 'id' => $tabla . '_' . $extraId . "_" . $localeCode,$readonly])) }}
                @endif
            </div>
            @endforeach
        </div>
        @if ($error_campo)
        <div class="invalid-feedback">
            {{ $errors->get($extraId)[0] }}
        </div>
        @endif
        @if($help != "")
        <small class="form-text text-muted mt-0">
            {{ $help }}
        </small>
        @endif
    </div>
</div>
@if ($datos['es_html']==true)
<?php
if ($js_section != "") {
    ?>
    @push($js_section)
    <?php
}
$nameScriptLoader = config("sirgrimorum.crudgenerator.scriptLoader_name","scriptLoader") . "Creator";
?>
<script>
    var {{ $tabla . "_" . $extraId }}Ejecutado = false;
    function {{ $tabla . "_" . $extraId }}Loader(){
        if (!{{ $tabla . "_" . $extraId }}Ejecutado){
            @foreach(config("sirgrimorum.crudgenerator.list_locales") as $localeCode)
            CKEDITOR.replace('{{ $tabla . "_" . $extraId . "_" . $localeCode }}');
            @endforeach
        }
        {{ $tabla . "_" . $extraId }}Ejecutado = true;
    }
    window.addEventListener('load', function() {
        {{ $tabla . "_" . $extraId }}Loader();
    });
    {{ $nameScriptLoader }}('ckeditor_js',"{{ $tabla . "_" . $extraId }}Loader();");
</script>
<?php
if ($js_section != "") {
    ?>
    @endpush
    <?php
}
?>
@endif
@endif
