# Mage2 Module Magedelight Customerprice

    ``magedelight/module-customerprice``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Price Per Customer module in Magento2

## upgrading module and want Price per customer module table data then use patch file
  = use db_schema.xml which is given in patch(patch-pricepercustomer-3.0.0-lower) directory

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Magedelight`
 - Enable the module by running `php bin/magento module:enable Magedelight_Customerprice`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require magedelight/module-customerprice`
 - enable the module by running `php bin/magento module:enable Magedelight_Customerprice`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Model
	- Customerprice

 - Model
	- CustomerpriceDiscount

 - Model
	- CustomerpriceCategory

 - Model
	- CustomerpriceSpecialprice


## Attributes



