<?php

return [
	"modelo" => "Sirgrimorum\CrudGenerator\Models\Catchederror", 
	"botones" => [
		'reportados' => [ // Aditional buttons to use in lists, The show, edit, remove or create ones must bue only strings
			"title" => "__trans__crudgenerator::catchederror.labels.show_reported",
			"text" => "<i class='fas fa-viruses fa-lg' aria-hidden='true'></i>",
			"extendSelected" => false,
			"class" => "btn btn-warning",
			"callback" => "function(idsSelected, namesSelected, rowsSelected){
				var params = {
					'modelo':'catchederror',
					'config':'sirgrimorum.models.catchederror',
					'smartMerge':false,
				};
				window.location.href = window.location.pathname+'?__parametros=' + encodeURIComponent(JSON.stringify(params));
			}", // Will be called with 3 arguments: idsSelected (int|string), namesSelected (string), rowsSelected (object)
		],
		'reportar' => [ // Aditional buttons to use in lists, The show, edit, remove or create ones must bue only strings
			"title" => "__trans__crudgenerator::catchederror.labels.bt_report",
			"text" => "<i class='far fa-eye fa-lg' aria-hidden='true'></i>",
			"extendSelected" => true,
			"class" => "btn btn-success",
			"callback" => "function(idsSelected, namesSelected, rowsSelected){
				form_string = '<form method=\'POST\' action=\'' + ulr_reportar.replace(':idSelected',idsSelected[0]) + '\' accept-charset=\'UTF-8\'>'
				form_string = form_string + '<input name=\'catchederror\' type=\'hidden\' value=\'' + idsSelected[0] + '\'>';
				form_string = form_string + '<input name=\'_token\' type=\'hidden\' value=\'' + $('meta[name=csrf-token]').attr('content') + '\'>';
                form_string = form_string + '</form>';
                form = $(form_string);
				form.appendTo('body');
                form.submit();
			}", // Will be called with 3 arguments: idsSelected (int|string), namesSelected (string), rowsSelected (object)
		],
		'no_reportados' => 'notThisTime',
		'no_reportar' => 'notThisTime',
	], 
	"js_vars" => [
		"ulr_reportar" => "__route__sirgrimorum_errorcatcher::report,{'catchederror':':idSelected'}__",
		'ulr_no_reportar' => 'notThisTime',
	],
	"query" => function(){
		return Sirgrimorum\CrudGenerator\Models\Catchederror::where("reportar","0")->orderBy("updated_at", "desc");
	},
];