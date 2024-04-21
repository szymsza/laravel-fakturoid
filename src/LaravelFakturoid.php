<?php

namespace WEBIZ\LaravelFakturoid;

use Fakturoid\Exception\AuthorizationFailedException;
use Fakturoid\FakturoidManager;
use GuzzleHttp\Client;

class LaravelFakturoid
{
    protected FakturoidManager $fakturoid;

    /**
     * @throws AuthorizationFailedException
     */
    public function __construct()
    {
        $this->fakturoid = new FakturoidManager(
            new Client(),
            config('fakturoid.account_api_id'),
            config('fakturoid.account_api_secret'),
            config('fakturoid.app_contact'),
        );
        $this->fakturoid->authClientCredentials();
        $this->fakturoid->setAccountSlug(config('fakturoid.account_name'));
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return $this->{$method}(...$arguments);
        }

        if (method_exists($this->fakturoid, $method)) {
            return $this->fakturoid->{$method}(...$arguments);
        }

        throw new \BadMethodCallException("Method '{$method}' does not exist on Fakturoid instance.");
    }
}
