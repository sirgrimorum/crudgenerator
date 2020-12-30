<?php

use Illuminate\Support\Facades\Auth;

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
    'permission' => function () {
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
     * crud administrator personalization
     */
    'theme' => [
        'main_color' => '#fff', // main tag background color
        'navbar_class' => 'navbar-light navbar-expand-md navbar-laravel', // classes fo the navbar
        'dropdown_menu_class' => '', // classes for the dropdown_menu in navbar
        'dropdown_menu_item_class' => '', // classes for the dropdown_menu item in navbar
        'dropdown_menu_item_active_color' => '#fff !important', // active color for the dropdown_menu item in navbar
        'dropdown_menu_item_active_bgcolor' => '#007bff !important', // active background color for the dropdown_menu item in navbar
        'dropdown_menu_item_hover_color' => '#16181b !important', // hover color for the dropdown_menu item in navbar
        'dropdown_menu_item_hover_bgcolor' => '#f8f9fa !important', // hover background color for the dropdown_menu item in navbar
        'dropdown_menu_item_disbled_color' => '#6c757d !important', // hover color for the dropdown_menu item in navbar
        'dropdown_menu_item_disabled_bgcolor' => 'transparent !important', // hover background color for the dropdown_menu item in navbar
        'sidebar_class' => '', // classes for the sidebar container
        'sidebar_nav_class' => 'nav-pills', // classes for the sidebar nav
        'sidebar_items' => 'nav-link text-secondary', // classes for the sidebar items
        'sidebar_items_hover_color' => '#000 !important', // hover color for sidebar items
        'sidebar_item_active' => 'active', // classes for active sidebar items
        'sidebar_item_active_color' => '#fff !important', // color for active sidebar items
        'sidebar_item_titulo' => 'text-white bg-dark', // classes for titles in sidebar
        'sidebar_item_titulo_opacity' => '0.15', // opacity for titles in sidebar
        'footer_class' => 'bg-dark text-white', // classes for the footer container
        'footer_a_color' => '#eee', // color for the a tags in footer
    ],
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
     * list of prefixes added to data to be computed with a function after returning it
     * used in configuration functions and in the get() function of models
     * use __ to tell the function where to end
     * 
     * For parameters, use ', ' to separate them inside the prefix and the __
     * For array parameter, use json notation inside comas
     * 
     * 'prefix' => 'global_function_name',
     * 'prefix' => function([parameters]){ return $string}
     */
    'data_prefixes' => [
        '__asset__' => 'asset',
        '__route__' => 'route',
        '__url__' => 'url',
        '__trans__' => '__',
        '__transarticle__' => 'trans_article',
        '__getLocale__' => 'Illuminate\Support\Facades\App::getLocale',
        '__function__' => function ($string) {
            return $string;
        },
    ],
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
     * theme for the success alert, options are 'material', 'bootstrap', 'dark', 'light'
     */
    'success_theme' => 'dark',
    /**
     * theme for the error alert, options are 'material', 'bootstrap', 'dark', 'light'
     */
    'error_theme' => 'dark',
    /**
     * Name for the scriptLoader function
     */
    'scriptLoader_name' => 'scriptLoader',
    /**
     * Name for the linkTagLoader function
     */
    'linkTagLoader_name' => 'linkTagLoader',
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
     * Path to the color-picker folder, if is in asset, just include the string for the asset
     * use 'vendor/sirgrimorum/colorpicker' publishing with tag=colorpicker
     */
    'colorpicker_path' => 'vendor/sirgrimorum/color-picker', // 
    /**
     * Icons to use in the different alerts, buttons, links this need font awesome 4.7 npm install font-awesome@4.7.0 --save
     */
    'icons' => [
        'home' => 'fa fa-home', // for use in admin menu
        'home_admin' => 'fa fa-cog', // for use in admin menu
        'empty' => 'fa fa-lg', // for use with image icon, default is 'fa fa-lg'
        'confirm' => 'fa fa-question-circle fa-lg', // confirm window, default is 'fa fa-question-circle fa-lg'
        'success' => 'fa fa-check fa-lg', // success alert, default is 'fa fa-check fa-lg'
        'error' => 'fa fa-exclamation-triangle fa-lg', // error alert, default is 'fa fa-exclamation-triangle fa-lg'
        'text_file' => 'fa fa-file-text-o fa-lg', // text file button, default is 'fa fa-file-text-o fa-lg'
        'office_file' => 'fa fa-file-word-o fa-lg', // office file type button, default is 'fa fa-file-word-o fa-lg'
        'compressed_file' => 'fa fa-file-archive-o fa-lg', // compressed file button, default is 'fa fa-file-archive-o fa-lg'
        'file' => 'fa fa-file-o fa-lg', // default file button, default is 'fa fa-file-o fa-lg'
        'url' => 'fa fa-link fa-lg', // url button 'fa fa-link, default is fa-lg'
        'video' => 'fa fa-film fa-lg', // video file button, default is 'fa fa-film fa-lg'
        'audio' => 'ffa fa-file-audio-o fa-lg', // audio file button, default is 'fa fa-file-audio-o fa-lg'
        'pdf' => 'fa fa-file-pdf-o fa-lg', // pdf file type button, default is 'fa fa-file-pdf-o fa-lg'
        'minus' => 'fa fa-minus', // minus icon, default is 'fa fa-minus'
        'plus' => 'fa fa-plus', // plus icon, default is 'fa fa-plus'
        'info' => 'fa fa-info-circle fa-lg', // info icon, default is 'fa fa-info-circle fa-lg'

    ],
    /**
     * Routes list for the administrator
     * "[Name of the model with first uppercase]"=>"[config path of the configuration array for the model]",
     * Use "-[Text]-" for group names. The text will be looked for in lang json using __
     */
    'admin_routes' => [
        "-Models-",
        "Article" => "sirgrimorum.models.article",
    ],
    /**
     * Probable column names of the "name" for a model, used to autobuild config, only apply for string fields
     */
    'probable_name' => ['name', 'nombre', 'title', 'titulo', 'nickname', 'alias', 'email', 'first_name', 'last_name', 'user_name', 'full_name'],
    /**
     * Probable column names of an "email" in the model, used to autobuild config, only apply for string fields
     */
    'probable_email' => ['email', 'e_mail', 'correo'],
    /**
     * Probable column names of an "url" in the model, used to autobuild config, only apply for string fields
     */
    'probable_url' => ['url', 'web', 'www', 'web_page', 'page', 'link', 'enlace', 'pagina', 'pagina_web'],
    /**
     * Probable column names of a "password" in the model, used to autobuild config, only apply for string fields
     */
    'probable_password' => ['password', 'clave', 'contrasena', 'pswd'],
    /**
     * Probable column names of a "html" in the model, used to autobuild config, only apply for text fields
     */
    'probable_html' => ['html', 'code', 'codigo', 'texto', 'contenido', 'content', 'description', 'descripcion', 'comment', 'comments', 'comentario', 'comentarios'],
    /**
     * Probable column names of a "article" type in the model, used to autobuild config, only apply for text or string fields
     */
    'probable_article' => ['scope', 'articulo'],
    /**
     * Probable column names of a "file" in the model, used to autobuild config, only apply for string fields
     */
    'probable_file' => ['file', 'archivo', 'pdf', 'doc', 'document', 'documento'],
    /**
     * Probable column names of an "image" in the model, used to autobuild config, only apply for string fields
     */
    'probable_image' => ['image', 'pic', 'picture', 'avatar', 'foto', 'imagen', 'logo', 'icon', 'icono'],
    /**
     * Probable column names of a "color" in the model, used to autobuild config, only apply for string fields
     */
    'probable_color' => ['color'],
];
