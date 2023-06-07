# M2 Self-Hosted Bold Checkout.

## Installation

1. Copy contents of the repository to `app/code/Bold/CheckoutSelfHosted/`
2. Run bin/magento setup:upgrade.
3. Clone and setup react app templates. https://github.com/bold-commerce/checkout-experience-templates#set-up-the-template.
4. Run React app "yarn serve" from React app root dir.
5. Navigate to Magento admin area Stores > Configuration > Sales > Checkout > Bold Checkout Integration.
6. Select "Bold Checkout Type" to "Self-Hosted".
7. Set "Self Hosted Checkout Experience Templates App Url" to your React App URL. (e.g.  http://localhost:8080/)
8. Save configuration.
