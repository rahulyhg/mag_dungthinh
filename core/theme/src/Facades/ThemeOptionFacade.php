<?php

namespace Botble\Theme\Facades;

use Botble\Theme\ThemeOption;
use Illuminate\Support\Facades\Facade;

class ThemeOptionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @author QuocDung Dang
     */
    protected static function getFacadeAccessor()
    {
        return ThemeOption::class;
    }
}
