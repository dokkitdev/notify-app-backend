<?php
/* test routes start*/
Route::get('/1','TestController@index');
/* test routes end*/

/* CRON routes start*/
Route::get('/importCustomers/{id}','ImportCustomersController@index');
Route::get('/importJobs/{id}','ImportJobsController@index');
Route::get('/collectData/{id}','CollectDataController@index');
/* CRON routes end*/

Route::any('/','Auth\LoginController@showLoginForm')->name('login');
Route::group(['middleware'=>['CheckAdmin']],function (){
    Route::get('/admin/index',['uses'=>'Admin\MainController@index','as'=>'main.index']);

    Route::get('/admin/templates',['uses'=>'Admin\TemplatesGroupsController@index','as'=>'templates.index']);


    Route::post('/admin/template',['uses'=>'Admin\TemplatesController@createTemplate','as'=>'template.createTemplate']);
    Route::put('/admin/template/{id}',['uses'=>'Admin\TemplatesController@updateTemplate','as'=>'template.update']);
    Route::get('/admin/template/{id}/edit',['uses'=>'Admin\TemplatesController@getTemplate','as'=>'template.show']);
    Route::get('/admin/template/{id}',['uses'=>'Admin\TemplatesController@create','as'=>'template.create']);
    Route::get('/admin/template/{id}/email',['uses'=>'Admin\TemplatesController@emailTemplate','as'=>'template.email']);


    Route::post('/admin/housingtemplategroup',['uses'=>'Admin\HousingTemplatesGroupsController@createTemplateGroup','as'=>'template.createTemplate']);

    Route::post('/admin/housingtemplate',['uses'=>'Admin\HousingTemplatesController@createTemplate','as'=>'template.createTemplate']);
    Route::put('/admin/housingtemplate/{id}',['uses'=>'Admin\HousingTemplatesController@updateTemplate','as'=>'template.update']);
    Route::get('/admin/housingtemplate/{id}/edit',['uses'=>'Admin\HousingTemplatesController@getTemplate','as'=>'template.show']);
    Route::get('/admin/housingtemplate/{id}',['uses'=>'Admin\HousingTemplatesController@create','as'=>'template.create']);
    Route::get('/admin/housingtemplate/{id}/email',['uses'=>'Admin\HousingTemplatesController@emailTemplate','as'=>'template.email']);


    Route::get('/admin/users',['uses'=>'Admin\UsersController@index','as'=>'users.index']);
    Route::resource('admin/users','Admin\UsersController',[
        'except'=>[
            'destroy'
        ]
    ]);
    Route::get('/admin/users/{id}/edit',['uses'=>'Admin\UsersController@edit','as'=>'users.edit']);
    Route::get('/admin/users/destroy/{id}',['uses'=>'Admin\UsersController@destroy','as'=>'users.destroy']);
    Route::put('/admin/users/{id}',['uses'=>'Admin\UsersController@update','as'=>'users.update']);
    //Route::get('/admin/block/blocking/{id}',['uses'=>'Admin\UsersController@blocking','as'=>'users.blocking']);
});
Route::group(['middleware'=>['CheckLogin']],function (){
    Route::get('/profile',['uses'=>'Profile@getProfile','as'=>'Profile.getProfile']);
    Route::put('/profile',['uses'=>'Profile@saveProfile','as'=>'Profile.saveProfile']);

    Route::get('/privateContracts',['uses'=>'CustomersController@privateContracts','as'=>'CustomersController.privateContracts']);
    Route::get('/housingCustomers',['uses'=>'CustomersController@housingCustomers','as'=>'CustomersController.housingCustomers']);

    Route::get('/logs',['uses'=>'LogsController@index','as'=>'LogsController.index']);

    Route::post('/contracts/update',['uses'=>'ContractsController@updateContract','as'=>'ContractsController.updateContract']);
    Route::post('/contracts/delete',['uses'=>'ContractsController@deleteContract','as'=>'ContractsController.deleteContract']);

    Route::post('/contracts/process',['uses'=>'ContractsController@processContracts','as'=>'ContractsController.processContracts']);

    Route::post('/jobs/update',['uses'=>'JobsController@updateJob','as'=>'JobsController.updateJob']);
    Route::post('/jobs/delete',['uses'=>'JobsController@deleteJob','as'=>'JobsController.deleteJob']);

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

