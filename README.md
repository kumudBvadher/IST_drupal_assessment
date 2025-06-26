Local setup( DOCKER DDEV)
==============================
install ddev from https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/#ddev-installation-windows

After installation Run this

choco install ddev -y
ddev --veresion

Clone this repo and cd to folder 
     git clone https://github.com/kumudBvadher/IST_drupal_assessment.git


ddev config

ddev start 
install Composer
ddev composer install
ddev composer require drupal/drush

ddev import-db --file=<filepath>




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
| Contributor   Contributor    pass123  

AI API Configuration (Mocked)
-----------------------------

Configure your API key in 'settings.php':

'''php
$settings['openai_api_key'] = 'mocked-key';
'''

Multilingual Setup
------------------

1. Enable Arabic at '/admin/config/regional/language'.
2. Enable translation for Dataset fields under '/admin/config/regional/content-language'.
3. Use the language switcher in your theme to switch between English and Arabic.

Useful Admin URLs
-----------------

- Dataset Upload: '/dataset-upload'
- Metadata Editor: '/metadata/edit/{dataset_id}'
- Dataset Dashboard (Views): '/admin/content/datasets'
- Moderation Workflows: '/admin/config/workflow/workflows'
- User Roles & Permissions: '/admin/people/roles'
