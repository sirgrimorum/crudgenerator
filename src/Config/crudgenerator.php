<?php

return [
    'default_locale' => 'es',
    'list_locales' => ['en', 'es'],
    'url_ignore' => [],
    /*
      |--------------------------------------------------------------------------
      | Prefix for administrator
      |--------------------------------------------------------------------------
      |
      |
     */
    'admin_prefix' => 'crud',
    /*
      |--------------------------------------------------------------------------
      |	  The permission option is the highest-level authentication check that lets you define a closure that should return true if the current user
      | is allowed to view the admin section. Any "falsey" response will send the user back to the 'login_path' defined below.
      |--------------------------------------------------------------------------
      |  @type closure
     */
    'permission' => function() {
return true;
return Auth::check();
},
    /**
     * Wether to use ajax and modals in lists crud o links to new pages
     *
     * @type string
     */
    'use_modals' => true,
    /**
     * The home path name
     *
     * @type string
     */
    'home_path' => '/home',
    /**
     * The login path is the path where Administrator will send the user if they fail a permission check
     *
     * @type string
     */
    'login_path' => 'login',
    /**
     * The logout path is the path where Administrator will send the user when they click the logout link
     *
     * @type string
     */
    'logout_path' => 'logout',
    /**
     * This is the key of the return path that is sent with the redirection to your login_action. Session::get('redirect') will hold the return URL.
     *
     * @type string
     */
    'login_redirect_key' => 'redirect',
    /**
     * Key of the result messages sent from de CrudController. Session::get('status')
     */
    'status_messages_key' => 'status',
    /**
     * Key of the error messages sent from de CrudController. Session::get('error')
     */
    'error_messages_key' => 'error',
    /**
     * trans function prefix for configuration file in order to know is needed to translate with the following key
     */
    'trans_prefix' => '__trans__',
    /**
     * trans_article function prefix for configuration file in order to know is needed to translate with the following key
     * Requires sirgrimorum/transarticles package to be instaled
     */
    'transarticle_prefix' => '__transarticle__',
    /**
     * route function prefix for configuration file in order to know is needed to translate routes with the following key
     */
    'route_prefix' => '__route__',
    /**
     * url function prefix for configuration file in order to know is needed to translate routes with the following key
     */
    'url_prefix' => '__url__',
    /**
     * App::getLocale() key for configuration file in order to know is needed to translate locales with the following key
     */
    'locale_key' => '__getLocale__',
    /**
     * Name of the blade stack that includes de js
     */
    'js_section' => 'selfjs', // if empty, put scripts before forms
    /**
     * Name of the blade stack that includes de css
     */
    'css_section' => 'selfcss', // if empty, put links before forms
    /**
     * Name of the blade stack that includes de modals
     */
    'modal_section' => 'modals', // if empty, put links after forms
    /**
     * Path to the ckeditor js, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/ckeditor/ckeditor.js' publishing with tag=ckeditor
     * or use 'https://cdn.ckeditor.com/4.4.5/full/ckeditor.js'
     */
    'ckeditor_path' => 'vendor/sirgrimorum/ckeditor/ckeditor.js',
    /**
     * path to csss to load in ckeditor separates with , and between ""
     */
    'principal_css' => '"__asset__css/app.css__"', // '"__asset__vendor/sirgrimorum/bootstrap3/css/bootstrap.min.css__", "__asset__css/app.css__"',
    
    /**
     * Path to the jquery tables folder, if is in asset, just include the string for the asset
     * use the .map for the files in other server
     * use 'vendor/sirgrimorum/jquerytables' publishing with tag=jquerytables
     * includes jquery 2.1.4
     */
    'jquerytables_path' => 'vendor/sirgrimorum/jquerytables',
    /**
     * Path to the jquery confirm and rails.js folder, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/confirm' publishing with tag=confirm
     * includes jquery 2.1.4, remember to use <meta name="csrf-token" content="{{ csrf_token() }}"> <meta name="csrf-param" content="_token">
     */
    'confirm_path' => 'vendor/sirgrimorum/confirm',
    /**
     * theme for the confirm window, options are 'material', 'bootstrap', 'dark', 'light'
     */
    'confirm_theme' => 'dark',
    /**
     * icon for the confirm window, options are bootstrap glyphicons 'glyphicon glyphicon-warning-sign' glyphicons or if you instaled it, fontawsome 'fa fa-question-circle fa-lg'
     */
    'confirm_icon' => 'fa fa-question-circle fa-lg',
    /**
     * theme for the success alert, options are 'material', 'bootstrap', 'dark', 'light'
     */
    'success_theme' => 'dark',
    /**
     * icon for the success alert, options are bootstrap glyphicons 'glyphicon glyphicon-warning-sign' glyphicons or if you instaled it, fontawsome 'fa fa-question-circle fa-lg'
     */
    'success_icon' => 'fa fa-check fa-lg',
    /**
     * theme for the error alert, options are 'material', 'bootstrap', 'dark', 'light'
     */
    'error_theme' => 'dark',
    /**
     * icon for the error alert, options are bootstrap glyphicons 'glyphicon glyphicon-warning-sign' glyphicons or if you instaled it, fontawsome 'fa fa-question-circle fa-lg'
     */
    'error_icon' => 'fa fa-exclamation-triangle fa-lg',
    /**
     * Path to the bootstrap slider folder, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/slider' publishing with tag=slider
     * needs bootstrap 3.*
     */
    'slider_path' => 'vendor/sirgrimorum/slider',
     /**
     * Path to the datetime picker folder, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/datetimepicker' publishing with tag=datetimepicker
     */
    'datetimepicker_path' => 'vendor/sirgrimorum/datetimepicker',
     /**
     * Path to the select2 folder, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/select2' publishing with tag=select2
     */
    'select2_path' => 'vendor/sirgrimorum/select2', // https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0
     /**
     * Path to the typeahead folder, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/typeahead' publishing with tag=typeahead
     */
    'typeahead_path' => 'vendor/sirgrimorum/typeahead', // 
    /**
     * Routes list for the administrator
     * "[Name of the model with first uppercase]"=>"[config path of the configuration array for the model]",
     */
    'admin_routes' => [
        "Article" => "sirgrimorum.models.article",
    ],
    /**
     * Probable column names of the "name" for a model, used to autobuild config, only apply for string fields
     */
    'probable_name' => ['name','nombre','title','titulo','nickname','alias','email','first_name','last_name','user_name','full_name'],
    /**
     * Probable column names of an "email" in the model, used to autobuild config, only apply for string fields
     */
    'probable_email' => ['email','e_mail','correo'],
    /**
     * Probable column names of an "url" in the model, used to autobuild config, only apply for string fields
     */
    'probable_url' => ['url','web','www','web_page','page','link','enlace','pagina','pagina_web'],
    /**
     * Probable column names of a "password" in the model, used to autobuild config, only apply for string fields
     */
    'probable_password' => ['password','clave','contrasena','pswd'],
    /**
     * Probable column names of a "html" in the model, used to autobuild config, only apply for text fields
     */
    'probable_html' => ['html','code','codigo','texto','contenido','content', 'description', 'descripcion', 'comment', 'comments', 'comentario', 'comentarios'],
    /**
     * Probable column names of a "file" in the model, used to autobuild config, only apply for string fields
     */
    'probable_file' => ['file','archivo','pdf','doc','document','documento'],
    /**
     * Probable column names of an "image" in the model, used to autobuild config, only apply for string fields
     */
    'probable_image' => ['image','pic','picture','avatar','foto','imagen','logo'],
];
