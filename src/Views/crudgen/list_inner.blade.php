@foreach($registros as $key => $value)
<?php
$id = $key;
if (isset($value[$config['id']])){
    if (is_array($value[$config['id']]) && isset($value[$config['id']]['value'])){
        $id = $value[$config['id']]['value'];
    }elseif (is_array($value[$config['id']])){
        if (count($value[$config['id']])>0){
            $id = $value[$config['id']][0];
        }
    }else{
        $id = $value[$config['id']];
    }
}
$nombre = $key;
if (isset($value[$config['nombre']])){
    if (is_array($value[$config['nombre']]) && isset($value[$config['nombre']]['value'])){
        $nombre = $value[$config['nombre']]['value'];
    }elseif (is_array($value[$config['nombre']])){
        if (count($value[$config['nombre']])>0){
            $nombre = $value[$config['nombre']][0];
        }
    }else{
        $nombre = $value[$config['nombre']];
    }
}
?>
<tr id = "{{ $tablaid }}__{{ $id }}|{!! $nombre !!}">
    @foreach($campos as $columna => $datos)
    @if (CrudGenerator::inside_array($datos,"hide","list")===false)
    <td class="position-relative">
        @if (isset($value[$columna]))
        @if (is_array($value[$columna]) && isset($value[$columna]['html_cell']))
        {!! $value[$columna]['html_cell'] !!}
        @elseif (is_array($value[$columna]) && isset($value[$columna]['html_show']))
        {!! $value[$columna]['html_show'] !!}
        @elseif (is_array($value[$columna]) && isset($value[$columna]['html']))
        {!! $value[$columna]['html'] !!}
        @elseif (is_array($value[$columna]) && isset($value[$columna]['value']))
        {!! $value[$columna]['value'] !!}
        @elseif (is_array($value[$columna]) && !array_key_exists('value',$value[$columna]))
        <pre>{!! print_r($value[$columna],true) !!}</pre>
        @elseif(!is_array($value[$columna]))
        {!! $value[$columna] !!}
        @else
        -
        @endif
        @else
        -
        @endif
    </td>
    @endif
    @endforeach
</tr>
@endforeach