<?php

namespace MityDigital\FuseUtilities\Tests;

use Illuminate\Encryption\Encrypter;
use Livewire\Blaze\BlazeServiceProvider;
use Livewire\LivewireServiceProvider;
use MityDigital\FuseUtilities\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getPackageProviders($app)
    {
        return [
            BlazeServiceProvider::class,
            // Livewire is optional, so only register it when it is installed
            ...(class_exists(LivewireServiceProvider::class) ? [LivewireServiceProvider::class] : []),
            ...parent::getPackageProviders($app),
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        /*$configs = [
            'assets',
            'cp',
            'forms',
            'static_caching',
            'stache',
            'system',
            'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set(
                "statamic.$config",
                require(__DIR__."/../vendor/statamic/cms/config/{$config}.php")
            );
        }*/

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));
    }
}
