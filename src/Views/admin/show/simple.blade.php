

<?php
//$config = config(config("sirgrimorum.crudgenerator.admin_routes." . $modelo));
//$config['botones'] = trans("crudgenerator::article.labels.create");
if ($config==""){
    $config = CrudGenerator::getConfig($modelo);
}elseif(is_string($config)){
    $config = CrudGenerator::getConfig($modelo, false, $config);
}

?>
    {!! CrudGenerator::show($config,$registro,true) !!}
