<?php
$auxprevios = [];
$dato = "";
$datoImg = "";
$previo = old($columna . "_name");
if (!is_array($previo)) {
    $previo = [];
}
try {
    $auxprevios = json_decode($registro->{$columna});
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
    if ($errors->has($columna . ".0")) {
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

<div class="form-group row">
    {{ Form::label($columna, ucfirst($datos['label']), array('class'=>$config['class_label'])) }}
    <div class="{{ $config['class_divinput'] }}" id="{{$tabla . "_" . $columna}}_container">
        @if (isset($datos['description']))
        <small class="form-text text-muted" id="{{ $tabla . '_' . $columna }}_help">
            {{ $datos['description'] }}
        </small>
        @endif
        @foreach($auxprevios as $datoReg)
        @if(is_object($datoReg))
        <?php
        $filename = str_start($datoReg->file, str_finish($datos['path'], '\\'));
        $esImagen =CrudLoader::filenameIsImage($datoReg->file,$datos);
        $error_campo = false;
        $claseError = '';
        ?>
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary">
                    @if($esImagen)
                    <img class="" style="cursor: pointer;" src="{{asset($filename)}}" onclick="toogleImagen(this);">
                    @else
                    <div class="pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    @endif
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($columna . "_namereg[]", $datoReg->name, ['class' => 'form-control ', "required"=>"required"]) }}
            {{ Form::hidden($columna . "_filereg[]", $datoReg->file, ['class' => 'form-control ',]) }}
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'{{$tabla . "_" . $columna}}')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        @if($esImagen)
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="{{asset($filename)}}" data-id="collapseImage">
            </div>
        </div>
        @endif
        @endif
        @endforeach
        <div class="input-group mt-2 mb-0">
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="d-none" style="cursor: pointer;" src="{{asset('img/car_bmw.jpg')}}" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text rounded-left">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($columna . "_name[]", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($columna . "[]", ['class' => 'custom-file-input form-control ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-success" onclick="addFile('{{$tabla . "_" . $columna}}','{{$columna}}')" title="{{trans("crudgenerator::admin.layout.labels.add")}}"><i class="fa fa-plus" aria-hidden="true"></i></button>
            </div>
            @if ($error_campo)
            <div class="invalid-feedback">
                {{ $errors->get($columna . ".0")[0] }}
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
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="d-none" style="cursor: pointer;" src="{{asset('img/car_bmw.jpg')}}" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text($columna . "_name[]", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),"required"=>"required",$readonly]) }}
            <div class="custom-file">
                {{ Form::file($columna . "[]", ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'{{$tabla . "_" . $columna}}')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
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
    <div class="d-none" id="{{$tabla . "_" . $columna}}_clone">
        <div class="input-group mt-2 mb-0" >
            <div class="input-group-prepend">
                <div class="rounded-left border border-secondary d-none">
                    <div class="d-none pl-3 pr-3 h-100 pt-1" style="cursor: default;"><i class="mt-2 fa fa-file-text-o fa-lg" aria-hidden="true"></i></div>
                    <img class="d-none" style="cursor: pointer;" src="{{asset('img/car_bmw.jpg')}}" onclick="toogleImagen(this);">
                </div>
                <div class="input-group-text">{{trans("crudgenerator::admin.layout.labels.file")}}</div>
            </div>
            {{ Form::text("nombre", $dato, ['class' => 'form-control ' . $claseError, 'placeholder'=>trans("crudgenerator::admin.layout.labels.name"),$readonly]) }}
            <div class="custom-file">
                {{ Form::file($columna . "[]", ['class' => 'custom-file-input ' . $claseError, $placeholder, $readonly,"data-toggle"=>"custom-file"]) }}
                <label class="custom-file-label">{{trans("crudgenerator::admin.layout.labels.choose_file")}}</label>
            </div>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger" onclick="removeFile(this,'{{$tabla . "_" . $columna}}')"  title="{{trans("crudgenerator::admin.layout.labels.remove")}}"><i class="fa fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="collapse" data-id="collapseImageCont">
            <div class="card collapse" >
                <img class="card-img-top" src="" data-id="collapseImage">
            </div>
        </div>
    </div>
</div>