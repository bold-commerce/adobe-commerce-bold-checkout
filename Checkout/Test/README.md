<h1>Run Api-Functional Tests:</h1>
<ol>
    <li><b>Set api token to config:</b> bin/magento config:set --scope=website --scope-code=base checkout/bold_checkout_base/api_token {{bold-api-token}}</li>
    <li><b>Run tests Git: </b> bin/cli vendor/bin/phpunit -c /var/www/html/dev/tests/api-functional/phpunit_rest.xml app/code/Bold/Checkout/Test/Api/*.php</li>
    <li><b>Run tests Composer: </b>  bin/cli vendor/bin/phpunit -c /var/www/html/dev/tests/api-functional/phpunit_rest.xml vendor/bold-commerce/module-checkout/Test/Api/CreateOrderTest.php</li>
</ol>
