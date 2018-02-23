<!DOCTYPE html>
<html lang='es'>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <!-- Titulo del sitio -->
        <title>{{ trans('crudgenerator::admin.layout.title') }}</title>
        <!-- Icono del sitio -->
        <link rel="shortcut icon" href="{{asset('vendor/sirgrimorum/images/favicon.ico')}}">
        <!-- Metas adicionales -->
        <meta name="author" content="{{ trans('crudgenerator::admin.layout.metadata.author') }}">
        <meta name="title" content="{{ trans('crudgenerator::admin.layout.metadata.title') }}">
        <meta name="description" content="{{ trans('crudgenerator::admin.layout.metadata.description') }}">
        <meta name="keywords" content="{{ trans('crudgenerator::admin.layout.metadata.keywords') }}">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="csrf-param" content="_token">
        <style>

            body{
                position: relative;
            }

            .jumbotron p {
                font-weight:normal;
            }

            /* menu */
            #menu_principal img.foto_profile{
                max-height: 42px;
                width: auto;
                margin-bottom: -15px;
                margin-top: -20px;
            }
            .login_menu, .reset_menu{
                width: 250px;
                padding-left: 20px;
                padding-right: 20px;
                margin-top: 30px;
                margin-left: auto;
                margin-right: auto;
            }
            .login_menu .form-group, .reset_menu .form-group{
                margin: 0px 0px 5px 0px;
            }
            .reset_menu{
                display: none;
            }

            /* same height */
            /* columns of same height styles */
            .container-xs-height {
                display:table;
                padding-left:0px;
                padding-right:0px;
            }
            .row-xs-height {
                display: table;
                width: 100%;
            }
            .col-xs-height {
                display: table-cell;
                float: none !important;
            }

            .gris_oscuro{
                background-color: #222;
                border-color: #080808;
                background-image: -webkit-linear-gradient(top,#3c3c3c 0,#222 100%);
                background-image: -o-linear-gradient(top,#3c3c3c 0,#222 100%);
                background-image: -webkit-gradient(linear,left top,left bottom,from(#3c3c3c),to(#222));
                background-image: linear-gradient(to bottom,#3c3c3c 0,#222 100%);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff3c3c3c', endColorstr='#ff222222', GradientType=0);
                filter: progid:DXImageTransform.Microsoft.gradient(enabled=false);
                background-repeat: repeat-x;
            }

            /*cuerpo*/

            #main{
                margin-top:51px;
            }

            footer{
                position:static;
                bottom: 0px;
                width: 100%;
            }
            /* footer */
            .cont_footer{
                padding-top: 20px;
                padding-bottom: 20px;
                /*height: 100px;*/
                text-align: center;
            }
            .cont_footer, .cont_footer a{
                color: #eee;
            }

            /* otros */
            .boton_centrado{
                text-align: center;
            }
            .ui-progressbar {
                position: relative;
            }
            .progress-label {
                position: absolute;
                left: 50%;
                top: 4px;
                font-weight: bold;
                /*text-shadow: 1px 1px 0 #fff;*/
            }

            .profile_pic {
                margin-right: 20px;
                min-width: 200px;
            }
            .profile_pic img{
                max-height: 200px;
            }

            .media-body{
                width: 90%;
            }
            .profile_pic .btn-group-xs{
                margin-top:-20px;
            }

            .profile_pic .btn-group-xs button.btn-default{
                background: none;
                border: 0px;
                text-shadow: none;
                box-shadow: none;
            }

            .form-group .form-control{
                margin-bottom: 18px;
            }

            .form-group .input-group .form-control{
                margin-bottom: 0px;
            }

            .form-group .input-group{
                margin-bottom: 18px;
            }

            .form-group .control-label{
                line-height: 14px;
            }

            div.list-group-item{
                cursor: pointer;
            }
            div.list-group-item:hover, div.list-group-item:focus {
                color: #555;
                text-decoration: none;
                background-color: #f5f5f5;
            }
        </style>
        <!-- Campo definido para incluir estilos especificos en las vistas que lo requieran -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @if (config("sirgrimorum.crudgenerator.css_section") != "")
            @stack(config("sirgrimorum.crudgenerator.css_section"))
        @endif
        
        
    </head>

    <body>
        <div id="app">
            <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
                <div class="container">
                    <a class="navbar-brand" href="{{ route("sirgrimorum_home",App::getLocale()) }}">
                        {{ trans("crudgenerator::admin.layout.title") }}
                    </a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ trans("crudgenerator::admin.layout.toggle_navigation") }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav mr-auto">
                            <li><a class="nav-link" href="{{ url(config("sirgrimorum.crudgenerator.home_path")) }}">{{ trans("crudgenerator::admin.layout.labels.home") }}</a></li>
                            @foreach(config("sirgrimorum.crudgenerator.admin_routes") as $modelo => $config)
                            <?php
                            if (Lang::has("sirgrimorum_cms::" . strtolower($modelo) . ".labels.plural")) {
                                $plurales = trans("crudgenerator::" . strtolower($modelo) . ".labels.plural");
                            } else {
                                $plurales = ucfirst($modelo) . 's';
                            }
                            ?>
                            <li><a class="nav-link" href="{{ route('sirgrimorum_modelos::index',['localecode'=> App::getLocale(),'modelo'=>strtolower($modelo)]) }}">{{ $plurales }}</a></li>
                            @endforeach
                            @stack("menuobj")
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true" >{{trans('crudgenerator::admin.layout.labels.' . App::getLocale()) }}<span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu" aria-labelledby="navbarDropdown">
                                    @foreach(config("sirgrimorum.crudgenerator.list_locales") as $localeCode)
                                    <li>
                                        <a class="dropdown-item" rel="alternate" hreflang="{{$localeCode}}" href="{{route('sirgrimorum_home',$localeCode) }}">
                                            {{{ trans('crudgenerator::admin.layout.labels.'.$localeCode) }}}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                            <!-- Authentication Links -->
                            @guest
                            <li><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                            <li><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                            @else
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Contenido de la pagina -->
            <main class="py-4">
                <div class="container">
                    @if (session(config("sirgrimorum.crudgenerator.status_messages_key")))
                    <!--div class="container">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
                            {!! session(config("sirgrimorum.crudgenerator.status_messages_key")) !!}
                        </div>
                    </div-->
                    @endif
                    @if (session(config("sirgrimorum.crudgenerator.error_messages_key")))
                    <div class="container">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="{{trans('crudgenerator::admin.layout.labels.close')}}"><span aria-hidden="true">&times;</span></button>
                            {!! session(config("sirgrimorum.crudgenerator.error_messages_key")) !!}
                        </div>
                    </div>
                    @endif
                    @yield("contenido")
                </div>

            </main>
            <footer>
                <div class="seccion gris_oscuro">
                    <div class="container cont_footer">
                        @section("piedepagina")
                        {{Route::current()->getName()}}
                        @show
                    </div>
                </div>
            </footer>
        </div>
        @if (config("sirgrimorum.crudgenerator.modal_section") != "")
            @stack(config("sirgrimorum.crudgenerator.modal_section"))
        @endif
        <script src="{{ asset('js/app.js') }}"></script>
        @if (config("sirgrimorum.crudgenerator.js_section") != "")
            @stack(config("sirgrimorum.crudgenerator.js_section"))
        @endif
    </body>
</html>
