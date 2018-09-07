<?php
/* test routes start*/
Route::get('/1','TestController@index');
/* test routes end*/

/* CRON routes start*/
Route::get('/importCustomers/{id}','ImportCustomersController@index');
Route::get('/collectData/{id}','CollectDataController@index');
/* CRON routes end*/

Route::any('/','Auth\LoginController@showLoginForm')->name('login');
Route::group(['middleware'=>['checkAdmin']],function (){
    Route::get('/admin/index',['uses'=>'Admin\MainController@index','as'=>'main.index']);

    Route::get('/admin/templates',['uses'=>'Admin\TemplatesGroupsController@index','as'=>'templates.index']);

    Route::post('/admin/template/{id}',['uses'=>'Admin\TemplatesController@update','as'=>'template.update']);
    Route::get('/admin/template/{id}',['uses'=>'Admin\TemplatesController@getTemplate','as'=>'template.show']);

    Route::get('/admin/users',['uses'=>'Admin\UsersController@index','as'=>'users.index']);
    Route::resource('admin/users','Admin\UsersController',[
        'except'=>[
            'destroy'
        ]
    ]);
    Route::get('/admin/users/{id}/edit',['uses'=>'Admin\UsersController@edit','as'=>'users.edit']);
    Route::get('/admin/users/destroy/{id}',['uses'=>'Admin\UsersController@destroy','as'=>'users.destroy']);
    Route::put('/admin/users/{id}',['uses'=>'Admin\UsersController@update','as'=>'users.update']);
    Route::get('/admin/block/blocking/{id}',['uses'=>'Admin\UsersController@blocking','as'=>'users.blocking']);
});
Route::group(['middleware'=>['checkLogin']],function (){
    Route::get('/profile',['uses'=>'Profile@getProfile','as'=>'Profile.getProfile']);
    Route::put('/profile',['uses'=>'getProfile@saveProfile','as'=>'Profile.saveProfile']);

    Route::get('/customers/{id}',['uses'=>'CustomersController@index','as'=>'CustomersController.index']);

});



//Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

if(env('ALLOW_REGISTRATION')==true) {
// Registration Routes...
    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register');
}

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

