<?php

namespace Botble\Base\Http\Middleware;

use Assets;
use Closure;

class Locale
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @author QuocDung Dang
     */
    public function handle($request, Closure $next)
    {
        if ($request->is(config('core.base.general.admin_dir') . '/*') || $request->is(config('core.base.general.admin_dir'))) {
            if (session()->has('admin-locale') && array_key_exists(session('admin-locale'), Assets::getAdminLocales())) {
                app()->setLocale(session('admin-locale'));
            }
        }

        return $next($request);
    }
}
