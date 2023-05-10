# Adobe Commerce Bold Checkout Integration.

## Overview
This module replaces native Adobe Commerce checkout with the Bold Checkout.

## Installation Git
1. Clone the repository to `app/code/Bold/`.
2. Run `bin/magento setup:upgrade`.

## Installation Composer
1. Run `composer require bold-commerce/module-checkout`.
2. Run `bin/magento setup:upgrade`.

## Enable Bold Checkout
1. Go to `Stores > Configuration > Sales > Checkout`.
2. Switch Configuration Scope to Website Bold Checkout should be integrated with.
3. Enter Bold Checkout API Token.
4. Enable Bold Checkout.
5. Save Config.
