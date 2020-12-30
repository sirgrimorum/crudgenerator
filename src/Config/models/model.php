<?php
/*
 * Configuration file for the article model to use in the crud generator of Sirgrimorum/Cms
 * For smart merge, set the value to "notThisTime" to CrudLoader to remove this attribute or field
 * structure:
            "modelo" => "[Model object]",
            "tabla" => "[table name]",
            "nombre" => "[attribute taken as name for the model]",
            "id" => "[id field name]",
            "url" => "[url to use as action for the form]", // use "Sirgrimorum_CrudAdministrator" if you want the crude gen to manage it, change if you want to handle it diferently
            "botones" => "[text or html of the submit button of the form or array of buttons for lists]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.labels.create__", use route_prefix or url_prefix y json notation to use route or url function, ej: "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId__" ,  use :modelName or :modelId to change it for the corresponding name or id Overriden if url is Sirgrimorum_CrudAdministrator, for lists, set an array using for keys the actions ('create','show','edit','remove') and for values the urls for each ones or the <a
            "botones" => [ //option A for lists using <a tag to load Button options
                'show' => "<a class='btn btn-info' href='__route__sirgrimorum_modelo::show,{'localecode':'__getLocale__','modelo':'article','registro':':modelId'}__' title='__trans__crudgenerator::datatables.buttons.t_show__ Article'>__trans__crudgenerator::datatables.buttons.show__</a>",
                'edit' => "<a class='btn btn-success' href='__route__sirgrimorum_modelo::edit,{'localecode':'__getLocale__','modelo':'article','registro':':modelId'}__' title='__trans__crudgenerator::datatables.buttons.t_edit__ Article'>__trans__crudgenerator::datatables.buttons.edit__</a>",
                'remove' => "<a class='btn btn-danger' href='__route__sirgrimorum_modelo::destroy,{'localecode':'__getLocale__','modelo':'article','registro':':modelId'}__' data-confirm='__trans__crudgenerator::admin.messages.confirm_destroy__' data-yes='__trans__crudgenerator::admin.layout.labels.yes__' data-no='__trans__crudgenerator::admin.layout.labels.no__' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme'). "' data-confirmicon='" . config('sirgrimorum.crudgenerator.icons.confirm'). "' data-confirmtitle='' data-method='delete' rel='nofollow' title='__trans__crudgenerator::datatables.buttons.t_remove__ Articles'>__trans__crudgenerator::datatables.buttons.remove__</a>",
                'create' => "<a class='btn btn-info' href='__route__sirgrimorum_modelos::create,{'localecode':'__getLocale__','modelo':'article'}__' title='__trans__crudgenerator::datatables.buttons.t_create__ Article'>__trans__crudgenerator::datatables.buttons.create__</a>",
            ],
            "botones" => [ //option B for lists using defaults for Button options
                'show' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId__",
                'edit' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/edit",
                'remove' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/destroy",
                'create' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/articles/create",
            ],
            "ajax" => false, // true if the table of list will be load using ajax
            "serverSide" => false, // true if the table of list will be load using ajax on server side
            "conditions" => true, // default true, show conditions buton in list view
	        "filters" => false, // default false, show filters buton in list view
            "files" => false // true if it whould contain file fields,
            "class_form" => "[name of the class]", // optional class for the form, default ''
            "class_label" => "[name of the class]", // optional class for all the labels, default 'col-form-label font-weight-bold mb-0 pt-0'
            "class_labelcont" => "[name of the class]", // optional class for all the labels containers, default 'col-xs-12 col-sm-4 col-md-2'
            "class_input" => "[name of the class]", // optional class for all the inputs, default '' 
            "class_divinput" => "[name of the class]", // optional class for all the divs containing the inputs, default 'col-xs-12 col-sm-8 col-md-10'
            "class_formgroup" => "[name of the class]", // optional class for all the divs containing the div inputs, default for show view 'border border-light', for others default is ''
            "class_offset" => "[name of the class]", // optional class for all the divs conatining buttons, checkboxes and radio buttons, default 'col-xs-offset-0 col-sm-offset-4 col-md-offset-2'
            "class_button" => "[name of the class]", // optional class for all the buttons default 'btn btn-primary'
            "pre_html" => "[Html code]", // optional code to be inserted after the form tag opening
            "post_html" => "[Html code]", // optional code to be inserted before the form tag closing
            "icono" => "<i class="fa fa-home mr-1"></i>", // optional code to be inserted before the name in the admin menu
            "query" => [Callable that returns Builder or collection or array of objects], // optional a function that returns the query builder to be used as base to retreive the model fields, if empty, will use Model::whereRaw("1=1")
            "campos" => [ // list of fields structure. For smart merge, set the value to "notThisTime" to CrudLoader to remove this attribute or field
                "[field/attribute name]" => [ //as apears in the model if used for db save with url "Sirgrimorum_CrudAdministrator"
                    "tipo" => "[type of the field]", // required for all types, options are: "function", "checkbox", "color", "date", "datetime", "time", "email", "url", "file", "files" (multiple files in a single field with Json notation, recomended, text type), "hidden", "html", "article" (Translation from Articles table, needs sirgrimorum/transarticles package), "number", "password", "radio", "relationship" (belongsTo one to many), "relationships" (many to many), "relationshipssel" (many to many with pivot table), "select", "slider", "text", "textarea", "json"
                    "label" => "[text of the label]", // required for all types, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.labels.name__",
                    "placeholder" => "[placeholder text]", // required for text, textarea, email, password and number types, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.placeholders.name__",
                    "description" => "[description text]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.descriptions.name__",
                    "valor" => "[default value of the field]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                    "value" => "[value or lists of values to be taken by de field]", // required for checkbox and radio types, mainly for the checkbox and radio use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                    "unchecked" => "[value to be taken by de field when unchecked]", // for checkbox and radio types, default is 0, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                    "enlace" => "[url for link in show or lists views]", // use :modelId or :modelName to change it for de id or name of the object, use the 'trans_prefix' value if you want localization ej: __route__users.show, {'user': ':modelId'}__ or url("__trans__crudgenerator::article.menu.links.usuario__", array(":modelId"),
                    "conditional" => [ // Only show if all of the conditions are fullfill. use :! for negation and :< :> := for operations and {:empty] or {:notempty}
                        'campo'=>'valor',
                        'campo'=>':!valor',
                        'campo'=>'{:empty}',
                        'campo'=>'{:notempty}',
                        'campo'=>':<notempty'
                        'campo'=>':=notempty'
                        'campo'=>':>notempty'
                    ], 
                    "hide" => ["create","list"], // for all types, views where this field would not be visible
                    "opciones" => [] // required for select type, array of options, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.selects.options__", use nested array for option groups
                    "multiple" => "multiple", // for the select type, if you want the select to be multiple select available, other values means do nothing
                    "modelo" => "[Model object]", // required for the relationship, relationships and the relationshipssel types
                    "id" => "[id field name]", // required for the relationship, relationships and the relationshipssel types
                    "campo" => "[attribute taken as name for the model]", // required for the relationship, relationships and the relationshipssel types, may use an array of field names
                    "groupby" => "[attribute taken as group for options in select]", // optional for the relationship, relationships and the relationshipssel types, may use an array of field names
                    "todos" => "", // required for the relationship, relationships and the relationshipssel types, array or collection of option models, a query Builder, callable that returns an array, a collection or a Query Builder, leave blank if you want all of them
                    "path" => "[name of the path in assets]/", // required for the file and files type, used to save the file, when url is "Sirgrimorum_CrudAdministrator", could be a route o function using the 'trans_prefix'
                    "saveFunction" => [Callable that returns the new path of the file after saving or false, is called with ($file, $filename, $detalles (this camp configuration translated))], Optional for the file and files type used instead of the normal save functionality, overrides the resize
                    "removeFunction" => [Callable that remove a file, is called with ($filename, $detalles (this camp configuration translated))], Optional for the file and files type used instead of the normal remove file functionality
                    "showPath" => "[url or Callable that return the file (responseFactory), is called with ($id, $filepath, $tipo (image, video, audio, pdf, text,office, compressed, other), $detalles (this camp configuration translated))]", // Optional for the file type, used to retreave the image in show and lists views, could be a route using the 'trans_prefix'
                    "saveCompletePath" => true, // for the file and files type, if true, save the new filename including "path", when url is "Sirgrimorum_CrudAdministrator"
                    "disk" => 'local', // for the file and files type, the disk to use for saving the files, remember to create symbolic link for public php artisan storage:link
                    "length" => [number of characters in the random file name], // for file and files types, default 20 when url is "Sirgrimorum_CrudAdministrator"
                    "resize" => [ // for file and files types when file is an image, save additional copies resized when url is "Sirgrimorum_CrudAdministrator", requieres Intervention/Image plugin (composer require intervention/image , $providers Intervention\Image\ImageServiceProvider::class , $aliases 'Image' => Intervention\Image\Facades\Image::class
                        "width" => [width of the new image in pixels], // if 0 or not present, it will take the height and preserve aspect ratio, if height and width equals to 0 or not present, it will just save a copy of the image
                        "height" => [height of the new image in pixels], // if 0 or not present, it will take the width and preserve aspect ratio, if height and width equals to 0 or not present, it will just save a copy of the image
                        "path" => "[name of the path in assets]", // required, path of the new file, it use the same filename of the original file
                        "quality" => [quality of the new image], // default 100
                    ], 
                    "pre" => "[prefix text for file name]", // for file and files types, use '_originalName_' to use the original name as prefix, use the 'trans_prefix' value if you want localization
                    "pre" => "[prefix text for input]", // mainly for number, slider and function types, use the 'trans_prefix' value if you want localization
                    "post" => "[postfix text for the input ]", // mainly for number, slider and function types, use the 'trans_prefix' value if you want localization
                    "scope" => "[ScopeBase for the Transarticles table, use dot notation if needed]", // for article types
                    "format" => [(number of decimals), "[decimal separator]", "[mil separator]"], // for number and function types, aplies format to the number []
                    "format" => [ // for the date, time and datetime types, carbon and moment results must be equivalents
                        "carbon" => "[carbon datetime format text]", // if not present defaults are "Y-m-d" for date or "H:i:s" for time or "Y-m-d H:i:s" for datetime, use % for localized format using strftime ej: '%A %d %B %Y'
                        "moment" => "[moment datetime format text]", // if not present defaults are "YYYY-MM-DD" for date or "HH:mm:ss" for time or "YYYY-MM-DD HH:mm:ss" for datetime
                    ],
                    "format" => "", // for the color type, default is null, could be "hex", search in  bootstrap-color-picker for other options
                    "timezone" => "[time zone text]", // for the date and datetime types, if not present default is timezone in app.php config
                    "min" => 0, // required for slider type, min value for slider and number types
                    "max" => 100, // required for slider type, max value for slider and number types
                    "step" => 5, // required for slider type, step value for slider and number types
                    "card_class" => [name of the class] // for the relationshipssel type, set additional classes for the cards
                    "columnas" => [[ // required for the relationshipssel type, list of columns to show in the table
                        "label" => "[Header for the column]", // required, use the 'trans_prefix' value if you want localization
                        "placeholder" => "[Placeholder for the column]", // required for 'text' and 'number' types, use the 'trans_prefix' value if you want localization
                        "type" => "[type of the filed]", // required, options are basically the same of field minus relationships plus: "label" (value of a field in table), "labelpivot" (value of a field in the pivot table)
                        "campo" => "[name of the field]", // required
                        "opciones" => [] // required for select type, array of options, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.selects.options__"
 *                      "format" => [(number of decimals), "[decimal separator]", "[mil separator]"], // for number types, aplies format to the number []
                        "valor" => "[default value of the field]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                   ],],
                   "readonly" => "readonly", // for all types, if set adds the value to the attribute "readonly" in the input and hide the field in create
                   "nodb" => "nodb", // for all types, if set means is not a field to use to update the model (just for show) when url is "Sirgrimorum_CrudAdministrator" and hide the field in create
                   "pre_html" => "[Html code]", // for all types, code to be inserted before the <div class="form-group row"> tag opening for this field
                   "post_html" => "[Html code]", // for all types, code to be inserted after the <div class="form-group row"> tag closing for this field
                   "es_html" => true, // for article types, if use a wysig editor or not
                   "datatables" => "[tipo de columna]", // for all types, options for this column in datatables, options are "noFiltro" (is not used as filter or condition), "preFiltro" (is used to prefilter the table)
                ],
            ],
            "rules" => [ //the array contining the validation rules. If not here, it whill search for them in the model, public property with the same name
            ],
            "error_messages" => [ //the validation error messages. If not here, it whill search for them in the model, public property with the same name
            ],
            "permissions" => [ //the permissions to validate before doing an action, for CrudController if not present, uses the "sirgrimorum_cms::permission" closure, false send back to the 'sirgrimorum_cms::login_path' 
                "default" => [closure that returns true or false], // the default permission to validate if others not present, remember to use request() helper if needed, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "index" => [closure that returns true or false], // permission for the index action of Crud, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "create" => [closure that returns true or false], // permission for the create action of Crud, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "store" => [closure($request) that returns true or false], // permission for the create action of Crud, remember to use request() helper if needed, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "show" => [closure($object) that returns true or false], // permission for the show action of Crud, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "edit" => [closure($object) that returns true or false], // permission for the edit action of Crud, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "update" => [closure($object) that returns true or false], // permission for the update action of Crud, remember to use request() helper if needed, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
                "destroy" => [closure($object) that returns true or false], // permission for the delete action of Crud, for CrudCrontroller false send back to the 'sirgrimorum_cms::login_path' 
            ],
 * 
 */
return [
    
];
