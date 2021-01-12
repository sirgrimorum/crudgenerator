@foreach($registros as $key => $value)
<?php
$id = CrudGenerator::getJustValue('id',$value,$config, $key);
$nombre = CrudGenerator::getJustValue('nombre',$value,$config, $key);
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