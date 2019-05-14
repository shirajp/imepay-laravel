<?php

Route::group(['namespace' => 'Shiraj19\ImePay'], function () {
    Route::get('/payment/ime/{amt}/{refid}', 'Imepay@index');
    Route::post('/payment/ime', 'Imepay@transaction_status');
});