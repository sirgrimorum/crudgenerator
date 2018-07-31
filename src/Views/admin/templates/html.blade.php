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

            /* footer */
            footer{
                position:static;
                bottom: 0px;
                width: 100%;
            }
            .cont_footer{
                padding-top: 20px;
                padding-bottom: 20px;
                /*height: 100px;*/
                text-align: center;
            }
            .cont_footer, .cont_footer a{
                color: #eee;
            }
        </style>
        <!-- Campo definido para incluir estilos especificos en las vistas que lo requieran -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @jsmodel(Auth::user(),currentUser)
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
                            <li><a class="nav-link" href="{{ route('sirgrimorum_modelos::index',['modelo'=>strtolower($modelo)]) }}">{{ $plurales }}</a></li>
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
                                        <a class="dropdown-item" rel="alternate" hreflang="{{$localeCode}}" href="{{CrudGenerator::changeLocale($localeCode) }}">
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
                <div class="bg-dark text-white">
                    <div class="container cont_footer">
                        {{ request()->path()}}
                        @section("piedepagina")
                        {{Route::current()->getName()}}
                        @show
                    </div>
                </div>
            </footer>
        </div>
        <script src="{{ asset('js/app.js') }}"></script>
        @if (config("sirgrimorum.crudgenerator.js_section") != "")
            @stack(config("sirgrimorum.crudgenerator.js_section"))
        @endif
        @if (config("sirgrimorum.crudgenerator.modal_section") != "")
            @stack(config("sirgrimorum.crudgenerator.modal_section"))
        @endif
    </body>
</html>
