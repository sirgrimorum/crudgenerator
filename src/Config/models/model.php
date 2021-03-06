<?php
/*
 * Configuration file for the article model to use in the crud generator of Sirgrimorum/Cms
 * For smart merge, set the value to "notThisTime" to CrudLoader to remove this attribute or field
 * structure:
            "modelo" => "[Model object]",
            "tabla" => "[table name]",
            "forceSmartMerge" => false, // If should force smartMerge on load or not, if not set, will use the specifications from parameters or false as default
            "nombre" => "[attribute taken as name for the model]",
            "id" => "[id field name]",
            "url" => "[url to use as action for the form]" | [ // use "Sirgrimorum_CrudAdministrator" if you want the crudgen to manage it, change if you want to handle it diferently, this
                "show" => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId__", // (Replaced by "botones") The url to the show view, use :modelId for the id in lists, will be call using GET
                "create" => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/articles/create", // (Replaced by "botones") The url to the create view, will be call using GET
                "store" => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/store", // The url to process the store action, will be call using POST
                "edit" => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/edit", // (Replaced by "botones") The url to the edit view, use :modelId for the id in lists, will be call using GET
                "update" => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/update", // The url to the process the update action, use :modelId for the id in lists, will be call using PUT
                "remove" => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/destroy", // (Replaced by "botones") The url to process the remove action, use :modelId for the id in lists, will be call using DELETE
            ], 
            "botones" => "[text or html of the submit button of the form or array of buttons for lists]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.labels.create__", use route_prefix or url_prefix y json notation to use route or url function, ej: "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId__" ,  use :modelName or :modelId to change it for the corresponding name or id Overriden if url is Sirgrimorum_CrudAdministrator, for lists, set an array using for keys the actions ('create','show','edit','remove') and for values the urls for each ones or the <a
            "botones" => [ //option A for lists using <a tag to load Button options
                'show' => "<a class='btn btn-info' href='__route__sirgrimorum_modelo::show,{'modelo':'article','registro':':modelId'}__' title='__trans__crudgenerator::datatables.buttons.t_show__ Article'>__trans__crudgenerator::datatables.buttons.show__</a>",
                'edit' => "<a class='btn btn-success' href='__route__sirgrimorum_modelo::edit,{'modelo':'article','registro':':modelId'}__' title='__trans__crudgenerator::datatables.buttons.t_edit__ Article'>__trans__crudgenerator::datatables.buttons.edit__</a>",
                'remove' => "<a class='btn btn-danger' href='__route__sirgrimorum_modelo::destroy,{'modelo':'article','registro':':modelId'}__' data-confirm='__trans__crudgenerator::admin.messages.confirm_destroy__' data-yes='__trans__crudgenerator::admin.layout.labels.yes__' data-no='__trans__crudgenerator::admin.layout.labels.no__' data-confirmtheme='" . config('sirgrimorum.crudgenerator.confirm_theme'). "' data-confirmicon='" . config('sirgrimorum.crudgenerator.icons.confirm'). "' data-confirmtitle='' data-method='delete' rel='nofollow' title='__trans__crudgenerator::datatables.buttons.t_remove__ Articles'>__trans__crudgenerator::datatables.buttons.remove__</a>",
                'create' => "<a class='btn btn-info' href='__route__sirgrimorum_modelos::create,{'modelo':'article'}__' title='__trans__crudgenerator::datatables.buttons.t_create__ Article'>__trans__crudgenerator::datatables.buttons.create__</a>",
                'boton_adicional1' => [ // Aditional buttons to use in lists, The show, edit, remove or create ones must bue only strings
                    "title" => "[Title of the button (on mouseover)]",
                    "text" => "[Content of the button, could be html]",
                    "extendSelected" => false, // If it should extend selected, when true, it would be disabled if no row is selected
                    "class" => "[Class for the button]",
                    "callback" => "[name of the javascript function to be called on click]", // Will be called with 4 arguments: idsSelected (int|string), namesSelected (string), rowsSelected (object), tablaid (string), remember to use showLoading() and hideLoading() if needed
                    "script" => "[url to de external script where de function is]", // could use __asset__, __url__ or __route__
                ],
                'boton_adicional2' => [
                    "title" => "[Title of the button (on mouseover)]",
                    "text" => "[Content of the button, could be html]",
                    "extendSelected" => true, // If it should extend selected, when true, it would be disabled if no row is selected
                    "class" => "[Class for the button]",
                    "callback" => "function(idsSelected, namesSelected, rowsSelected, tablaId){
                        console.log('boton_adicional2 clicked', idsSelected, namesSelected, rowsSelected.data().toArray());
                    }", // Will be called with 4 arguments: idsSelected (int|string), namesSelected (string), rowsSelected (object), tablaid (string), remember to use showLoading() and hideLoading() if needed
                ]
            ],
            "botones" => [ //option B for lists using defaults for Button options
                'show' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId__",
                'edit' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/edit",
                'remove' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/article/:modelId/destroy",
                'create' => "__url____route__sirgrimorum_home,{'localecode':'__getLocale__'}__/articles/create",
            ],
            "js_vars" => [ // variable to have available in javascript in lists for "botones"
                "var1" => "value", //
            ],
            "rememberPreFiltersFor" => (5*60), // Duration in seconds of the cookie to remember pre_filters in index, default is 5 minutes. put 0 to not remember pre-filters
            "forguetPreFiltersAfterFirstUse" => false, // If the Prefilters should be forget after the first remember (comeback to the page and leaving without changing them)
            "multiRemove" => true, // Alows to remove several registries at once in the list view, default is true,
            "ajax" => false, // true if the table of list will be load using ajax
            "serverSide" => false, // true if the table of list will be load using ajax on server side
            "conditions" => true, // default true, show conditions buton in list view
	        "filters" => false, // default false, show filters buton in list view
            "files" => false // true if it whould contain file fields,
            "class_form" => "[name of the class]", // optional class for the form, default ''
            "class_label" => "[name of the class]", // optional class for all the labels, default 'col-form-label font-weight-bold mb-0 pt-0'
            "class_labelcont" => "[name of the class]", // optional class for all the labels containers, default 'col-xs-12 col-sm-12 col-md-3'
            "class_input" => "[name of the class]", // optional class for all the inputs, default '' 
            "class_divinput" => "[name of the class]", // optional class for all the divs containing the inputs, default 'col-xs-12 col-sm-12 col-md-9'
            "class_formgroup" => "[name of the class]", // optional class for all the divs containing the div inputs, default for show view 'border border-light', for others default is ''
            "class_offset" => "[name of the class]", // optional class for all the divs conatining buttons, checkboxes and radio buttons, default 'offset-xs-0 offset-sm-0 offset-md-3'
            "class_button" => "[name of the class]", // optional class for all the buttons default 'btn btn-primary'
            "class_divbutton" => "[name of the class]", // optional class for the div containing the button default 'form-group row'
            "pre_html" => "[Html code]", // optional code to be inserted after the form tag opening
            "post_html" => "[Html code]", // optional code to be inserted before the form tag closing
            "pre_form_html" => "[Html code]" | ["create"=>"[Html code]", "edit"=>"[Html code]"], // optional code to be inserted before the form tag opening, use :formId to include the form id attribute
            "post_form_html" => "[Html code]" | ["create"=>"[Html code]", "edit"=>"[Html code]"], // optional code to be inserted after the form tag closing, use :formId to include the form id attribute
            "formId" => "[id for the create and edit forms]", // optional, if not present it would be use a random one
            "icono" => "<i class="fa fa-home mr-1"></i>", // optional code to be inserted before the name in the admin menu
            "query" => [Callable that returns Builder or collection or array of objects], // optional a function that returns the query builder to be used as base to retreive the model fields, if empty, will use Model::whereRaw("1=1")
            "campos" => [ // list of fields structure. For smart merge, set the value to "notThisTime" to CrudLoader to remove this attribute or field
                "[field/attribute name]" => [ //as apears in the model if used for db save with url "Sirgrimorum_CrudAdministrator"
                    "tipo" => "[type of the field]", // required for all types, options are: "function", "checkbox", "color", "date", "datetime", "time", "email", "url", "file", "files" (multiple files in a single field with Json notation, recomended, text type), "hidden", "html", "article" (Translation from Articles table, needs sirgrimorum/transarticles package), "number", "password", "radio", "relationship" (belongsTo one to many), "relationships" (many to many), "relationshipssel" (many to many with pivot table), "select", "slider", "text", "textarea", "json"
                    "tipos_temporales" => [ // temporary type of fields to be taken just for a specific action ("list", "show", "create", "edit") 
                        "create" => "[type of the field]",
                    ],
                    "label" => "[text of the label]", // required for all types, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.labels.name__",
                    "placeholder" => "[placeholder text]", // required for text, textarea, email, password and number types, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.placeholders.name__",
                    "description" => "[description text]", // is shown under the label use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.descriptions.name__",
                    "help" => "[help text]", // is shown under the input use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.descriptions.name__",
                    'extraClassDiv' => "extra_class_div_field", // for the form group div, use 'contenedor_field_value' or 'contenedor_field_value_not' for 'chekeador' or 'selecteador' conditioning
                    'extraClassInput' => "extra_class_field", // for the input, use 'checkeador' for conditional fields in 'checkbox' or 'radio' types and 'selecteador' for 'select' type
                    'extraDataInput' => [ // for the input, aditional attributes
                        'data-dato1' => 'valor_dato_1' // use 'data-contenedor' => '.contenedor_field' or 'data-onRemove' => '[code]' with 'chekeador' o 'selecteador' class
                    ],
                    "truncate" => 200, // for the string types, truncate de text in list view, default 200, use 0 for not truncating
                    "valor" => "[default value of the field]", // could be a callable function($registro = null) that return something, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                    "value" => "[value or lists of values to be taken by de field]" | [] // required for checkbox and radio types, on json types makes the json locked (only values can be changed),  could be a callable function($registro = null) that return something, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                        'valor1',
                        'valor2' => "Label valor 2",
                        'valor3' => [
                            'separador' => true, // If this only a separator not an actual choice
                            'label' => "__trans__crudgenerator::article.labels.checkbox.valor3",
                            'description' => "Descripcion valor 3", // goes inside de input group
                            'help' => "Help valor 3", // goes outside de input group with 'extraClassDiv'
                            'extraClassDiv' => "extra_class_div_valor3", // for the input group div, use 'contenedor_field_value2' or 'contenedor_field_value2_not' for 'chekeador' conditioning
                            'extraClassInput' => "extra_class_input_valor3", // for the input, use 'checkeador' for conditional fields
                            'extraDataInput' => [ // for the input, aditiona attributes
                                'data-dato1' => 'valor_dato_1' // use 'data-contenedor' => '.contenedor_field_valor3' with 'chekeador' class
                            ],
                            'checked' => true, // for checked or radio if is checked by default
                        ]
                    ],
                    "arrayInValue" => false, //for the json and files types, if should return de json_decoded in the value from get(), false (default) return the json string
                    "show_data" => "[]", // function(array $dato (the one obtain after using $model->get("campo", false)) or string with field_names between <-field_name-> or <-fied_name.value-> to use to process data of the column in the show view
                    "list_data" => "[]", // function(array $dato (the one obtain after using $model->get("campo", false)) or string with field_names between <-field_name-> or <-fied_name.value-> to use to process data of the column in the list view
                    "create_data" => "[]", // function() or string with field_names between <-field_name-> or <-fied_name.value-> to use to process data of the column in the list view, will be used to replace "valor" and "value" fields if not present
                    "edit_data" => "[]", // function($dato (the one obtain after using $model->get("campo", false)) or string with field_names between <-field_name-> or <-fied_name.value-> to use to process data of the column in the list view, will be used to replace "valor" and "value" fields if not present
                    "glue" => "_", // string to be use as glue when value is an array and must be a string, ej: in checkbox with multiple selection, default is "_"
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
                    "opciones" => [] // required for select type, array of options, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.selects.options__", use nested array for option groups, could be a callable of the type function($registro = null){} that returns an array. The $registro var could be the relationship father model for relationshipssel fields
                    "multiple" => "multiple", // for the select and relationshipssel types, if you want the select to be multiple select available, other values means only one. For the select default is only one. For relationshipssel false means only one could be selected, other values means multiple, default is multiple
                    "modelo" => "[Model object]", // required for the relationship, relationships and the relationshipssel types
                    "id" => "[id field name]", // required for the relationship, relationships and the relationshipssel types
                    "campo" => "[attribute taken as name for the model]", // required for the relationship, relationships and the relationshipssel types, may use an array of field names  or a string with the names of the fields to be replaced between <-field_name-> or <-field_name.sub_field.*.sub_field2-> for json, could be a callable of the type function($elemento = null) where $elemento could be an object, array (from $model->get()) or string
                    "groupby" => "[attribute taken as group for options in select]", // optional for the relationship, relationships and the relationshipssel types, may use an array of field names
                    "todos" => "", // required for the relationship, relationships and the relationshipssel types, array or collection of option models, a query Builder, callable that returns an array, a collection or a Query Builder, or a well formed whereRaw command, leave blank if you want all of them
                    "path" => "[name of the path in assets]/", // required for the file and files type, used to save the file, when url is "Sirgrimorum_CrudAdministrator", could be a route o function using the 'trans_prefix'
                    "saveFunction" => [Callable that returns the new path of the file after saving or false, is called with ($file, $filename, $detalles (this camp configuration translated))], Optional for the file and files type used instead of the normal save functionality, overrides the resize
                    "removeFunction" => [Callable that remove a file, is called with ($filename, $detalles (this camp configuration translated))], Optional for the file and files type used instead of the normal remove file functionality
                    "showPath" => "[url or Callable that return the file (responseFactory), is called with ($id, $filepath, $tipo (image, video, audio, pdf, text,office, compressed, other), $detalles (this camp configuration translated))]", // Optional for the file type, used to retreave the image in show and lists views, could be a route using the 'trans_prefix', :modelId, :modelName, :modelCampo
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
                    "inputfilter" => "function(value) {" . // For the create and edit views, Optional input filter function written in JavaScript that accepts a value and return if the value fullfills the filter
                            "return /^([+]|[0-9])?[0-9]*$/.test(value);". // ej. Allow phonenumbers of only numbers and optional leading +
                        "})".
                    "",
                    "config" => "", // for the relationshipssel type, the configuration for the model details to be shown, if empty, will load the default, if is a string, will load the file of the string with config(), if an array will use the array as config
                    "smartMerge" => true, // for the relationshipssel type, if true and "config" is set to something, it will be smartmerged with the default config, default is false
                    "card_class" => [name of the class], // for the relationshipssel type, set additional classes for the cards
                    "minLength" => 1, // for the relationshipssel type, set the minimum number of characters needed to start searching. 0 will start search on focus
                    "maxItem" => 15, // for the relationshipssel type, set the maximum number of items to display on the search, 0 to show all
                    "maxItemPerGroup" => 4, // for the relationshipssel type, set the maximum number of itemps to show per gour on the results
                    "backdrop" => [ // for the relationshipssel type, shows a backdrop behind the search on focus, if false, no backdrop, true (default), a backdrop, us an array of styles to override the styles
                        "background-color" => "#fff",
                    ],
                    "template" => "",// String with a function (query, item) or an html with {{variable}} to be changed for the registry fields values (documentation on http://www.runningcoder.org/jquerytypeahead/#template)
                    "columnas" => [[ // required for the relationshipssel type, list of columns to show in the table
                        "label" => "[Header for the column]", // required, use the 'trans_prefix' value if you want localization
                        "placeholder" => "[Placeholder for the column]", // required for 'text' and 'number' types, use the 'trans_prefix' value if you want localization
                        "type" => "[type of the filed]", // required, options are basically the same of field minus relationships plus: "label" (value of a field in table), "labelpivot" (value of a field in the pivot table)
                        "campo" => "[name of the field]", // required
                        "opciones" => [] // required for select type, array of options, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.selects.options__"
                        "format" => [(number of decimals), "[decimal separator]", "[mil separator]"], // for number types, aplies format to the number []
                        "valor" => "[default value of the field]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                   ],],
                   "readonly" => ["create","list"], // for all types, if set adds the value to the attribute "readonly" in the input 
                   "nodb" => "nodb", // for all types where readonly not applies, if set means is not a field to use to update the model (just for show) when url is "Sirgrimorum_CrudAdministrator" and hide the field in create
                   "pre_html" => "[Html code]", // for all types, code to be inserted before the <div class="form-group row"> tag opening for this field
                   "post_html" => "[Html code]", // for all types, code to be inserted after the <div class="form-group row"> tag closing for this field
                   "es_html" => true, // for article types, if use a wysig editor or not
                   "datatables" => "[tipo de columna]", // for all types, options for this column in datatables, options are "noFiltro" (is not used as filter or condition), "preFiltro" (is used to prefilter the table)
                ],
            ],
            "rules" => [ //the array contining the validation rules. If not here, it whill search for them in the model public property with the same name, , use :model to be change for the model name, use "[relation]__[field_in_pivot_table]" for por rules for the pivot table columns
            ],
            "error_messages" => [ //the validation error messages. If not here, it whill search for them in the model, public property with the same name, use "[relation]__[field_in_pivot_table].[rule]" for por custom error messages for the rules of pivot table columns, and :submodel to change for the name of the related registry
            ],
            "show_button_list" => [ // if the buttons sould be shown in lists, default is true, remember to use request() ans auth() helpers if needed
                "create" => [closure() that returns true or false], 
                "show" => [closure() that returns true or false],
                "edit" => [closure() that returns true or false],
                "remove" => [closure() that returns true or false],
                "boton_adicional1" => [closure() taht return true or false],
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
