<?php
/* test routes start*/
Route::get('/1', 'TestController@index');
/* test routes end*/
 
/* CRON routes start*/
Route::get('/importCustomers/{id}', 'ImportCustomersController@index');
Route::get('/importJobs/{id}', 'ImportJobsController@index');
Route::get('/collectData/{id}', 'CollectDataController@index');
/* CRON routes end*/

Route::any('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::group(['middleware' => ['CheckAdmin']], function () {
    Route::get('/admin/index', ['uses' => 'Admin\MainController@index', 'as' => 'main.index']);

    Route::get('/admin/templates', ['uses' => 'Admin\TemplatesGroupsController@index', 'as' => 'templates.index']);


    Route::post('/admin/template', ['uses' => 'Admin\TemplatesController@createTemplate', 'as' => 'template.createTemplate']);
    Route::put('/admin/template/{id}', ['uses' => 'Admin\TemplatesController@updateTemplate', 'as' => 'template.update']);
    Route::get('/admin/template/{id}/edit', ['uses' => 'Admin\TemplatesController@getTemplate', 'as' => 'template.show']);
    Route::get('/admin/template/{id}', ['uses' => 'Admin\TemplatesController@create', 'as' => 'template.create']);
    Route::get('/admin/template/{id}/email', ['uses' => 'Admin\TemplatesController@emailTemplate', 'as' => 'template.email']);
    Route::get('/admin/template/{id}/download-pdf', ['uses' => 'Admin\TemplatesController@downloadPDF', 'as' => 'template.pdf.download']);


    Route::post('/admin/housingtemplategroup', ['uses' => 'Admin\HousingTemplatesGroupsController@createTemplateGroup', 'as' => 'template.createTemplate']);

    Route::post('/admin/housingtemplate', ['uses' => 'Admin\HousingTemplatesController@createTemplate', 'as' => 'template.createTemplate']);
    Route::put('/admin/housingtemplate/{id}', ['uses' => 'Admin\HousingTemplatesController@updateTemplate', 'as' => 'template.update']);
    Route::get('/admin/housingtemplate/{id}/edit', ['uses' => 'Admin\HousingTemplatesController@getTemplate', 'as' => 'template.show']);
    Route::get('/admin/housingtemplate/{id}', ['uses' => 'Admin\HousingTemplatesController@create', 'as' => 'template.create']);
    Route::get('/admin/housingtemplate/{id}/email', ['uses' => 'Admin\HousingTemplatesController@emailTemplate', 'as' => 'template.email']);
    Route::get('/admin/housingtemplate/{id}/download-pdf', ['uses' => 'Admin\HousingTemplatesController@downloadPDF', 'as' => 'housing-template.pdf.download']);


    Route::group(['prefix' => '/admin/templates'], function () {
        Route::get('/', 'Template\\TemplateController@all')->name('templates.all');
        Route::get('/{id}/edit', 'Template\\TemplateController@getTemplate')->name('templates.edit');
        Route::put('/{id}/edit', 'Template\\TemplateController@putTemplate')->name('templates.edit');
        Route::post('/upload_docx', 'Template\\TemplateController@uploadDocx')->name('templates.upload_docx');
    });
    Route::get('/logs/{id}', 'LogsController@showEmails')->name('logs.emails');



    Route::group(['prefix' => '/appointments'], function () {
        Route::get('/', 'Admin\\AppointmentsController@index')->name('appointments.all');
        Route::post('/generate', 'Admin\\AppointmentsController@generate')->name('appointments.generate');
        Route::get('/{id}/clear', 'Admin\\AppointmentsController@clear')->name('appointments.clear');
        Route::get('/{id}/view', 'Admin\\AppointmentsController@viewPdf')->name('appointments.view');
        Route::get('/import', 'Admin\\AppointmentsController@import')->name('appointments.import');
        Route::get('/clear', 'Admin\\AppointmentsController@clearDublicates');
    });

    Route::get('/dashboard', 'DashboardController@getDashboard')->name('dashboard');



    Route::group(['prefix' => '/housing'], function () {
        Route::get('/', 'Admin\\HousingController@index')->name('housing.all');
        Route::get('/{id}/view', 'Admin\\HousingController@viewPdf')->name('housing.view');
        Route::post('/generate', 'Admin\\HousingController@generate')->name('housing.generate');
        Route::get('/import', 'Admin\\HousingController@import')->name('housing.import');
    });

    Route::group(['prefix' => '/private'], function () {
        Route::get('/', 'Admin\\PrivateController@index')->name('private.all');
        Route::get('/{id}/view', 'Admin\\PrivateController@viewPdf')->name('private.view');
        Route::post('/generate', 'Admin\\PrivateController@generate')->name('private.generate');
        Route::get('/import', 'Admin\\PrivateController@import')->name('private.import');
    });


    Route::get('/admin/users', ['uses' => 'Admin\UsersController@index', 'as' => 'users.index']);
    Route::resource('admin/users', 'Admin\UsersController', [
        'except' => [
            'destroy'
        ]
    ]);
    Route::get('/admin/users/{id}/edit', ['uses' => 'Admin\UsersController@edit', 'as' => 'users.edit']);
    Route::get('/admin/users/destroy/{id}', ['uses' => 'Admin\UsersController@destroy', 'as' => 'users.destroy']);
    Route::put('/admin/users/{id}', ['uses' => 'Admin\UsersController@update', 'as' => 'users.update']);
    //Route::get('/admin/block/blocking/{id}',['uses'=>'Admin\UsersController@blocking','as'=>'users.blocking']);
});
Route::group(['middleware' => ['CheckLogin']], function () {
    Route::get('/profile', ['uses' => 'Profile@getProfile', 'as' => 'Profile.getProfile']);
    Route::put('/profile', ['uses' => 'Profile@saveProfile', 'as' => 'Profile.saveProfile']);

    Route::get('/privateContracts', ['uses' => 'CustomersController@privateContracts', 'as' => 'CustomersController.privateContracts']);
    Route::get('/housingCustomers', ['uses' => 'CustomersController@housingCustomers', 'as' => 'CustomersController.housingCustomers']);

    Route::get('/logs', ['uses' => 'LogsController@index', 'as' => 'LogsController.index']);
    Route::get('/logs/{type}/{date}', 'LogsController@dailyLettersLog')->name('daily-letters-log');
    Route::get('/logs/download-pdf-letter-from-s3', 'LogsController@downloadPdfLetterFromS3')->name('download-pdf-letter-from-s3');

    Route::post('/contracts/undelete', ['uses' => 'ContractsController@undeleteContract', 'as' => 'ContractsController.undeleteContract']);
    Route::post('/contracts/delete', ['uses' => 'ContractsController@deleteContract', 'as' => 'ContractsController.deleteContract']);

    Route::post('/contracts/process', ['uses' => 'ContractsController@processContracts', 'as' => 'ContractsController.processContracts']);
    Route::post('/jobs/process', ['uses' => 'JobsController@processJobs', 'as' => 'JobsController.processJobs']);

    Route::post('/jobs/undelete', ['uses' => 'JobsController@undeleteJob', 'as' => 'JobsController.undeleteJob']);
    Route::post('/jobs/delete', ['uses' => 'JobsController@deleteJob', 'as' => 'JobsController.deleteJob']);

    Route::post('/support', ['uses' => 'SupportController@getSupport', 'as' => 'SupportController.getSupport']);

});


//Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

if (env('ALLOW_REGISTRATION') == true) {
// Registration Routes...
    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'Auth\RegisterController@register');
}

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmailBySendGrid')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@resetPasswordOwn');

