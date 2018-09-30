<?php

namespace Botble\Setting\Providers;

use Botble\Base\Events\SessionStarted;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\Facades\SettingFacade;
use Botble\Setting\Models\Setting as SettingModel;
use Botble\Setting\Repositories\Caches\SettingCacheDecorator;
use Botble\Setting\Repositories\Eloquent\SettingRepository;
use Botble\Setting\Repositories\Interfaces\SettingInterface;
use Botble\Setting\Supports\SettingsManager;
use Botble\Setting\Supports\SettingStore;
use Botble\Support\Services\Cache\Cache;
use Event;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * This provider is deferred and should be lazy loaded.
     *
     * @var boolean
     */
    protected $defer = true;

    /**
     * @author Sang Nguyen
     */
    public function register()
    {
        $this->app->singleton(SettingsManager::class, function (Application $app) {
            return new SettingsManager($app);
        });

        $this->app->bind(SettingStore::class, function (Application $app) {
            return $app->make(SettingsManager::class)->driver();
        });

        AliasLoader::getInstance()->alias('Setting', SettingFacade::class);

        if ($this->app->make(SettingStore::class)->get('enable_cache', false)) {
            $this->app->singleton(SettingInterface::class, function () {
                return new SettingCacheDecorator(new SettingRepository(new SettingModel()), new Cache($this->app['cache'], SettingRepository::class));
            });
        } else {
            $this->app->singleton(SettingInterface::class, function () {
                return new SettingRepository(new SettingModel());
            });
        }

        Helper::autoload(__DIR__ . '/../../helpers');
    }

    /**
     * @author Sang Nguyen
     */
    public function boot()
    {
        $this->setIsInConsole($this->app->runningInConsole())
            ->setNamespace('core/setting')
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->publishAssetsFolder()
            ->publishPublicFolder();

        Event::listen(SessionStarted::class, function () {
            dashboard_menu()
                ->registerItem([
                    'id' => 'cms-core-settings',
                    'priority' => 998,
                    'parent_id' => null,
                    'name' => trans('core.setting::setting.title'),
                    'icon' => 'fa fa-cogs',
                    'url' => route('settings.options'),
                    'permissions' => ['settings.options'],
                ])
                ->registerItem([
                    'id' => 'cms-core-settings-general',
                    'priority' => 1,
                    'parent_id' => 'cms-core-settings',
                    'name' => trans('core.base::layouts.setting_general'),
                    'icon' => null,
                    'url' => route('settings.options'),
                    'permissions' => ['settings.options'],
                ])
                ->registerItem([
                    'id' => 'cms-core-settings-email',
                    'priority' => 2,
                    'parent_id' => 'cms-core-settings',
                    'name' => trans('core.base::layouts.setting_email'),
                    'icon' => null,
                    'url' => route('settings.email'),
                    'permissions' => ['settings.email'],
                ])
                ->registerItem([
                    'id' => 'cms-core-settings-media',
                    'priority' => 3,
                    'parent_id' => 'cms-core-settings',
                    'name' => trans('core.setting::setting.media.title'),
                    'icon' => null,
                    'url' => route('settings.media'),
                    'permissions' => ['settings.media'],
                ]);

            admin_bar()->registerLink('Setting', route('settings.options'), 'appearance');
        });
    }

    /**
     * Which IoC bindings the provider provides.
     *
     * @return array
     */
    public function provides()
    {
        return [
            SettingsManager::class,
            SettingStore::class,
            'setting',
        ];
    }
}
