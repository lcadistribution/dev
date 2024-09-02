#!/bin/bash
bin/magento cache:clean
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f fr_FR en_US
bin/magento cache:clean
bin/magento index:reindex