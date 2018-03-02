<?php

namespace Sirgrimorum\CrudGenerator\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CrudGeneratorLocaleRedirect {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $app = app();
        if (in_array($request->path(), $app->config->get('sirgrimorum.crudgenerator.url_ignore'))) {
            return $next($request);
        }
        $currentLocale = $app->getLocale();
        $defaultLocale = $app->config->get('app.locale');
        $params = explode('/', $request->path());
        $redirect = false;
        if (count($params) > 0) {
            $locale = $params[0];
            if (!in_array($locale, $app->config->get('sirgrimorum.crudgenerator.list_locales'))) {
                $locale = $currentLocale;
                $redirect = \Sirgrimorum\CrudGenerator\CrudGenerator::changeLocale($currentLocale,$request->path());
            }
            if ($redirect) {
                app('session')->reflash();
                return new RedirectResponse($redirect, 302, ['Vary' => 'Language Support']);
            }
        }
        return $next($request);
    }

}
