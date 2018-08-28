<?php

namespace Botble\Translation;

use Artisan;
use Botble\Base\Interfaces\PluginInterface;
use Botble\Base\Supports\Helper;
use Botble\Translation\Providers\TranslationServiceProvider;
use Schema;

class Plugin implements PluginInterface
{
    /**
     * @author QuocDung Dang
     */
    public static function activate()
    {
        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'plugins/translation/database/migrations',
        ]);

        Artisan::call('vendor:publish', [
            '--force' => true,
            '--tag' => 'public',
            '--provider' => TranslationServiceProvider::class,
        ]);
    }

    /**
     * @author QuocDung Dang
     */
    public static function deactivate()
    {
    }

    /**
     * @author QuocDung Dang
     */
    public static function remove()
    {
        Helper::removePluginAssets('translation');

        Schema::dropIfExists('translations');
    }
}
