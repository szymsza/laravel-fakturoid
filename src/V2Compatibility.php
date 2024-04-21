<?php

namespace WEBIZ\LaravelFakturoid;

trait V2Compatibility
{
    /**
     * Ad-hoc overwrite this old API method.
     */
    public function createMessage()
    {
        return $this->getInvoicesProvider()->createMessage(...func_get_args());
    }

    /**
     * Ad-hoc overwrite this old API method.
     */
    public function fireInvoice()
    {
        $args = func_get_args();

        if (isset($args[1]) && in_array($args[1], ["pay", "pay_proforma", "pay_partial_proforma"])) {
            return $this->getInvoicesProvider()->createPayment($args[0], ['paid_on' => (new \DateTime())->format('Y-m-d')]);
        }

        return $this->getInvoicesProvider()->fireAction(...$args);
    }

    /**
     * If a method does not exist and matches the old API, try to approximate new API call.
     *
     * @param $method string
     * @param $arguments array
     * @return mixed
     */
    public function handlePreviousAPIMethod(string $method, array $arguments): mixed
    {
        // Listen to old methods and redirect them to new providers
        if (preg_match("/^(create|get|update|delete)(.+)$/", $method, $matches)) {
            $action = $matches[1];

            [$provider, $method, $arguments] = $this->parsePreviousAPIMethod($action, $matches[2], $arguments);

            return $this->{"get" . $provider . "Provider"}()
                ->{$method}(...$arguments);
        }

        throw new \BadMethodCallException("Method '{$method}' does not exist on Fakturoid instance.");
    }

    /**
     * Based on an old method signature, get appropriate provider, method, and possibly alter arguments to reflect the new API.
     *
     * @param $action string method to be called, i.e., create/get/update/delete
     * @param $entity string entity to call the method over, e.g., Invoices (singular if $action === "get")
     * @param $arguments array arguments passed to the method
     * @return array [ $provider    describes the provider name to call the method over,
     *                 $method      describes the actual name of the class method to be called,
     *                 $arguments   is an array of arguments to be passed to the method, possibly augmented by, e.g., filters ]
     */
    private function parsePreviousAPIMethod(string $action, string $entity, array $arguments): array
    {
        $provider = $entity;

        // Convert singular to plural for update and create
        if ($action != "get")
            $provider = $provider . "s";

        $method = ($action === "get") ? "list" : $action;

        // Ad-hoc special cases
        switch ($entity) {
            case "InvoicePdf": $provider = "Invoices"; $method = "getPdf"; break;
            case "RegularInvoices": $provider = "Invoices"; $arguments[0]["document_type"] = "regular"; break;
            case "ProformaInvoices": $provider = "Invoices"; $arguments[0]["document_type"] = "proforma"; break;
        }

        return [$provider, $method, $arguments];
    }
}