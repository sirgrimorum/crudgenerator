<h2 class='card-header text-center'>
    @if (isset($registro[$nombre]))
    @if (is_array($registro[$nombre]) && isset($registro[$nombre]['value']))
    {!! $registro[$nombre]['value'] !!}
    @elseif (is_array($registro[$nombre]) && !array_key_exists('value',$registro[$nombre]))
    <pre>{!! print_r($registro[$nombre],true) !!}</pre>
    @elseif(!is_array($registro[$nombre]))
    {!! $registro[$nombre] !!}
    @else
    -
    @endif
    @else
    -
    @endif
</h2>
    <div class="card-body">
        @foreach ($campos as $columna => $datos)
        @if (isset($datos['pre_html']))
        {!! $datos['pre_html'] !!}
        @endif
        <div class="form-group row {{$config['class_formgroup']}}">
            @if (CrudGenerator::inside_array($datos, "hide", "show") === false)
            <div class='{{$config['class_labelcont']}}'>
                {{ Form::label($columna, ucfirst($datos['label']), ['class'=>$config['class_label']]) }}
                <small class="form-text text-muted mt-0" id="{{ $tabla . '_' . $columna }}_help">
                    @if (isset($datos['description']))
                        {!! $datos['description'] !!}
                    @endif
                </small>
            </div>
            <div class="{{ $config['class_divinput'] }}" id="{{$tabla . "_" . $columna}}_container">
                <div class="form-control-plaintext {{$config['class_input']}}">
                    @if (isset($registro[$columna]))
                    @if (is_array($registro[$columna]))
                    {!! CrudGenerator::getDatoToShow($registro[$columna], "show", $datos) !!}
                    @elseif(!is_array($registro[$columna]))
                    {!! $registro[$columna] !!}
                    @else
                    -
                    @endif
                    @else
                    -
                    @endif
                </div>
            </div>
            @else
            @endif
        </div>
        @if (isset($datos['post_html']))
        {!! $datos['post_html'] !!}
        @endif
        @endforeach
    </div>