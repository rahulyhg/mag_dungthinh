<?php

namespace Botble\Language\Providers;

use Assets;
use Botble\Base\Events\SessionStarted;
use Botble\Base\Supports\Helper;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Language\Facades\LanguageFacade;
use Botble\Language\Http\Middleware\LocaleSessionRedirect;
use Botble\Language\Http\Middleware\LocalizationRedirectFilter;
use Botble\Language\Http\Middleware\LocalizationRoutes;
use Botble\Language\Models\Language as LanguageModel;
use Botble\Language\Models\LanguageMeta;
use Botble\Language\Repositories\Caches\LanguageMetaCacheDecorator;
use Botble\Language\Repositories\Eloquent\LanguageMetaRepository;
use Botble\Language\Repositories\Interfaces\LanguageMetaInterface;
use Event;
use Html;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Botble\Language\Repositories\Caches\LanguageCacheDecorator;
use Botble\Language\Repositories\Eloquent\LanguageRepository;
use Botble\Language\Repositories\Interfaces\LanguageInterface;
use Botble\Support\Services\Cache\Cache;
use Language;
use Route;
use Theme;
use Yajra\DataTables\DataTableAbstract;

class LanguageServiceProvider extends ServiceProvider
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
        $this->setIsInConsole($this->app->runningInConsole())
            ->setNamespace('plugins/language')
            ->loadAndPublishConfigurations(['general']);

        if (setting('enable_cache', false)) {
            $this->app->singleton(LanguageInterface::class, function () {
                return new LanguageCacheDecorator(new LanguageRepository(new LanguageModel()), new Cache($this->app['cache'], LanguageRepository::class));
            });

            $this->app->singleton(LanguageMetaInterface::class, function () {
                return new LanguageMetaCacheDecorator(new LanguageMetaRepository(new LanguageMeta()), new Cache($this->app['cache'], LanguageMetaRepository::class));
            });
        } else {
            $this->app->singleton(LanguageInterface::class, function () {
                return new LanguageRepository(new LanguageModel());
            });

            $this->app->singleton(LanguageMetaInterface::class, function () {
                return new LanguageMetaRepository(new LanguageMeta());
            });
        }

        Helper::autoload(__DIR__ . '/../../helpers');

        AliasLoader::getInstance()->alias('Language', LanguageFacade::class);

        /**
         * @var Router $router
         */
        $router = $this->app['router'];
        $router->aliasMiddleware('localize', LocalizationRoutes::class);
        $router->aliasMiddleware('localizationRedirect', LocalizationRedirectFilter::class);
        $router->aliasMiddleware('localeSessionRedirect', LocaleSessionRedirect::class);
    }

    /**
     * @author QuocDung Dang
     */
    public function boot()
    {
        $this->setIsInConsole($this->app->runningInConsole())
            ->setNamespace('plugins/language')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->publishAssetsFolder()
            ->publishPublicFolder();

        if ($this->app->runningInConsole()) {
            $this->app->register(CommandServiceProvider::class);
        } else {
            $this->app->register(EventServiceProvider::class);

            Event::listen(SessionStarted::class, function () {
                dashboard_menu()->registerItem([
                    'id' => 'cms-plugins-language',
                    'priority' => 2,
                    'parent_id' => 'cms-core-settings',
                    'name' => trans('plugins.language::language.menu'),
                    'icon' => null,
                    'url' => route('languages.list'),
                    'permissions' => ['languages.list'],
                ]);
            });

            Assets::addJavascriptDirectly('vendor/core/plugins/language/js/language-global.js');
            Assets::addStylesheetsDirectly(['vendor/core/plugins/language/css/language.css']);
            $default_language = Language::getDefaultLanguage(['lang_id']);
            if (!empty($default_language)) {
                add_action(BASE_ACTION_META_BOXES, [$this, 'addLanguageBox'], 50, 3);
                add_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, [$this, 'addCurrentLanguageEditingAlert'], 55, 3);
                add_action(BASE_ACTION_BEFORE_EDIT_CONTENT, [$this, 'getCurrentAdminLanguage'], 55, 3);
                $this->app->booted(function () {
                    Theme::asset()->add('language-css', 'vendor/core/plugins/language/css/language-public.css');
                    Theme::asset()->container('footer')->add('language-public-js', 'vendor/core/plugins/language/js/language-public.js', ['jquery']);
                });
            }

            add_filter(BASE_FILTER_GET_LIST_DATA, [$this, 'addLanguageColumn'], 50, 2);
            add_filter(BASE_FILTER_TABLE_HEADINGS, [$this, 'addLanguageTableHeading'], 50, 2);
            add_filter(LANGUAGE_FILTER_SWITCHER, [$this, 'languageSwitcher']);
            add_filter(BASE_FILTER_BEFORE_GET_FRONT_PAGE_ITEM, [$this, 'checkItemLanguageBeforeShow'], 50, 3);
            add_filter(BASE_FILTER_BEFORE_GET_SINGLE, [$this, 'getRelatedDataForOtherLanguage'], 50, 4);
            if (!is_in_admin()) {
                add_filter(BASE_FILTER_GROUP_PUBLIC_ROUTE, [$this, 'addLanguageMiddlewareToPublicRoute'], 958, 1);
            }
            add_filter(BASE_FILTER_TABLE_BUTTONS, [$this, 'addLanguageSwitcherToTable'], 247, 2);
            add_filter(BASE_FILTER_TABLE_QUERY, [$this, 'getDataByCurrentLanguage'], 157, 3);
            add_filter(DASHBOARD_FILTER_ADMIN_NOTIFICATIONS, [$this, 'registerAdminAlert'], 2, 1);
            add_filter(BASE_FILTER_BEFORE_GET_ADMIN_LIST_ITEM, [$this, 'checkItemLanguageBeforeGetAdminListItem'], 50, 3);
            add_filter(THEME_OPTIONS_ACTION_META_BOXES, [$this, 'addLanguageMetaBoxForThemeOptionsAndWidgets'], 55, 2);
            add_filter(WIDGET_TOP_META_BOXES, [$this, 'addLanguageMetaBoxForThemeOptionsAndWidgets'], 55, 2);
        }
    }

    /**
     * @param $screen
     * @author QuocDung Dang
     */
    public function addLanguageBox($screen)
    {
        if (in_array($screen, Language::screenUsingMultiLanguage())) {
            add_meta_box('language_wrap', trans('plugins.language::language.name'), [$this, 'languageMetaField'], $screen, 'top', 'default');
        }
    }

    /**
     * @param $screen
     * @param $data
     * @return string
     * @throws \Throwable
     */
    public function addLanguageMetaBoxForThemeOptionsAndWidgets($data = null, $screen)
    {
        $route = null;
        switch ($screen) {
            case THEME_OPTIONS_MODULE_SCREEN_NAME:
                $route = 'theme.options';
                break;
            case WIDGET_MANAGER_MODULE_SCREEN_NAME:
                $route = 'widgets.list';
                break;
        }

        if (empty($route)) {
            return $data;
        }

        return $data . view('plugins.language::partials.admin-list-language-chooser', compact('route'))->render();
    }

    /**
     * @author QuocDung Dang
     * @throws \Throwable
     */
    public function languageMetaField()
    {
        $languages = Language::getActiveLanguage([
            'lang_code',
            'lang_flag',
            'lang_name',
        ]);
        if ($languages->isEmpty()) {
            return null;
        }

        $related = [];
        $value = null;
        $args = func_get_args();

        $meta = null;

        $current_route = explode('.', Route::currentRouteName());
        $route = [
            'create' => Route::currentRouteName(),
            'edit' => $current_route[0] . '.' . 'edit'
        ];

        if (!empty($args[0])) {
            $meta = app(LanguageMetaInterface::class)->getFirstBy(
                [
                    'lang_meta_content_id' => $args[0]->id,
                    'lang_meta_reference' => $args[1]],
                [
                    'lang_meta_code',
                    'lang_meta_content_id',
                    'lang_meta_origin',
                ]
            );
            if (!empty($meta)) {
                $value = $meta->lang_meta_code;
            }

            $current_route = $current_route = explode('.', Route::currentRouteName());

            if (count($current_route) > 2) {
                $route = $current_route[0] . '.' . $current_route[1];
            } else {
                $route = $current_route[0];
            }

            $route = [
                'create' => $route . '.' . 'create',
                'edit' => Route::currentRouteName()
            ];
        } elseif (request()->input('ref_from')) {
            $meta = app(LanguageMetaInterface::class)->getFirstBy(
                [
                    'lang_meta_content_id' => request()->input('ref_from'),
                    'lang_meta_reference' => $args[1],
                ],
                [
                    'lang_meta_code',
                    'lang_meta_content_id',
                    'lang_meta_origin',
                ]
            );
            $value = request()->input('ref_lang');
        }
        if ($meta) {
            $related = Language::getRelatedLanguageItem($meta->lang_meta_content_id, $meta->lang_meta_origin);
        }
        $current_language = self::checkCurrentLanguage($languages, $value);

        if (!$current_language) {
            $current_language = Language::getDefaultLanguage([
                    'lang_flag',
                    'lang_name',
                    'lang_code',
                ]);
        }

        $route = apply_filters(LANGUAGE_FILTER_ROUTE_ACTION, $route);
        return view('plugins.language::language-box', compact('args', 'languages', 'current_language', 'related', 'route'))->render();
    }

    /**
     * @param $value
     * @param $languages
     * @return mixed
     * @author QuocDung Dang
     */
    public function checkCurrentLanguage($languages, $value)
    {
        $current_language = null;
        foreach ($languages as $language) {
            if ($value) {
                if ($language->lang_code == $value) {
                    $current_language = $language;
                }
            } else {
                if (request()->input('ref_lang')) {
                    if ($language->lang_code == request()->input('ref_lang')) {
                        $current_language = $language;
                    }
                } elseif ($language->lang_is_default) {
                    $current_language = $language;
                }
            }
        }

        if (empty($current_language)) {
            foreach ($languages as $language) {
                if ($language->lang_is_default) {
                    $current_language = $language;
                }
            }
        }

        return $current_language;
    }

    /**
     * @param $screen
     * @param \Illuminate\Http\Request $request
     * @param \Eloquent | null $data
     * @return null|string
     * @author QuocDung Dang
     */
    public function getCurrentAdminLanguage($screen, $request, $data = null)
    {
        $code = null;
        if ($request->has('ref_lang')) {
            $code = $request->input('ref_lang');
        } elseif (!empty($data)) {
            $meta = app(LanguageMetaInterface::class)->getFirstBy([
                'lang_meta_content_id' => $data->id,
                'lang_meta_reference' => $screen,
            ], ['lang_meta_code']);
            if (!empty($meta)) {
                $code = $meta->lang_meta_code;
            }
        }

        if (empty($code)) {
            $code = Language::getDefaultLocaleCode();
        }

        Language::setCurrentAdminLocale($code);

        return $code;
    }

    /**
     * @param $screen
     * @param \Illuminate\Http\Request $request
     * @param $data
     * @return void
     * @author QuocDung Dang
     * @since 2.1
     * @throws \Throwable
     */
    public function addCurrentLanguageEditingAlert($screen, $request, $data = null)
    {
        if (in_array($screen, Language::screenUsingMultiLanguage())) {
            $code = Language::getCurrentAdminLocaleCode();
            if (!empty($code)) {
                $code = $this->getCurrentAdminLanguage($screen, $request, $data);
            }

            $language = null;
            if (!empty($code)) {
                $language = app(LanguageInterface::class)->getFirstBy(['lang_code' => $code], ['lang_name']);
                if (!empty($language)) {
                    $language = $language->lang_name;
                }
            }
            echo view('plugins.language::partials.notification', compact('language'))->render();
        }
        echo null;
    }


    /**
     * @param $headings
     * @param $screen
     * @return mixed
     * @author QuocDung Dang
     */
    public function addLanguageTableHeading($headings, $screen)
    {
        if (in_array($screen, Language::screenUsingMultiLanguage())) {
            $languages = Language::getActiveLanguage(['lang_flag', 'lang_name']);
            $heading = '';
            foreach ($languages as $language) {
                $heading .= language_flag($language->lang_flag, $language->lang_name);
            }
            return array_merge($headings, [
                'language' => [
                    'name' => 'language_meta.lang_meta_id',
                    'title' => $heading,
                    'class' => 'text-center language-header',
                ],
            ]);
        }
        return $headings;
    }

    /**
     * @param DataTableAbstract $data
     * @param $screen
     * @return mixed
     * @author QuocDung Dang
     */
    public function addLanguageColumn($data, $screen)
    {
        if (in_array($screen, Language::screenUsingMultiLanguage())) {
            return $data->addColumn('language', function ($item) use ($screen) {
                $current_language = app(LanguageMetaInterface::class)->getFirstBy([
                    'lang_meta_content_id' => $item->id,
                    'lang_meta_reference' => $screen,
                ]);
                $related_languages = [];
                if ($current_language) {
                    $related_languages = Language::getRelatedLanguageItem($current_language->lang_meta_content_id, $current_language->lang_meta_origin);
                    $current_language = $current_language->lang_meta_code;
                }
                $languages = Language::getActiveLanguage();
                $data = '';
                $current_route = explode('.', Route::currentRouteName());

                if (count($current_route) > 2) {
                    $route = $current_route[0] . '.' . $current_route[1];
                } else {
                    $route = $current_route[0];
                }

                foreach ($languages as $language) {
                    if ($language->lang_code == $current_language) {
                        $data .= view('plugins.language::partials.status.active', compact('route', 'item'))->render();
                    } else {
                        $added = false;
                        if (!empty($related_languages)) {
                            foreach ($related_languages as $key => $related_language) {
                                if ($key == $language->lang_code) {
                                    $data .= view('plugins.language::partials.status.edit', compact('route', 'related_language'))->render();
                                    $added = true;
                                }
                            }
                        }
                        if (!$added) {
                            $data .= view('plugins.language::partials.status.plus', compact('route', 'item', 'language'))->render();
                        }
                    }
                }

                return view('plugins.language::partials.language-column', compact('data'))->render();
            });
        }
        return $data;
    }

    /**
     * @param array $options
     * @return string
     * @author QuocDung Dang
     * @throws \Throwable
     */
    public function languageSwitcher($options = [])
    {
        $supported_locales = Language::getSupportedLocales();
        return view('plugins.language::partials.switcher', compact('options', 'supported_locales'))->render();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $data
     * @param string $screen
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return mixed
     * @author QuocDung Dang
     */
    public function checkItemLanguageBeforeShow($data, $model, $screen = null)
    {
        return $this->getDataByCurrentLanguageCode($data, $model, $screen, Language::getCurrentLocaleCode());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $data
     * @param string $screen
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return mixed
     * @author QuocDung Dang
     */
    public function checkItemLanguageBeforeGetAdminListItem($data, $model, $screen = null)
    {
        return $this->getDataByCurrentLanguageCode($data, $model, $screen, Language::getCurrentAdminLocaleCode());
    }

    /**
     * @param \Eloquent $data
     * @param $screen
     * @param \Illuminate\Database\Eloquent\Builder $model
     * @return string
     * @author QuocDung Dang
     */
    public function getRelatedDataForOtherLanguage($data, $model, $screen)
    {
        if (in_array($screen, Language::screenUsingMultiLanguage())) {
            if (!empty($data)) {
                $current = app(LanguageMetaInterface::class)->getFirstBy([
                    'lang_meta_reference' => $screen,
                    'lang_meta_content_id' => $data->id,
                ]);
                if ($current) {
                    if ($current->lang_meta_code != Language::getCurrentLocaleCode()) {
                        if (config('plugins.language.general.show_item_in_default_language_if_current_version_not_existed', true) == false && $screen != MENU_MODULE_SCREEN_NAME) {
                            return null;
                        }
                        $meta = app(LanguageMetaInterface::class)->getModel()
                            ->where('lang_meta_origin', '=', $current->lang_meta_origin)
                            ->where('lang_meta_content_id', '!=', $data->id)
                            ->where('lang_meta_code', '=', Language::getCurrentLocaleCode())
                            ->first();
                        if ($meta) {
                            $data = $model->where('id', '=', $meta->lang_meta_content_id)->first();
                            if ($data) {
                                return $data;
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return array
     * @author QuocDung Dang
     */
    public function addLanguageMiddlewareToPublicRoute($data)
    {
        return array_merge_recursive($data, [
            'prefix' => Language::setLocale(),
            'middleware' => [
                'localeSessionRedirect',
                'localizationRedirect',
            ],
        ]);
    }

    /**
     * @param $buttons
     * @param $screen
     * @return array
     * @author QuocDung Dang
     * @since 2.2
     */
    public function addLanguageSwitcherToTable($buttons, $screen)
    {
        if (in_array($screen, Language::screenUsingMultiLanguage())) {
            $active_languages = Language::getActiveLanguage(['lang_code', 'lang_name']);
            $language_buttons = [];
            $current_language = Language::getCurrentAdminLocaleCode();
            foreach ($active_languages as $item) {
                $language_buttons[] = [
                    'className' => 'change-data-language-item ' . ($item->lang_code == $current_language ? 'active' : ''),
                    'text' => Html::tag('span', $item->lang_name, ['data-href' => route('languages.change.data.language', $item->lang_code)])->toHtml(),
                ];
            }

            $language_buttons[] = [
                'className' => 'change-data-language-item ' . ('all' == $current_language ? 'active' : ''),
                'text' => Html::tag('span', trans('plugins.language::language.show_all'), ['data-href' => route('languages.change.data.language', 'all')])->toHtml(),
            ];

            $flag = app(LanguageInterface::class)->getFirstBy(['lang_code' => $current_language], ['lang_flag', 'lang_name']);
            if (!empty($flag)) {
                $flag = language_flag($flag->lang_flag, $flag->lang_name);
            } else {
                $flag = Html::tag('i', '', ['class' => 'fa fa-flag'])->toHtml();
            }

            $language = [
                'language' => [
                    'extend' => 'collection',
                    'text' => $flag . Html::tag('span', ' ' . trans('plugins.language::language.change_language') . ' ' . Html::tag('span', '', ['class' => 'caret'])->toHtml())->toHtml(),
                    'buttons' => $language_buttons,
                ],
            ];
            $buttons = array_merge($buttons, $language);
        }

        return $buttons;
    }

    /**
     * @param Builder $query
     * @param Model $model
     * @param string $screen
     * @return mixed
     * @author QuocDung Dang
     * @since 2.2
     */
    public function getDataByCurrentLanguage($query, $model, $screen = null)
    {
        if (!empty($screen) && in_array($screen, Language::screenUsingMultiLanguage())) {
            /**
             * @var \Eloquent $model
             */
            $table = $model->getTable();
            $query = $query->join('language_meta', 'language_meta.lang_meta_content_id', $table . '.id')
                ->where('language_meta.lang_meta_reference', '=', $screen);

            if (Language::getCurrentAdminLocaleCode() != 'all') {
                $query = $query->where('language_meta.lang_meta_code', '=', Language::getCurrentAdminLocaleCode());
            }
        }
        return $query;
    }

    /**
     * @param Builder $data
     * @param Model $model
     * @param string $screen
     * @param $language_code
     * @return mixed
     * @author QuocDung Dang
     */
    protected function getDataByCurrentLanguageCode($data, $model, $screen, $language_code)
    {
        if (!empty($screen) && in_array($screen, Language::screenUsingMultiLanguage()) && !empty($language_code)) {
            $table = $model->getTable();
            $query = $data->join('language_meta', 'language_meta.lang_meta_content_id', $table . '.id')
                ->where('language_meta.lang_meta_reference', '=', $screen);
            if (Language::getCurrentAdminLocaleCode() != 'all') {
                $query = $query->where('language_meta.lang_meta_code', '=', $language_code);
            }
            return $query;
        }
        return $data;
    }

    /**
     * @param string $alert
     * @return string
     * @author QuocDung Dang
     * @throws \Throwable
     */
    public function registerAdminAlert($alert)
    {
        return $alert . view('plugins.language::partials.admin-language-switcher')->render();
    }
}