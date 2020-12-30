<?php
$auxprevios = [];
$dato = "";
$datoImg = "";
if (isset($config["extraId"])) {
    $extraId = $config['extraId'];
} else {
    $extraId = $columna;
}
$previo = old($extraId . "_name");
if (!is_array($previo)) {
    $previo = [];
}
try {
    $auxprevios = json_decode($registro->{$columna});
    if (!is_array($auxprevios)){
        $auxprevios = [];
    }
} catch (Exception $ex) {
    $auxprevios = [];
}
if (count($previo) > 0) {
    $dato = $previo[0];
    array_shift($previo);
}
if ($dato == "") {
    if (isset($datos["valor"])) {
        $dato = $datos["valor"];
    }
}
$error_campo = false;
$claseError = '';
if ($errores == true) {
    if ($errors->has($columna . ".0") || $errors->has($columna)) {
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
if (isset($datos["placeholder"])) {
    $placeholder = $datos['placeholder'];
} else {
    $placeholder = "";
}

?>

<div class="form-group row {{$config['class_formgroup']}}" data-tipo='contenedor-campo' data-campo='{{$tabla . '_' . $extraId}}'>
    <div class='{{$config['class_labelcont']}}'>
        {{ Form::label($extraId, ucfirst($datos['label']), ['class'=>'mb-0 ' . $config['class_label']]) }}
        @if (isset($datos['description']))
        <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $extraId }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
    </div>
    <div class="{{ $config['class_divinput'] }}" id="{{$tabla . "_" . $extraId}}_container">
        @foreach($auxprevios as $datoReg)
        @if(is_object($datoReg))
        <?php
        $pathImage = str_replace([":modelName", ":modelId", ":modelCampo"], [$registro->{$config['nombre']}, $registro->{$config['id']}, $datoReg->file], $datos['pathImage']);
        if (\Illuminate\Support\Str::startsWith(strtolower($pathImage), ["http:", "https:"])){
            $filename = $datoReg->file;
            $urlFile = $pathImage;
        }else{
            $filename = \Illuminate\Support\Str::start($datoReg->file, \Illuminate\Support\Str::finish($pathImage, '\\'));
            $urlFile = route('sirgrimorum_modelo::modelfile', ['modelo' => $modelo, 'campo' => $columna]) . "?_f=" . $filename;
        }
        $tipoFile =CrudGenerator::filenameIs($datoReg->file,$datos);
        $error_campo = false;
        $claseError = '';
        ?>
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary">
                    @if ($tipoFile=='image')
                    <img class="rounded-left " style="cursor: pointer;" src="{{ $urlFile }}" onclick="toogleImagen(this);">
                    @elseif($tipoFile=='video')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="toogleVideo(this);">{!! CrudGenerator::getIcon($tipoFile,true,'mt-2') !!}</div>
                    @elseif($tipoFile=='audio')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="toogleAudio(this);">{!! CrudGenerator::getIcon($tipoFile,true,'mt-2') !!}</div>
                    @elseif($tipoFile=='pdf')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: pointer;" onclick="tooglePdf(this);">{!! CrudGenerator::getIcon($tipoFile,true,'mt-2') !!}</div>
                    @elseif($tipoFile=='other')
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: default;">{!! CrudGenerator::getIcon($tipoFile,true,'mt-2') !!}</div>
                    @else
                    <a class="d-block pl-3 pr-3 h-100 pt-1 text-secondary" href='{{ $urlFile }}' target="_blank" >
                        {!! CrudGenerator::getIcon($tipoFile,true,'mt-2') !!}
                    </a>
                    @endif
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($extraId . "_namereg[]", $datoReg->name, ['class' => 'form-control nombre_file ',  'placeholder'=>trans("crudgenerator::admin.layout.labels.name"), "required"=>"required"]) }}
            {{ Form::hidden($extraId . "_filereg[]", $datoReg->file, ['class' => 'form-control ',]) }}
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'{{$tabla . "_" . $extraId}}')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}">{!! CrudGenerator::getIcon("minus",true) !!}</button>
            </div>
        </div>
        @if($tipoFile =='image')
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="{{ $urlFile }}" data-id="collapseImage">
            </div>
        </div>
        @elseif($tipoFile =='video')
        <div class="collapse" data-id="collapseVideoCont">
            <div class="card collapse" >
                <!--iframe class="card-img-top" src="{{ $urlFile }}" data-id="collapseVideo"></iframe-->
                <video class="card-img-top" controls preload="auto" height="300" >
                    <source src="{{ $urlFile }}" type="video/mp4" />
                </video>
            </div>
        </div>
        @elseif($tipoFile =='audio')
        <div class="collapse" data-id="collapseAudioCont">
            <div class="card collapse" >
                <audio class="card-img-top" controls preload="auto" >
                    <source src="{{ $urlFile }}" type="audio/mpeg" />
                </audio>
            </div>
        </div>
        @elseif($tipoFile =='pdf')
        <div class="collapse" data-id="collapsePdfCont">
            <div class="card collapse" >
                <iframe class="card-img-top" style="min-height: 500px;" src="{{ $urlFile }}" style="border: none;"></iframe>
            </div>
        </div>
        @endif
        @endif
        @endforeach
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;">{!! CrudGenerator::getIcon('file',true,'mt-2') !!}</div>
                    <img class="rounded-left d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text rounded-left">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($extraId . "_name[]", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($extraId . "[]", ['class' => 'custom-file-input form-control ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-success" onclick="addFile('{{$tabla . "_" . $extraId}}','{{$extraId}}')" title="{{trans("crudgenerator::admin.layout.labels.add")}}">{!! CrudGenerator::getIcon('plus',true) !!}</button>
            </div>
            @if ($error_campo)
            <div class="invalid-feedback">
                @if($errors->has($columna . ".0"))
                {{ $errors->get($columna . ".0")[0] }}
                @elseif($errors->has($columna))
                {{ $errors->get($columna)[0] }}
                @endif
            </div>
            @endif
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
        @foreach($previo as $index=>$dato)
        @if($dato!="")
        <?php
        $error_campo = false;
        $claseError = '';
        if ($errores == true) {
            if ($errors->has($columna . "." . ($index + 1))) {
                $error_campo = true;
                $claseError = 'is-invalid';
            } else {
                $claseError = 'is-valid';
            }
        }
        ?>
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;">{!! CrudGenerator::getIcon('file',true,'mt-2') !!}</div>
                    <img class="rounded-left d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($extraId . "_name[]", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),"required"=>"required",$readonly]) }}
            <div class="custom-file">
                {{ Form::file($extraId . "[]", ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'nuevo')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}">{!! CrudGenerator::getIcon('minus',true) !!}</button>
            </div>
            @if ($error_campo)
            <div class="invalid-feedback">
                {{ $errors->get($columna . "." . ($index+1))[0] }}
            </div>
            @endif
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
        @endif
        @endforeach
    </div>
    <div class="d-none" id="{{$tabla . "_" . $extraId}}_clone">
        <div class="input-group mt-2 mb-0" >
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;">{!! CrudGenerator::getIcon('file',true,'mt-2') !!}</div>
                    <img class="rounded-left d-none" style="cursor: pointer;" src="" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text("nombre", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($extraId . "[]", ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'nuevo')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}">{!! CrudGenerator::getIcon('minus',true) !!}</button>
            </div>
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
    </div>
</div>