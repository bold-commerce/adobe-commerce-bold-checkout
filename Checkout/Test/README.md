# Run Api-Functional Tests

- **Set api token to config:** `bin/magento config:set --scope=website --scope-code=base checkout/bold_checkout_base/api_token {{bold-api-token}}`
- **Run tests Git:** `bin/cli vendor/bin/phpunit -c /var/www/html/dev/tests/api-functional/phpunit_rest.xml app/code/Bold/Checkout/Test/Api/*.php` 
- **Run tests Composer:**  `bin/cli vendor/bin/phpunit -c /var/www/html/dev/tests/api-functional/phpunit_rest.xml vendor/bold-commerce/module-checkout/Test/Api/CreateOrderTest.php`

# Run MFTF Tests

To work with MFTF you will need to run the following:

- enable the `selenium` image in the `compose.yaml` file
  ```
  selenium:
    image: selenium/standalone-chrome-debug:3.8.1
    ports:
      - "5900:5900"
    extra_hosts: *appextrahosts
  ```
- uncomment/modify _extra_hosts_ in `compose.yaml` file
  ```
  services:
    app:
  ...
      extra_hosts: &appextrahosts
        # Selenium support, replace "magento.test" with URL of your site
        - "magento.test:172.17.0.1"
  ...
    phpfpm:
      extra_hosts: *appextrahosts
  ```
- run `bin/mftf build:project`
- update `src/dev/tests/acceptance/.env` file with appropriate values:
  ```
  MAGENTO_BASE_URL=https://magento.test/
  MAGENTO_BACKEND_NAME=
  MAGENTO_ADMIN_USERNAME=
  SELENIUM_HOST=selenium
  BOLD_ACCOUNT_EMAIL=
  BOLD_ACCOUNT_PASSWORD=
  BOLD_CHECKOUT_SHOP_DOMAIN=
  BOLD_CHECKOUT_API_TOKEN=
  BOLD_CHECKOUT_SHOP_ID=
  ```
- copy `src/dev/tests/acceptance/.credentials.example` to `src/dev/tests/acceptance/.credentials`
- update `src/dev/tests/acceptance/.credentials` file with appropriate values:
  ```
  magento/MAGENTO_ADMIN_PASSWORD=
  ```
- update `src/nginx.conf.sample` with the following before `deny all` location:
  ```
  location ~* ^/dev/tests/acceptance/utils($|/) {
    root $MAGE_ROOT;
    location ~ ^/dev/tests/acceptance/utils/command.php {
      fastcgi_pass   fastcgi_backend;
      fastcgi_index  index.php;
      fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
      include        fastcgi_params;
    }
  }
  ```
- install **VNC client** (Remmina)
- connect with the VNC by `127.0.0.1:5900`, (default password: `secret`)
- run single test:
  `bin/mftf run:test StorefrontBoldCheckoutStandardWithAuthorizePaymentByCustomerTest`
- run suite:
  `bin/mftf run:group BoldCheckoutStandardSuite`

## Additionally

To have ability to check run test results, you can use `allure`.

- install allure:
  ```
  mkdir -p "$ALLURE_DIR"
  wget https://github.com/allure-framework/allure2/releases/download/2.22.2/allure-2.22.2.tgz
  tar zxvf allure-2.22.2.tgz --strip-components=1 -C "$ALLURE_DIR"
  rm allure-2.22.2.tgz
  sudo ln -s "$ALLURE_DIR"/bin/allure /usr/local/bin/allure
  ```
- run allure:
  `allure serve src/dev/tests/acceptance/tests/_output/allure-results/`
