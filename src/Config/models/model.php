<?php
/*
 * Configuration file for the article model to use in the crud generator of Sirgrimorum/Cms
 * structure:
            "modelo" => "[Model object]",
            "tabla" => "[table name]",
            "nombre" => "[attribute taken as name for the model]",
            "id" => "[id field name]",
            "url" => "[url to use as action for the form]", // use "Sirgrimorum_CrudAdministrator" if you want the crude gen to manage it, change if you want to handle it diferently
            "botones" => "[text or html of the submit button of the form or array of buttons for lists]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.labels.create__", use :modelName or :modelId to change it for the corresponding name or id Overriden if url is Sirgrimorum_CrudAdministrator
            "files" => false // true if it whould contain file fields,
            "class_form" => "[name of the class]", // optional class for the form, default 'form-horizontal'
            "class_label" => "[name of the class]", // optional class for all the labels, default 'col-xs-12 col-sm-4 col-md-2'
            "class_input" => "[name of the class]", // optional class for all the inputs, default '' 
            "class_divinput" => "[name of the class]", // optional class for all the divs containing the inputs, default 'col-xs-12 col-sm-8 col-md-10'
            "class_offset" => "[name of the class]", // optional class for all the divs conatining buttons, checkboxes and radio buttons, default 'col-xs-offset-0 col-sm-offset-4 col-md-offset-2'
            "class_button" => "[name of the class]", // optional class for all the buttons default 'btn btn-primary'
            "campos" => [ // list of fields structure 
                "[field/attribute name]" => [ //as apears in the model if used for db save with url "Sirgrimorum_CrudAdministrator"
                    "tipo" => "[type of the field]", // required for all types, options are: "function", "checkbox", "date", "datetime", "email", "url", "file", "hidden", "html", "number", "password", "radio", "relationship" (belongsTo one to many), "relationships" (many to many), "relationshipssel" (many to many with pivot table), "select", "slider", "text", "textarea"
                    "label" => "[text of the label]", // required for all types, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.labels.name__",
                    "placeholder" => "[placeholder text]", // required for text, textarea, email, password and number types, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.placeholders.name__",
                    "description" => "[description text]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.descriptions.name__",
                    "valor" => "[default value of the field]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                    "value" => "[value or lists of values to be taken by de field]", // required for checkbox and radio types, mainly for the checkbox and radio use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                    "enlace" => "[url for link in show or lists views]", // use :modelId or :modelName to change it for de id or name of the object, use the 'trans_prefix' value if you want localization ej: url("__trans__crudgenerator::article.menu.links.usuario__", array(":modelId"),
                    "opciones" => [] // required for select type, array of options, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.selects.options__"
                    "multiple" => "multiple", // for the select type, if you want the select to be multiple select available, other values means do nothing
                    "modelo" => "[Model object]", // required for the relationship, relationships and the relationshipsel types
                    "id" => "[id field name]", // required for the relationship, relationships and the relationshipsel types
                    "campo" => "[attribute taken as name for the model]", // required for the relationship, relationships and the relationshipsel types
                    "todos" => "", // required for the relationship, relationships and the relationshipsel types, array of option models, leave blank if you want all of them
                    "pathImage" => "[name of the path in assets]/", // required for the file type, used to retreave the image in show and lists views
                    "path" => "[name of the path in assets]/", // required for the file type, used to save the file, when url is "Sirgrimorum_CrudAdministrator"
                    "saveCompletePath" => true, // for the file type, if true, save the new filename including "path", when url is "Sirgrimorum_CrudAdministrator"
                    "length" => [number of characters in the random file name], // for file types, default 20 when url is "Sirgrimorum_CrudAdministrator"
                    "resize" => [ // for file types when file is an image, save additional copies resized when url is "Sirgrimorum_CrudAdministrator", requieres Intervention/Image plugin (composer require intervention/image , $providers Intervention\Image\ImageServiceProvider::class , $aliases 'Image' => Intervention\Image\Facades\Image::class
                        "width" => [width of the new image in pixels], // if 0 or not present, it will take the height and preserve aspect ratio, if height and width equals to 0 or not present, it will just save a copy of the image
                        "height" => [height of the new image in pixels], // if 0 or not present, it will take the width and preserve aspect ratio, if height and width equals to 0 or not present, it will just save a copy of the image
                        "path" => "[name of the path in assets]", // required, path of the new file, it use the same filename of the original file
                        "quality" => [quality of the new image], // default 100
                    ], 
                    "pre" => "[prefix text for file name]", // for file types, use '_originalName_' to use the original name as prefix, use the 'trans_prefix' value if you want localization
                    "pre" => "[prefix text for input]", // mainly for number, slider and function types, use the 'trans_prefix' value if you want localization
                    "post" => "[postfix text for the input ]", // mainly for number, slider and function types, use the 'trans_prefix' value if you want localization
                    "format" => [(number of decimals), "[decimal separator]", "[mil separator]"], // for number and function types, aplies format to the number []
                    "format" => "[moment datetime format text]", // for the date and datetime types, if not present defaults are "YYYY-MM-DD" for date or "YYYY-MM-DD HH:mm:ss" for datetime
                    "timezone" => "[time zone text]", // for the date and datetime types, if not present default is timezone in app.php config
                    "min" => 0, // required for slider type, min value for slider and number types
                    "max" => 100, // required for slider type, max value for slider and number types
                    "step" => 5, // required for slider type, step value for slider and number types
                    "columnas" => [ // required for the relationshipssel type, list of columns to show in the table
                        "label" => "[Header for the column]", // required, use the 'trans_prefix' value if you want localization
                        "type" => "[type of the filed]", // required, options are: "label" (value of a field in table), "labelpivot" (value of a field in the pivot table), "text", "number", "hidden", "select"
                        "campo" => "[name of the field]", // required
                        "opciones" => [] // required for select type, array of options, use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.selects.options__"
                        "valor" => "[default value of the field]", // use the 'trans_prefix' value if you want localization ej: "__trans__crudgenerator::article.default_values.name__",
                   ],
                   "readonly" => "readonly", // for all types, if set adds the value to the attribute "readonly" in the input
                   "nodb" => "nodb", // for all types, if set means is not a field to use to update the model (just for show) when url is "Sirgrimorum_CrudAdministrator"
                ],
            ],
            "rules" => [ //the array contining the validation rules. If not here, it whill search for them in the model, public property with the same name
            ],
            "error_messages" => [ //the validation error messages. If not here, it whill search for them in the model, public property with the same name
            ],
            "permissions" => [ //the permissions to validate before doing an action, if not present, uses the "sirgrimorum_cms::permission" closure, false send back to the 'sirgrimorum_cms::login_path' 
                "default" => [closure that returns true or false], // the default permission to validate if others not present, false send back to the 'sirgrimorum_cms::login_path' 
                "index" => [closure that returns true or false], // permission for the index action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
                "create" => [closure that returns true or false], // permission for the create action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
                "store" => [closure($request) that returns true or false], // permission for the create action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
                "show" => [closure($object) that returns true or false], // permission for the show action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
                "edit" => [closure($object) that returns true or false], // permission for the edit action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
                "update" => [closure($object) that returns true or false], // permission for the update action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
                "destroy" => [closure($object) that returns true or false], // permission for the delete action of Crud, false send back to the 'sirgrimorum_cms::login_path' 
            ],
 * 
 */
return [
    
];
