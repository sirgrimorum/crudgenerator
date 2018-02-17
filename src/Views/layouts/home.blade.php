@extends("sirgrimorum::layouts.includes.principal")

@section("contenido")
<div class="welcome">
    <h1>{{ trans('crudgenerator::admin.layout.hola') }}</h1>
</div>
@stop

@section("selfjs")
<script>
    $(document).ready(function() {
        
    });
</script>
@stop
