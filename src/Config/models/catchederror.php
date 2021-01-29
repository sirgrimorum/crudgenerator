<?php

return [
	"modelo" => "Sirgrimorum\CrudGenerator\Models\Catchederror", 
	"tabla" => "catched_errors", 
	"nombre" => "id", 
	"id" => "id", 
	"url" => "Sirgrimorum_CrudAdministrator", 
	"botones" => [
		'no_reportados' => [ // Aditional buttons to use in lists, The show, edit, remove or create ones must bue only strings
			"title" => "__trans__crudgenerator::catchederror.labels.show_not_reported",
			"text" => "<i class='fas fa-virus-slash fa-lg' aria-hidden='true'></i>",
			"extendSelected" => false,
			"class" => "btn btn-warning",
			"callback" => "function(idsSelected, namesSelected, rowsSelected){
				var params = {
					'modelo':'catchederror',
					'config':'sirgrimorum.models.catchederror_hidden',
					'baseConfig':'sirgrimorum.models.catchederror',
					'smartMerge':true,
				};
				window.location.href = window.location.pathname+'?__parametros=' + encodeURIComponent(JSON.stringify(params));
			}", // Will be called with 3 arguments: idsSelected (int|string), namesSelected (string), rowsSelected (object)
		],
		'no_reportar' => [ // Aditional buttons to use in lists, The show, edit, remove or create ones must bue only strings
			"title" => "__trans__crudgenerator::catchederror.labels.bt_no_report",
			"text" => "<i class='fas fa-archive fa-lg' aria-hidden='true'></i>",
			"extendSelected" => true,
			"class" => "btn btn-danger",
			"callback" => "function(idsSelected, namesSelected, rowsSelected){
				form_string = '<form method=\'POST\' action=\'' + ulr_no_reportar.replace(':idSelected',idsSelected[0]) + '\' accept-charset=\'UTF-8\'>'
				form_string = form_string + '<input name=\'catchederror\' type=\'hidden\' value=\'' + idsSelected[0] + '\'>';
				form_string = form_string + '<input name=\'_token\' type=\'hidden\' value=\'' + $('meta[name=csrf-token]').attr('content') + '\'>';
                form_string = form_string + '</form>';
                form = $(form_string);
				form.appendTo('body');
                form.submit();
			}", // Will be called with 3 arguments: idsSelected (int|string), namesSelected (string), rowsSelected (object)
		],
	], 
	"js_vars" => [
		"ulr_no_reportar" => "__route__sirgrimorum_errorcatcher::no_report,{'catchederror':':idSelected'}__",
	],
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
				$mensaje = Sirgrimorum\CrudGenerator\CrudGenerator::truncateText($data['value'],80);
				return "$mensaje <p><small>(<-file.value->: <-line.value->)</small></p>";
			}
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
		"reportar" => [
			"tipo" => "select", 
			"label" => "__trans__crudgenerator::catchederror.labels.reportar", 
			"placeholder" => "__trans__crudgenerator::catchederror.placeholders.reportar", 
			"description" => "__trans__crudgenerator::catchederror.descriptions.reportar",
			"opciones" => "__trans__crudgenerator::catchederror.selects.reportar",
			"hide" => ["list"],
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
		"created_at" => [
			"tipo" => "datetime",
			"label" => "__trans__crudgenerator::catchederror.labels.created_at",
			"placeholder" => "",
			"format" => [
				"carbon" => "__trans__crudgenerator::admin.formats.carbon.datetime",
				"moment" => "__trans__crudgenerator::admin.formats.moment.datetime",
			],
			"nodb" => "nodb",
			"hide" => ["list"],
		],
		"updated_at" => [
			"tipo" => "datetime",
			"label" => "__trans__crudgenerator::catchederror.labels.updated_at",
			"placeholder" => "",
			"format" => [
				"carbon" => "__trans__crudgenerator::admin.formats.carbon.datetime",
				"moment" => "__trans__crudgenerator::admin.formats.moment.datetime",
			],
			"nodb" => "nodb",
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
	"show_button_list" => [ // if the buttons sould be shown in lists, default is true, remember to use request() ans auth() helpers if needed
		"create" => true, 
		"edit" => function(){return false;},
		"remove" => true,
		"no_reportados" => true,
	],
	"permissions" => [ //the permissions to validate before doing an action, if not present, uses the "sirgrimorum_cms::permission" closure, false send back to the 'sirgrimorum_cms::login_path' 
        "default" => function() {
            if (auth()->check()){
                $user = App\User::find(auth()->user()->id);
                return $user->isSupeAdmin();
            }
            return false;
        }, // the default permission to validate if others not present, false send back to the 'sirgrimorum_cms::login_path' 
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