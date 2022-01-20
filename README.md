RubenRomao_UnCancelOrder extension for Magento2.3
===

This extension will give you the option to process a canceled order by setting it back to processing and update the inventory, as this isn't possible by default in M2.3.

Installation
===

This package is registered on [Packagist](https://packagist.org/packages/rubenromao/un-cancel-order) for easy installation. In your Magento installation root run:

`composer require rubenromao/un-cancel-order`

This will install the latest version in your Magento installation, when completed run:

```
php bin/magento module:enable RubenRomao_UnCancelOrder

php bin/magento setup:upgrade

php bin/magento cache:clean
```

This will enable the extension within your installation.

Upgrades
===

When there is an updated version available, simply run (in your Magento installation root) to download and install the updated version:

`composer update`

