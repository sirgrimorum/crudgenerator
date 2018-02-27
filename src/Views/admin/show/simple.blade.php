

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.create");
if ($config==""){
    $config = CrudLoader::getConfig($modelo);
}

?>
    {!! CrudLoader::show($config,$registro,true) !!}
