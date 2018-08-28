<?php

namespace Botble\Assets\Providers;

use Botble\Assets\Facades\AssetsFacade;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Class AssetsServiceProvider
 * @package Botble\Assets
 * @author QuocDung Dang
 * @since 22/07/2015 11:23 PM
 */
class AssetsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @author QuocDung Dang
     */
    public function register()
    {
        AliasLoader::getInstance()->alias('Assets', AssetsFacade::class);
    }

    /**
     * @author QuocDung Dang
     */
    public function boot()
    {
        $this->setIsInConsole($this->app->runningInConsole())
            ->setNamespace('core/assets')
            ->loadAndPublishConfigurations(['general'])
            ->loadAndPublishViews();
    }
}
