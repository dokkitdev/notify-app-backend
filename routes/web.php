<?php

Route::any('/webhooks', 'WebhookController@webhookAction');

Route::any('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::group(
    ['middleware' => ['CheckAdmin']],
    function () {
        Route::group(
            ['prefix' => '/admin'],
            function () {
                Route::get('/index', ['uses' => 'Admin\MainController@index', 'as' => 'main.index']);
            }
        );
        Route::get('/admin/templates', ['uses' => 'Admin\TemplatesGroupsController@index', 'as' => 'templates.index']);
        Route::group(
            ['prefix' => '/admin/templates'],
            function () {
                Route::get('/', 'Template\\TemplateController@all')->name('templates.all');
                Route::get('/{id}/edit', 'Template\\TemplateController@getTemplate')->name('templates.edit');
                Route::put('/{id}/edit', 'Template\\TemplateController@putTemplate')->name('templates.edit');
                Route::post('/upload_docx', 'Template\\TemplateController@uploadDocx')->name('templates.upload_docx');
            }
        );
        Route::get('/logs/{id}', 'LogsController@showEmails')->name('logs.emails');


        Route::group(
            ['prefix' => '/asset-report'],
            function () {
                Route::get('/', 'AssetReportController@index')->name('asset_report.index');
                Route::get('/download', 'AssetReportController@downloadCsv')->name('asset_report.download_csv');
                Route::get('/schedule', 'AssetReportController@scheduleValidation')->name('asset_report.schedule');
            }
        );
        Route::group(
            ['prefix' => '/zero-report'],
            function () {
                Route::get('/', 'ZeroReportController@index')->name('zero_report.index');
                Route::post('/', 'ZeroReportController@requestToParseZeroReport');
            }
        );
        Route::group(
            ['prefix' => '/report-logs'],
            function () {
                Route::get('/', 'ReportLogsController@index')->name('report_logs.index');
                Route::get('/{id}/download', 'ReportLogsController@download')->name('report_logs.download');
            }
        );


        Route::group(
            ['prefix' => '/appointments'],
            function () {
                Route::get('/', 'Admin\\AppointmentsController@index')->name('appointments.all');
                Route::post('/generate', 'Admin\\AppointmentsController@generate')->name('appointments.generate');
                Route::get('/{id}/clear', 'Admin\\AppointmentsController@clear')->name('appointments.clear');
                Route::get('/{id}/view', 'Admin\\AppointmentsController@viewPdf')->name('appointments.view');
                Route::get('/import', 'Admin\\AppointmentsController@import')->name('appointments.import');
                Route::get('/clear', 'Admin\\AppointmentsController@clearDublicates');
            }
        );
        Route::group(
            ['prefix' => '/chl'],
            function () {
                Route::get('/', 'Admin\\AppointmentsChlController@index')->name('chl.appointments.all');
                Route::get('/{id}/view', 'Admin\\AppointmentsChlController@viewPdf')->name('chl.appointments.view');
                Route::post('/generate', 'Admin\\AppointmentsChlController@generate')->name(
                    'chl.appointments.generate'
                );
            }
        );

        Route::group(
            ['prefix' => '/report'],
            function () {
                Route::get('/', 'Admin\\ReportController@index')->name('report.all');
                Route::get('/report', 'Admin\\ReportController@report')->name('report.email');
            }
        );
        Route::group(
            ['prefix' => '/reports'],
            function () {
                Route::get('/', 'Admin\\ReportController@indexReports')->name('reports.all');
                Route::post('/generate', 'Admin\\ReportController@generateReports')->name('reports.generate');
            }
        );


        Route::get('/dashboard', 'DashboardController@getDashboard')->name('dashboard');


        Route::group(
            ['prefix' => '/housing'],
            function () {
                Route::get('/', 'Admin\\HousingController@index')->name('housing.all');
                Route::get('/{id}/view', 'Admin\\HousingController@viewPdf')->name('housing.view');
                Route::post('/generate', 'Admin\\HousingController@generate')->name('housing.generate');
                Route::get('/import', 'Admin\\HousingController@import')->name('housing.import');
            }
        );

        Route::group(
            ['prefix' => '/parsing-logs'],
            function () {
                Route::get('/', 'Admin\\ParsingLogsController@index')->name('parsing_logs.all');
            }
        );

        Route::group(
            ['prefix' => '/private'],
            function () {
                Route::post('/reparse', 'Admin\\NewPrivateController@reparse')->name('private.reparse');
                Route::get('/', 'Admin\\NewPrivateController@index')->name('private.all');
                Route::get('/debit', 'Admin\\NewPrivateController@debitIndex')->name('private.debit');
                Route::get('/{id}/view', 'Admin\\NewPrivateController@viewPdf')->name('private.view');
                Route::post('/generate', 'Admin\\NewPrivateController@generate')->name('private.generate');
            }
        );


        Route::get('/admin/users', ['uses' => 'Admin\UsersController@index', 'as' => 'users.index']);
        Route::resource(
            'admin/users',
            'Admin\UsersController',
            [
                'except' => [
                    'destroy',
                ],
            ]
        );
        Route::get('/admin/users/{id}/edit', ['uses' => 'Admin\UsersController@edit', 'as' => 'users.edit']);
        Route::get('/admin/users/destroy/{id}', ['uses' => 'Admin\UsersController@destroy', 'as' => 'users.destroy']);
        Route::put('/admin/users/{id}', ['uses' => 'Admin\UsersController@update', 'as' => 'users.update']);
    }
);
Route::group(
    ['middleware' => ['CheckLogin']],
    function () {
        Route::get('/profile', ['uses' => 'Profile@getProfile', 'as' => 'Profile.getProfile']);
        Route::put('/profile', ['uses' => 'Profile@saveProfile', 'as' => 'Profile.saveProfile']);

        Route::get(
            '/privateContracts',
            ['uses' => 'CustomersController@privateContracts', 'as' => 'CustomersController.privateContracts']
        );
        Route::get(
            '/housingCustomers',
            ['uses' => 'CustomersController@housingCustomers', 'as' => 'CustomersController.housingCustomers']
        );

        Route::get('/logs', ['uses' => 'LogsController@index', 'as' => 'LogsController.index']);
        Route::get('/logs/{type}/{date}', 'LogsController@dailyLettersLog')->name('daily-letters-log');
        Route::get('/logs/download-pdf-letter-from-s3', 'LogsController@downloadPdfLetterFromS3')->name(
            'download-pdf-letter-from-s3'
        );

        Route::post(
            '/contracts/undelete',
            ['uses' => 'ContractsController@undeleteContract', 'as' => 'ContractsController.undeleteContract']
        );
        Route::post(
            '/contracts/delete',
            ['uses' => 'ContractsController@deleteContract', 'as' => 'ContractsController.deleteContract']
        );

        Route::post(
            '/contracts/process',
            ['uses' => 'ContractsController@processContracts', 'as' => 'ContractsController.processContracts']
        );
        Route::post('/jobs/process', ['uses' => 'JobsController@processJobs', 'as' => 'JobsController.processJobs']);

        Route::post('/jobs/undelete', ['uses' => 'JobsController@undeleteJob', 'as' => 'JobsController.undeleteJob']);
        Route::post('/jobs/delete', ['uses' => 'JobsController@deleteJob', 'as' => 'JobsController.deleteJob']);

        Route::post('/support', ['uses' => 'SupportController@getSupport', 'as' => 'SupportController.getSupport']);
    }
);


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

