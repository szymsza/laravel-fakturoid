# Laravel Fakturoid

Simple wrapper for official php package https://github.com/fakturoid/fakturoid-php

### Docs

- [Installation](#installation)
- [Configuration](#configuration)
- [Examples](#examples)
- [Upgrade Guide](#upgrade-guide)

## Installation

### Step 1: Install package

Add the package in your composer.json by executing the command.

```bash
composer require szymsza/laravel-fakturoid
```

This will both update composer.json and install the package into the vendor/ directory.

### Step 2: Configuration

First initialise the config file by running this command:

```bash
php artisan vendor:publish --provider="WEBIZ\LaravelFakturoid\FakturoidServiceProvider" --tag="config"
```

With this command, initialize the configuration and modify the created file, located under `config/fakturoid.php`.

## Configuration

```php
return [
    'account_name' => env('FAKTUROID_NAME', 'XXX'), // URL slug of your account
    'account_api_id' => env('FAKTUROID_API_ID', 'XXX'), // found in your Fakturoid user account settings
    'account_api_secret' => env('FAKTUROID_API_SECRET', 'XXX'), // found in your Fakturoid user account settings
    'app_contact' => env('FAKTUROID_APP_CONTACT', 'Application <your@email.cz>'), // linked to the application you are developing
];
```

## Examples

### Create Subject, Create Invoice, Send Invoice (API v3-style)

```php

use Fakturoid;

try {
    // create subject
    $subject = Fakturoid::getSubjectsProvider()->create([
        'name' => 'Firma s.r.o.',
        'email' => 'aloha@pokus.cz'
    ]);
    if ($subject->getBody()) {
        $subject = $subject->getBody();

        // create invoice with lines
        $lines = [
            [
                'name' => 'Big sale',
                'quantity' => 1,
                'unit_price' => 1000
            ],
        ];

        $invoice = Fakturoid::getInvoicesProvider()->create(['subject_id' => $subject->id, 'lines' => $lines]);
        $invoice = $invoice->getBody();

        // send created invoice
        Fakturoid::getInvoicesProvider()->fireAction($invoice->id, 'deliver');
    }
} catch (\Exception $e) {
    dd($e->getCode() . ": " . $e->getMessage());
}

```

### Create Subject, Create Invoice, Send Invoice (old API-style)

```php

use Fakturoid;

try {
    // create subject
    $subject = Fakturoid::createSubject(array(
        'name' => 'Firma s.r.o.',
        'email' => 'aloha@pokus.cz'
    ));
    if ($subject->getBody()) {
        $subject = $subject->getBody();

        // create invoice with lines
        $lines = [
            [
                'name' => 'Big sale',
                'quantity' => 1,
                'unit_price' => 1000
            ],
        ];

        $invoice = Fakturoid::createInvoice(array('subject_id' => $subject->id, 'lines' => $lines));
        $invoice = $invoice->getBody();

        // send created invoice
        Fakturoid::fireInvoice($invoice->id, 'deliver');
    }
} catch (\Exception $e) {
    dd($e->getCode() . ": " . $e->getMessage());
}

```

## Upgrade Guide

If you used the older version of this package communicating with Fakturoid API v2 (pre-March 2024), an **update is required** before the old API version gets turned off on 31st March 2025.

Standard upgrade guide:

1. Update the package to the latest version using Composer.
2. Update your configuration in `config/fakturoid.php` (see [Configuration](#configuration)) or delete your `config/fakturoid.php` and publish the configuration again (see [Installation: Step 2](#step-2-configuration)).
3. Edit your configuration in `.env`:
    * Remove `FAKTUROID_EMAIL` and `FAKTUROID_API_KEY`
    * Add `FAKTUROID_API_ID` and `FAKTUROID_API_SECRET` with credentials found in your Fakturoid user account settings

If you only used basic Fakturoid functionality, all might work as usual at this point. If not, the issue might be of two types:

### Changes of the underlying API
Arguments and return types of Fakturoid API v3 calls are not always the same as in v2 - e.g., `proforma` boolean has been replaced by a `document_type` attribute when fetching invoices.

To see if you need to provide different arguments of should expect different return values, please consult the [official Fakturoid API changelog](https://www.fakturoid.cz/api/v3/changelog).

### Changes of the underlying PHP library
The PHP library this package provides a facade to has changed significantly. Although this wrapper tries to cover up most of these changes to make your code backwards compatible, some less commonly used methods or generally edge cases might case a problem. This can be usually recognized by a `BadMethodCallException: Method 'XXX' does not exist on Fakturoid instance.` or a similar exception thrown by the wrapper or the PHP library.

To solve this issue, please consult the [PHP library documentation](https://github.com/fakturoid/fakturoid-php) to learn how to call your functionality in the new API format (viewing the [README diff](https://github.com/fakturoid/fakturoid-php/commit/207e12a7b495c14882b6566ebd03ed00236953a2#diff-b335630551682c19a781afebcf4d07bf978fb1f8ac04c6bf87428ed5106870f5) might come in handy). E.g., to create a subject, instead of calling 

```php
Fakturoid::createSubject([...]);
```
you would now use
```php
Fakturoid::->getSubjectsProvider()->create([...]);
```
as can be seen in the README diff on line 27, resp. 172.

If you do run into such unhandled comatibility problem, please consider submitting a pull request to the V2Compatibility trait of this package, or at least opening an issue in this repository.

## License

Copyright (c) 2019 - 2020 webiz eu s.r.o MIT Licensed.
