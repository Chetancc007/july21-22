# Installation

Odoo Bridge For Magento2 module installation is very easy, please follow the steps for installation-

Note:- current modules are compatible with magento v2.2.* and odoo v11.0.

1. Unzip the respective extension zip and create Webkul(vendor) and Odoomagentoconnect(module) name folder inside your magento /app/code/ directory and then move all module's files into magento root directory Magento2/app/code/Webkul/Odoomagentoconnect/ folder.

    note:- while moving magento module files please ignore Odoo-Modules.zip file, cause this zip contains odoo module.

2. Run following commands via terminal
    
    php bin/magento setup:upgrade

    php bin/magento setup:di:compile

    php bin/magento setup:static-content:deploy
    

3. Flush the cache and reindex all.

now module is properly installed

# Installation of odoo modules on odoo

simply extract Odoo-Modules.zip and follow Readme.md file which is inside Odoo-Modules directory.

# User Guide

For Magento v2.2.* Odoo Bridge module's working process follow user guide - http://webkul.com/blog/odoo-bridge-for-magento-v2/
    
# Support

Find us our support policy - https://store.webkul.com/support.html/

# Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/
