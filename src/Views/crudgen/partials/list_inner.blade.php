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
        @if (is_array($value[$columna]))
        {!! CrudGenerator::getDatoToShow($value[$columna], "list", $datos) !!}
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