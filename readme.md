This package helps you to integrate ime-pay payment gateway to your laravel application.

Steps:
1) Require the package. 
    
    composer require shiraj19/ime-pay
2) Add provider in app.php

3) Publish vendor with 

    php artisan vendor:publish
    
4) Run 'artisan migrate' command for uploading migration.

Steps to make it happen:

1. After vendor publish is done, your need to configure the ime-pay-config.php file in the App/config directory.
2. Then just call the url '/payment/ime/{amt}/{refid}' with amt & refid as parameters or just call the index function of the class with amount and refid as params.

And thats it, mission accomplished!

================================
Note: 
In step 1, it is important to add the credentials, or the package wouldnt be able to communicate with the IME service.
================================