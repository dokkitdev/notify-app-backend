<?php

Route::any('/webhooks', 'WebhookController@webhookAction');

Route::any('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::group(
    ['middleware' => ['CheckAdmin']],
    function () {
        Route::get('/dashboard', 'Admin\\DashboardController@getDashboard')->name('dashboard');


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
            ['prefix' => '/housing'],
            function () {
                Route::get('/', 'Admin\\HousingController@index')->name('housing.all');
                Route::get('/{id}/view', 'Admin\\HousingController@viewPdf')->name('housing.view');
                Route::post('/generate', 'Admin\\HousingController@generate')->name('housing.generate');
                Route::get('/import', 'Admin\\HousingController@import')->name('housing.import');
            }
        );

        Route::group(
            ['prefix' => '/private'],
            function () {
                Route::post('/reparse', 'Admin\\PrivateController@reparse')->name('private.reparse');
                Route::get('/', 'Admin\\PrivateController@index')->name('private.all');
                Route::get('/debit', 'Admin\\PrivateController@debitIndex')->name('private.debit');
                Route::get('/{id}/view', 'Admin\\PrivateController@viewPdf')->name('private.view');
                Route::post('/generate', 'Admin\\PrivateController@generate')->name('private.generate');
            }
        );


        Route::group(
            ['prefix' => '/reports'],
            function () {
                Route::get('/', 'Admin\\ReportController@indexReports')->name('reports.all');
                Route::post('/generate', 'Admin\\ReportController@generateReports')->name('reports.generate');
            }
        );

        Route::group(
            ['prefix' => '/asset-report'],
            function () {
                Route::get('/', 'Admin\\AssetReportController@index')->name('asset_report.index');
                Route::get('/download', 'Admin\\AssetReportController@downloadCsv')->name('asset_report.download_csv');
                Route::get('/schedule', 'Admin\\AssetReportController@scheduleValidation')->name(
                    'asset_report.schedule'
                );
            }
        );

        Route::group(
            ['prefix' => '/zero-report'],
            function () {
                Route::get('/', 'Admin\\ZeroReportController@index')->name('zero_report.index');
                Route::post('/', 'Admin\\ZeroReportController@requestToParseZeroReport');
            }
        );

        Route::group(
            ['prefix' => '/report-logs'],
            function () {
                Route::get('/', 'Admin\\ReportLogsController@index')->name('report_logs.index');
                Route::get('/{id}/download', 'Admin\\ReportLogsController@download')->name('report_logs.download');
            }
        );

        Route::get('/logs', 'Admin\\LogsController@index')->name('logs.index');
        Route::get('/logs/{id}', 'Admin\\LogsController@showEmails')->name('logs.emails');


        Route::group(
            ['prefix' => '/templates'],
            function () {
                Route::get('/', 'Admin\\TemplateController@all')->name('templates.all');
                Route::get('/{id}/edit', 'Admin\\TemplateController@getTemplate')->name('templates.edit');
                Route::put('/{id}/edit', 'Admin\\TemplateController@putTemplate')->name('templates.edit');
                Route::post('/upload_docx', 'Admin\\TemplateController@uploadDocx')->name('templates.upload_docx');
            }
        );

        Route::get('/users', ['uses' => 'Admin\UsersController@index', 'as' => 'users.index']);
        Route::resource(
            '/users',
            'Admin\UsersController',
            [
                'except' => [
                    'destroy',
                ],
            ]
        );
        Route::get('/users/{id}/edit', ['uses' => 'Admin\UsersController@edit', 'as' => 'users.edit']);
        Route::get('/users/destroy/{id}', ['uses' => 'Admin\UsersController@destroy', 'as' => 'users.destroy']);
        Route::put('/users/{id}', ['uses' => 'Admin\UsersController@update', 'as' => 'users.update']);

        Route::group(
            ['prefix' => '/parsing-logs'],
            function () {
                Route::get('/', 'Admin\\ParsingLogsController@index')->name('parsing_logs.all');
            }
        );
        Route::post('/support', 'Admin\\SupportController@getSupport')->name('support');

        Route::get('/profile', 'Admin\\ProfileController@getProfile')->name('profile');
        Route::post('/profile', 'Admin\\ProfileController@saveProfile');
//        ----not usedd
    }
);

//Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

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
