<?php

namespace WEBIZ\LaravelFakturoid;

trait V2Compatibility
{
    public function handleOutdatedMethod($method, $arguments)
    {
        // Listen to old methods and redirect them to new providers
        if (preg_match("/^(create|get|update|delete)(.+)$/", $method, $matches)) {
            $action = $matches[1];

            [$providerEntity, $arguments] = $this->convertOutdatedMethod($action, $matches[2], $arguments);
            $provider = $this->{"get" . $providerEntity . "Provider"}();

            switch ($action) {
                case "create": return $provider->create($arguments);
                case "get": return $provider->list($arguments);
                case "update": return $provider->update($arguments);
                case "delete": return $provider->delete($arguments);
            }
        }

        throw new \BadMethodCallException("Method '{$method}' does not exist on Fakturoid instance.");
    }

    private function convertOutdatedMethod($action, $entity, $arguments)
    {
        $provider = $entity;

        // Convert singular to plural for update and create
        if ($action != "get")
            $provider = $provider . "s";

        return [$provider, $arguments];
    }
}