<?php

return [
	"modelo" => "Sirgrimorum\CrudGenerator\Models\Catchederror", 
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
		return Sirgrimorum\CrudGenerator\Models\Catchederror::where("reportar","1")->orderBy("updated_at", "desc");
	},
	"campos" => [
		"url" => [
			"tipo" => "url",
			"tipos_temporales" => [ // temporary type of fields to be taken just for a specific action ("list", "show", "create", "edit") 
				"create" => "hidden",
			],
			"valor" => "/", 
			"label" => "__trans__crudgenerator::catchederror.labels.url", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.url", 
			"help" => "__trans__crudgenerator::catchederror.descriptions.url",
		],
        "type" => [
			"tipo" => "select",
			"label" => "__trans__crudgenerator::catchederror.labels.type",
			"opciones" => "__trans__crudgenerator::catchederror.selects.type",
			//"readonly" => ["edit"],
			"multiple" => "multiple",
			"description" => "__trans__crudgenerator::catchederror.descriptions.type",
		],
		"exception" => [
			"tipo" => "text", 
			"label" => "__trans__crudgenerator::catchederror.labels.exception", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.exception", 
			"help" => "__trans__crudgenerator::catchederror.descriptions.exception", 
		], 
		"file" => [
			"tipo" => "text", 
			"label" => "__trans__crudgenerator::catchederror.labels.file", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.file", 
			"help" => "__trans__crudgenerator::catchederror.descriptions.file",
			"hide" => ['list', 'show'],
		], 
		"line" => [
			"tipo" => "number", 
			"label" => "__trans__crudgenerator::catchederror.labels.line", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.line", 
			"help" => "__trans__crudgenerator::catchederror.descriptions.line",
			"hide" => ['list', 'show'],
			"format" => [
				"0" => 0,
				"1" => ".",
				"2" => ".",
            ],
		], 
		"message" => [
			"tipo" => "textarea", 
			"tipos_temporales" => [ // temporary type of fields to be taken just for a specific action ("list", "show", "create", "edit") 
				"create" => "hidden",
			],
			"label" => "__trans__crudgenerator::catchederror.labels.message", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.message", 
			"description" => "__trans__crudgenerator::catchederror.descriptions.message",
			"show_data" => "<p><-message.value-></p><p><small><-file.value-> (<-line.value->)</small></p>",
			"valor" => "Not set",
			"list_data" => function($data){
				$mensaje = Sirgrimorum\CrudGenerator\CrudGenerator::truncateText($data['value'],50);
				return "$mensaje <p><small>(<-file.value->: <-line.value->)</small></p>";
			}
		],
		"reportar" => [
			"tipo" => "select", 
			"label" => "__trans__crudgenerator::catchederror.labels.reportar", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.reportar", 
			"description" => "__trans__crudgenerator::catchederror.descriptions.reportar",
			"opciones" => "__trans__crudgenerator::catchederror.selects.reportar",
			"hide" => ["select"],
			"valor" => "0",
			"readonly" => ["create"],
		],
		"occurrences" => [
			"tipo" => "json", 
			"tipos_temporales" => [ // temporary type of fields to be taken just for a specific action ("list", "show", "create", "edit") 
				"create" => "hidden",
			],
			"label" => "__trans__crudgenerator::catchederror.labels.occurrences", 
			"description" => "__trans__crudgenerator::catchederror.descriptions.occurrences",
			"readonly" => "readonly",
			"valor" => "{}",
			"show_data" => function($data){
				dump($data['data']);
				return "";
			},
			"list_data" => "<-occurrences.data.num->"
		], 
		"trace" => [
			"tipo" => "json", 
			"tipos_temporales" => [ // temporary type of fields to be taken just for a specific action ("list", "show", "create", "edit") 
				"create" => "hidden",
			],
			"label" => "__trans__crudgenerator::catchederror.labels.trace",  
			"readonly" => "readonly",
			"valor" => "{}",
			"hide" => ["list"],
			"show_data" => function($data){
				dump($data['data']);
				return "";
			}
		], 
		"request" => [
			"tipo" => "json", 
			"tipos_temporales" => [ // temporary type of fields to be taken just for a specific action ("list", "show", "create", "edit") 
				"create" => "hidden",
			],
			"label" => "__trans__crudgenerator::catchederror.labels.request", 
			"description" => "__trans__crudgenerator::catchederror.descriptions.request",
			"readonly" => "readonly",
			"valor" => "{}",
			"hide" => ["list"],
			"show_data" => function($data){
				dump($data['data']);
				return "";
			}
		],
	], 
	"rules" => [
		"url" => "bail|required", 
		"type" => "bail|required", 
		"exception" => "bail|required", 
		"file" => "bail|required", 
		"message" => "bail|required", 
		"occurrences" => "bail|required", 
		"trace" => "bail|required", 
		"request" => "bail|required", 
		"reportar" => "bail|required", 
	], 
	"permissions" => [ //the permissions to validate before doing an action, if not present, uses the "sirgrimorum_cms::permission" closure, false send back to the 'sirgrimorum_cms::login_path' 
        "default" => function() {
            if (auth()->check()){
                $user = App\User::find(auth()->user()->id);
                return $user->isSupeAdmin();
            }
            return false;
        }, // the default permission to validate if others not present, false send back to the 'sirgrimorum_cms::login_path' 
		"create" => function(){
			return true;
		},
		"edit" => function(){
			return false;
		},
		"update" => function(){
			return false;
		},
		/* "index" => [closure that return true or false], // permission for the index action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
      "show" => [closure($object) that return true or false], // permission for the show action of Crud, false send back to the 'sirgrimorum_cms::login_path'
      "destroy" => [closure($object) that return true or false], // permission for the delete action of Crud, false send back to the 'sirgrimorum_cms::login_path' */
    ],
];