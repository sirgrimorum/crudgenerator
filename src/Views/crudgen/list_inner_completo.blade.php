@foreach($registros as $key => $value)
<tr id = "{{ $tablaid }}__{{ $value->{$config['id']} }}|{!! $value->{$config['nombre']} !!}">
    @foreach($campos as $columna => $datos)
    @if (CrudGenerator::inside_array($datos,"hide","list")===false)
    <td class="position-relative">
        @if (isset($datos["pre"]))
        {!! $datos["pre"] !!}
        @endif
        @if ($datos['tipo']=="relationship")
        @if (CrudGenerator::hasRelation($value,$columna))
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId", ":modelName"],[$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")],[$value->{$columna}->{$datos['id']}, $value->{$columna}->{$datos['nombre']}],$datos['enlace'])) }}">
            @endif
            @if($value->{$columna})
            {!! CrudGenerator::getNombreDeLista($value->{$columna}, $datos['campo']) !!}
            @else
            -
            @endif
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        @else
        -
        @endif
        @elseif ($datos['tipo']=="relationships")
        @if (CrudGenerator::hasRelation($value, $columna))
        <ul>
        @foreach($value->{$columna}()->get() as $sub)
        <li>
            @if(array_key_exists('enlace',$datos))
            <a href="{{ str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],$datos['enlace'])) }}">
                @endif
                {!! CrudGenerator::getNombreDeLista($sub, $datos['campo']) !!}
                @if(array_key_exists('enlace',$datos))
            </a>
            @endif
        </li>
        @endforeach
        </ul>
        @else
        -
        @endif
        @elseif ($datos['tipo']=="relationshipssel")
        @if (CrudGenerator::hasRelation($value, $columna))
        <ul>
        @foreach($value->{$columna}()->get() as $sub)
        <li>
            @if(array_key_exists('enlace',$datos))
            <a href="{{ str_replace([":modelId", ":modelName"], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],str_replace([urlencode(":modelId"), urlencode(":modelName")], [$sub->{$datos['id']}, $sub->{$datos['nombre']}],$datos['enlace'])) }}">
                @endif
                {!! CrudGenerator::getNombreDeLista($sub, $datos['campo']) !!}
                @if(array_key_exists('enlace',$datos))
            </a>
            @endif
                @if(array_key_exists('columnas',$datos))
                    @if(is_array($datos['columnas']))
                        @if (is_object($sub->pivot))
                        <ul>
                        @foreach($datos['columnas'] as $infoPivote)
                            @if($infoPivote['type'] != "hidden" && $infoPivote['type'] != "label")
                            <li>
                                @if ($infoPivote['type'] == "number" && isset($infoPivote['format']))
                                {!! number_format($sub->pivot->{$infoPivote['campo']},$infoPivote['format'][0],$infoPivote['format'][1],$infoPivote['format'][2]) !!}
                                @elseif ($infoPivote['type'] == "select" && isset($infoPivote['opciones']))
                                {!! $infoPivote['opciones'][$sub->pivot->{$infoPivote['campo']}] !!}
                                @else
                                {!! $sub->pivot->{$infoPivote['campo']} !!},
                                @endif
                            </li>
                            @endif
                        @endforeach
                        </ul>
                        @endif
                    @endif
                @endif
        </li>
        @endforeach
        </ul>
        @else
        -
        @endif
        @elseif ($datos['tipo']=="select")
        @if (array_key_exists($value->{$columna},$datos['opciones']))
        {!! $datos['opciones'][$value->{$columna}] !!}
        @else
        -
        @endif
        @elseif ($datos['tipo']=="checkbox" || $datos['tipo']=="radio")
        @if (is_array($datos['value']))
        @if (array_key_exists($value->{$columna},$datos['value']))
        {!! $datos['value'][$value->{$columna}] !!}
        @else
        @if($value->{$columna}===true)
        {{trans('crudgenerator::admin.layout.labels.yes')}}
        @else
        {{trans('crudgenerator::admin.layout.labels.no')}}
        @endif
        @endif
        @else
        @if ($datos['value']==$value->{$columna} && $value->{$columna} ==true)
        {{trans('crudgenerator::admin.layout.labels.yes')}}
        @elseif($value->{$columna}==$datos['value'])
        {!! $datos['value'] !!}
        @elseif ($value->{$columna}==true)
        {!! $datos['value'] !!}
        @else
        {{trans('crudgenerator::admin.layout.labels.no')}}
        @endif
        @endif
        @elseif($datos['tipo']=="date" || $datos['tipo']=="datetime" || $datos['tipo']=="time")
        <?php
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
        $dato = $value->{$columna};

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
        ?>
        @elseif ($datos['tipo']=="function")
        @if (isset($datos['format']))
        @if (is_array($datos['format']))
        {{ number_format($value->{$columna}(),$datos['format'][0],$datos['format'][1],$datos['format'][2]) }}
        @else
        {{ number_format($value->{$columna}()) }}
        @endif
        @else
        {!! $value->{$columna}() !!}
        @endif
        @elseif ($datos['tipo']=="article" && class_exists(config('sirgrimorum.transarticles.default_articles_model')))
        <div style="max-height:200px;overflow-y:scroll;">
            {!! $value->get($columna) !!}
        </div>
        @elseif ($datos['tipo']=="json")
        <div style="max-height:200px;overflow-y:scroll;">
            <pre>{!!print_r($value->{$columna},true)!!}</pre>
        </div>
        @elseif ($datos['tipo']=="url")
        <a href='{{ $value->{$columna} }}' target='_blank'><i class="mt-2 {{ CrudGenerator::getIcon('url') }}" aria-hidden="true"></i></a>
        @elseif ($datos['tipo']=="file")
        @if ($value->{$columna} == "" )
        -
        @else
        <?php
        $auxprevio = $value->{$columna};
        $filename = \Illuminate\Support\Str::start($auxprevio, \Illuminate\Support\Str::finish($datos['path'], '\\'));
        $tipoFile = CrudGenerator::filenameIs($auxprevio, $datos);
        $auxprevioName = substr($auxprevio, stripos($auxprevio, '__') + 2, stripos($auxprevio, '.', stripos($auxprevio, '__')) - (stripos($auxprevio, '__') + 2));
        ?>
        @if($tipoFile == 'image')
        <figure class="figure">
            <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                <img src="{{ route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}" class="figure-img img-fluid rounded" alt="{{$auxprevioName}}">
                <figcaption class="figure-caption">{{$auxprevioName}}</figcaption>
            </a>
        </figure>
        @else
        <ul class="fa-ul">
            <li class="pl-2">
                @if ($tipoFile == 'image')
                <i class="{{ CrudGenerator::getIcon('empty') }} fa-li" aria-hidden="true"><img class="w-75 rounded" style="cursor: pointer;" src="{{ route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}"></i>
                @else
                {!! CrudGenerator::getIcon($tipoFile,true,'fa-li') !!}
                @endif
                <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                    {{$auxprevioName}}
                </a>
            </li>
        </ul>
        @endif
        @endif
        @elseif ($datos['tipo']=="files")
        <?php
        try {
            $auxprevios = json_decode($value->{$columna});
            if (!is_array($auxprevios)){
                $auxprevios = [];
            }
        } catch (Exception $ex) {
            $auxprevios = [];
        }
        ?>
        @if (count($auxprevios)==0)
        -
        @else
        <ul class="fa-ul">
        @foreach($auxprevios as $datoReg)
        @if(is_object($datoReg))
        <?php
        $filename = \Illuminate\Support\Str::start($datoReg->file, \Illuminate\Support\Str::finish($datos['path'], '\\'));
        $tipoFile = CrudGenerator::filenameIs($datoReg->file,$datos);
        ?>
            <li class="pl-2">
                @if ($tipoFile == 'image')
                <i class="{{ CrudGenerator::getIcon('empty') }} fa-li" aria-hidden="true"><img class="w-75 rounded" style="cursor: pointer;" src="{{ route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}"></i>
                @else
                {!! CrudGenerator::getIcon($tipoFile,true,'fa-li') !!}
                @endif
                <a class="text-secondary" href='{{route('sirgrimorum_modelo::modelfile',['modelo'=>$modelo,'campo'=>$columna]) . "?_f=" . $filename }}' target="_blank" >
                    {{$datoReg->name}}
                </a>
            </li>
        @endif
        @endforeach
        </ul>
        @endif
        @else
        @if(array_key_exists('enlace',$datos))
        <a href="{{ str_replace([":modelId",":modelName"],[$value->{$identificador},$value->{$nombre}],str_replace([urlencode (":modelId"),urlencode(":modelName")],[$value->{$identificador},$value->{$nombre}],$datos['enlace'])) }}">
            @endif
            @if ($datos['tipo']=="number" && isset($datos['format']))
            @if (is_array($datos['format']))
            {{ number_format($value->{$columna},$datos['format'][0],$datos['format'][1],$datos['format'][2]) }}
            @else
            {{ number_format($value->{$columna}) }}
            @endif
            @elseif($datos['tipo']=="html")
            <div style="max-height:200px;overflow-y:scroll;">
                {!! $value->get($columna) !!}
            </div>
            @else
            {!! CrudGenerator::truncateText($value->get($columna)) !!}
            @endif
            @if(array_key_exists('enlace',$datos))
        </a>
        @endif
        @endif
        @if (isset($datos["post"]))
        {!! " " . $datos["post"] !!}
        @endif
    </td>
    @endif
    @endforeach
</tr>
@endforeach