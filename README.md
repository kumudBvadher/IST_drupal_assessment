Local setup( DOCKER DDEV)
==============================
install ddev from https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/#ddev-installation-windows

After installation Run this

choco install ddev -y
ddev --veresion

Clone this repo :

     git clone https://github.com/kumudBvadher/IST_drupal_assessment.git
>cd IST_drupal_assessment;

ddev config
     you will be asked for root location type "web"
               
               Docroot Location (.): web
               Project Type [backdrop, cakephp, craftcms, drupal, drupal6, drupal7, drupal8, drupal9, drupal10, drupal11, generic, laravel, magento, magento2, php,           shopware6, silverstripe, symfony, typo3, wordpress] (drupal10): drupal10
               
and then 
ddev start 
install Composer
ddev composer install
ddev composer require drupal/drush

ddev import-db --file=<filepath>

AI API Configuration
-----------------------------

Configure your API key in 'settings.php':

'''php
          
          $settings['openai_api_key'] = 'mocked-key';
'''

Useful Admin URLs
-----------------

- Dataset Upload: '/dataset/upload'
- Metadata Editor: 'dataset/{datase-id}/edit-metadata'
- Dataset Dashboard (Views): '/admin/content/datasets'
- User Roles & Permissions: '/admin/people/roles'
  

AI-Enhanced Dataset Publishing Platform
=======================================

This is a custom Drupal 10+ project that allows users to upload datasets (CSV/XLSX), automatically generate multilingual metadata using AI , and manage publishing workflows with revisioning and dashboards.

Module: dataset_manager
-----------------------

Main functionality includes:

- Upload CSV/XLSX files
- Display file summary (columns, rows, size)
- Auto-generate title, description, tags using mocked AI
- English & Arabic support using Drupal translation
- Metadata editor with revision tracking
- Content moderation workflow
- Supervisor dashboard with filters (Views)


Test Credentials
----------------

| Role       | Username   | Password |
|------------|------------|----------|
| Admin       admin       Admin@123
| Supervisor  supervisor  Admin@123
| Contributor   Contributor    Admin@123  



Multilingual Setup
------------------

1. Enable Arabic at '/admin/config/regional/language'.
2. Enable translation for Dataset fields under '/admin/config/regional/content-language'.
3. Use the language switcher in your theme to switch between English and Arabic.


