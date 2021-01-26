<?php

return [
	"modelo" => "Sirgrimorum\CrudGenerator\Models\CatchedErrors", 
	"tabla" => "catched_errors", 
	"nombre" => "id", 
	"id" => "id", 
	"url" => "Sirgrimorum_CrudAdministrator", 
	"botones" => "__trans__crudgenerator::admin.layout.labels.create", 
	"files" => true, 
	"icono" => "<i class='fa fa-bug mr-1'></i>",
	"ajax" => false,
	"serverSide" => false,
	"conditions" => true,
	"filters" => false,
	"query" => function(){
		return Sirgrimorum\CrudGenerator\Models\CatchedErrors::whereRaw("1=1")->orderBy("updated_at", "desc");
	},
	"campos" => [
        "type" => [
			"tipo" => "select",
			"label" => "__trans__crudgenerator::catchedError.labels.type",
			"opciones" => "__trans__crudgenerator::catchedError.selects.type",
			//"readonly" => ["edit"],
			"description" => "__trans__crudgenerator::catchedError.descriptions.interval",
		],
		"exception" => [
			"tipo" => "text", 
			"label" => "__trans__crudgenerator::catchedError.labels.exception", 
			"placeholder" => "__trans__crudgenerator::catchedError.placeholders.exception", 
			"help" => "__trans__crudgenerator::catchedError.descriptions.exception", 
		], 
		"file" => [
			"tipo" => "text", 
			"label" => "__trans__crudgenerator::catchedError.labels.file", 
			"placeholder" => "__trans__crudgenerator::catchedError.placeholders.file", 
			"help" => "__trans__crudgenerator::catchedError.descriptions.file",
			"hide" => ['list', 'show'],
		], 
		"line" => [
			"tipo" => "number", 
			"label" => "__trans__crudgenerator::catchedError.labels.line", 
			"placeholder" => "__trans__crudgenerator::catchedError.placeholders.line", 
			"help" => "__trans__crudgenerator::catchedError.descriptions.line",
			"hide" => ['list', 'show'],
			"format" => [
				"0" => 0,
				"1" => ".",
				"2" => ".",
            ],
		], 
		"message" => [
			"tipo" => "textarea", 
			"label" => "__trans__crudgenerator::catchedError.labels.message", 
			"placeholder" => "__trans__crudgenerator::catchedError.placeholders.message", 
			"description" => "__trans__crudgenerator::catchedError.descriptions.message",
			"show_data" => "<p><-message-></p><p><small><-file->: <-line-></small></p>",
			"list_data" => function($data){
				$mensaje = Sirgrimorum\CrudGenerator\CrudGenerator::truncateText($data['value'],50);
				return "$mensaje <p><small>(<-file->: <-line->)</small></p>";
			}
		], 
		"occurrences" => [
			"tipo" => "json", 
			"label" => "__trans__crudgenerator::catchedError.labels.occurrences", 
			"description" => "__trans__crudgenerator::catchedError.descriptions.occurrences",
			"readonly" => "readonly",
			"valor" => "{}",
		], 
		"trace" => [
			"tipo" => "json", 
			"label" => "__trans__crudgenerator::catchedError.labels.trace",  
			"readonly" => "readonly",
			"valor" => "{}",
		], 
		"request" => [
			"tipo" => "json", 
			"label" => "__trans__crudgenerator::catchedError.labels.request", 
			"description" => "__trans__crudgenerator::catchedError.descriptions.request",
			"readonly" => "readonly",
			"valor" => "{}",
		],
	], 
	"rules" => [
		"name" => "bail|max:150|required", 
		"link" => "bail|with_articles", 
		"menu" => "bail|with_articles", 
		"template" => "bail|with_articles", 
		"order" => "bail|required", 
		"sections" => "bail|exists:sections,id", 
	], 
	"permissions" => [ //the permissions to validate before doing an action, if not present, uses the "sirgrimorum_cms::permission" closure, false send back to the 'sirgrimorum_cms::login_path' 
        "default" => function() {
            if (auth()->check()){
                $user = App\User::find(auth()->user()->id);
                return $user->isSupeAdmin();
            }
            return false;
        }, // the default permission to validate if others not present, false send back to the 'sirgrimorum_cms::login_path' 
    /* "index" => [closure that return true or false], // permission for the index action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
      "create" => [closure that return true or false], // permission for the create action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "show" => [closure($object) that return true or false], // permission for the show action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "edit" => [closure($object) that return true or false], // permission for the edit action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "update" => [closure($object) that return true or false], // permission for the update action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "destroy" => [closure($object) that return true or false], // permission for the delete action of Crud, false send back to the 'sirgrimorum_cms::login_path' */
    ],
];