# Adobe Commerce Bold Checkout Integration.
## Description
This module replaces native Adobe Commerce checkout with the Bold Checkout.
## Installation of the Adobe Commerce module
1. From your Adobe Commerce server Clone https://github.com/bold-commerce/adobe-commerce-bold-checkout to `app/code/Bold/`.
2. Run `bin/magento setup:upgrade`
3. Run `bin/magento setup:di:compile`
# Configuration of the Adobe Commerce composer package
1. Run `composer require bold-commerce/module-checkout`
2. Run `bin/magento setup:upgrade
3. Run `bin/magento setup:di:compile`
# Enable Bold Checkout
1. Go to `Stores > Configuration > Sales > Checkout`.
2. Switch Configuration Scope to Website Bold Checkout should be integrated with.
3. Enter Bold Checkout API Token.
4. Enable Bold Checkout.
5. Save Config.
## Documentation
Additional information about installing Bold commerce platform - https://developer.boldcommerce.com/default/guides/platform-connector
How to install a module on your Adobe Commerce platform - https://experienceleague.adobe.com/docs/commerce-cloud-service/user-guide/configure-store/b2b-module.html?lang=en
