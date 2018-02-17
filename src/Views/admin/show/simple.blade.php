
@if (Session::has('message'))
<div class="alert alert-info">{{ Session::pull('message') }}</div>
@endif
<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
$config['botones'] = trans("crudgenerator::article.labels.create");
?>
    {!! CrudLoader::show($config,$registro) !!}
