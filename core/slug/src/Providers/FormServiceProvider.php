<?php

namespace Botble\Slug\Providers;

use Form;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{

    /**
     * Boot the service provider.
     * @return void
     * @author QuocDung Dang
     */
    public function boot()
    {
        $this->app->booted(function () {
            Form::component('permalink', 'core.slug::permalink', [
                'name',
                'value' => null,
                'id' => null,
                'url' => null,
                'preview' => route('public.single', config('core.slug.general.pattern')),
                'default_slug' => url('/'),
                'ending_url' => config('core.base.general.public_single_ending_url'),
                'attributes' => [],
            ]);
        });
    }
}
