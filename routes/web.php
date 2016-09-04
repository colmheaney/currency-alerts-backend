<?php

Route::group(['prefix' => 'api/v1', 'middleware' => 'cors'], function() {
  Route::resource('alerts', 'AlertController', [
    'except' => ['edit', 'create']
  ]);

  Route::post('user', [
    'uses' => 'AuthController@store'
  ]);

  Route::post('user/signin', [
    'uses' => 'AuthController@signin'
  ]);
});

