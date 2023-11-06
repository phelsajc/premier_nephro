<?php

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('addEmployee', 'AuthController@refresh');

    // try to transfer methods here
    
});
Route::get('validate', 'AuthController@checkToken');

Route::apiResource('/employee','Api\EmployeeController');


Route::match(['get','post'],'/filterEmployee','Api\EmployeeController@filterEmployee');
Route::match(['get','post'],'/patientEmployee','Api\PatientController@filterEmployee');
//Route::match(['get','post'],'/patientEmployee','Api\PatientController@filterEmployee_test');
Route::match(['get','post'],'/check_doctors_detail/{id}','Api\PatientController@check_doctors_detail');

Route::match(['get','post'],'saveInitialData','Api\PatientController@saveInitialData');
Route::match(['get','post'],'searchMedicine','MedicineController@searchMedicine');
Route::match(['get','post'],'searchDiagnostic','MedicineController@searchDiagnostic');
Route::match(['get','post'],'getPxInfo/{pspat}','Api\PatientController@getPxInfo');
Route::match(['get','post'],'getFormDetail/{id}','Api\PatientController@EditInitialData');
Route::match(['get','post'],'upDateHPE','Api\PatientController@upDateHPE');
Route::match(['get','post'],'getDiagnosisInfo/{pspat}','Api\PatientController@getDiagnosisInfo');
Route::match(['get','post'],'addMedicine/{method}/{pspat}/{diagnosis_id}','PrescriptionController@store');
Route::match(['get','post'],'getrequency','PrescriptionController@getrequency');
Route::match(['get','post'],'getPrescribeMedicine/{id}','PrescriptionController@getPrescribeMedicine');
Route::match(['get','post'],'getPrecriptionDetail/{id}','PrescriptionController@getPrecriptionDetail');
Route::match(['get','post'],'updateMedicine/{method}/{diagnosis_id}','PrescriptionController@updateMedicine');
Route::match(['get','post'],'addDiagnostics','PrescriptionController@addDiagnostics');
Route::match(['get','post'],'print_prescription/{id}/{doctor}','PDFController@printPrescription');
Route::match(['get','post'],'getPrescribeLabs/{id}','PrescriptionController@getPrescribeLabs');
Route::match(['get','post'],'destroyLab/{id}','PrescriptionController@destroyLab');
Route::match(['get','post'],'destroyMeds/{id}','PrescriptionController@destroyMeds');


Route::match(['get','post'],'addusers','UserController@registerUser');
Route::match(['get','post'],'listusers','UserController@getAllUsers');

Route::match(['get','post'],'show_frequency/{id}','PrescriptionController@show_frequency');

/**
 * St.Marina
 */

#Products
Route::match(['get','post'],'products-add','ProductController@store');
Route::match(['get','post'],'products-update','ProductController@update');
Route::match(['get','post'],'products','ProductController@index');
Route::match(['get','post'],'products-detail/{id}','ProductController@edit');
Route::match(['get','post'],'products-delete/{id}','ProductController@delete');
Route::match(['get','post'],'searchProduct','ProductController@searchProduct');
Route::match(['get','post'],'getProducts','ProductController@getProducts');
Route::match(['get','post'],'stockInventory','ProductController@stockInventory');
Route::match(['get','post'],'product-find','ProductController@find');

#Company
Route::match(['get','post'],'company-add','CompanyController@store');
Route::match(['get','post'],'company-update','CompanyController@update');
Route::match(['get','post'],'company','CompanyController@index');
Route::match(['get','post'],'company-detail/{id}','CompanyController@edit');
Route::match(['get','post', 'delete'],'company-delete/{id}','CompanyController@delete');
Route::match(['get','post'],'getCompanies','CompanyController@getCompanies');
Route::match(['get','post'],'company-find','CompanyController@find');

#Transaction
Route::match(['get','post'],'saveTransaction','TransactionController@store');
Route::match(['get','post'],'updateTransaction','TransactionController@update');
Route::match(['get','post'],'getTransaction/{id}','TransactionController@getTransaction');
Route::match(['get','post'],'transactions','TransactionController@index');
Route::match(['get','post'],'getTransactionHeader/{id}','TransactionController@getTransactionHeader');
Route::match(['get','post'],'report','TransactionController@report');
Route::match(['get','post'],'DailyReport','TransactionController@DailyReport');
Route::match(['get','post'],'YearlyReport','TransactionController@yearly_report');


#ReceivedProducts
Route::match(['get','post'],'rec_products-add','ReceivedProductController@store');
Route::match(['get','post'],'rec_products-update','ReceivedProductController@update');
Route::match(['get','post'],'rec_products','ReceivedProductController@index');
Route::match(['get','post'],'rec_ledgers/{id}','ReceivedProductController@ledger');
Route::match(['get','post'],'rec_products-detail/{id}','ReceivedProductController@edit');
Route::match(['get','post'],'rec_products-delete/{id}','ReceivedProductController@delete');
Route::match(['get','post'],'rec_searchProduct','ReceivedProductController@searchProduct');
Route::match(['get','post'],'rec_inventory','ReceivedProductController@inventory');
Route::match(['get','post'],'rec_payment','ReceivedProductController@payment');
Route::match(['get','post'],'getLastBalance','ReceivedProductController@getLastBalance');
Route::match(['get','post'],'checksales','ReceivedProductController@sales');


#Collections
Route::match(['get','post'],'collection-add','CollectionController@store');
Route::match(['get','post'],'collection-update','CollectionController@update');
Route::match(['get','post'],'collection','CollectionController@index');
Route::match(['get','post'],'collection-detail/{id}','CollectionController@edit');
Route::match(['get','post'],'collection-delete/{id}','CollectionController@delete');
Route::match(['get','post'],'collection-report','CollectionController@reports');

#Patients
Route::match(['get','post'],'patients-import','PatientsController@import');
Route::match(['get','post'],'patients-add','PatientsController@store');
Route::match(['get','post'],'patients-update','PatientsController@update');
Route::match(['get','post'],'patients','PatientsController@index');
Route::match(['get','post'],'patients-detail/{id}','PatientsController@edit');
Route::match(['get','post'],'patients-delete/{id}','PatientsController@delete');
Route::match(['get','post'],'patients-find','PatientsController@find');


#Docotrs
Route::match(['get','post'],'doctors-import','DoctorsController@import');
Route::match(['get','post'],'doctors-add','DoctorsController@store');
Route::match(['get','post'],'doctors-update','DoctorsController@update');
Route::match(['get','post'],'doctors','DoctorsController@index');
Route::match(['get','post'],'doctors-detail/{id}','DoctorsController@edit');
Route::match(['get','post'],'doctors-delete/{id}','DoctorsController@delete');
Route::match(['get','post'],'getDoctors','DoctorsController@getDoctors');

#Batches
Route::match(['get','post'],'batch-add','BatchController@store');
Route::match(['get','post'],'batch-update','BatchController@update');
Route::match(['get','post'],'batches','BatchController@index');
Route::match(['get','post'],'batch-detail/{id}','BatchController@edit');
Route::match(['get','post'],'batch-delete/{id}','BatchController@delete');
Route::match(['get','post'],'get-batches','BatchController@batches');

#Schedule || Session
Route::match(['get','post'],'schedule-import','ScheduleController@import');
Route::match(['get','post'],'schedule-add','ScheduleController@store');
Route::match(['get','post'],'schedule-update','ScheduleController@update');
Route::match(['get','post'],'schedule','ScheduleController@index');
Route::match(['get','post'],'schedule-detail/{id}','ScheduleController@edit');
Route::match(['get','post'],'schedule-delete/{id}','ScheduleController@delete');


#Copay
Route::match(['get','post'],'copay-report','CopayController@report');
Route::match(['get','post'],'pdf','CopayController@Exportreport');

#PHIC
Route::match(['get','post'],'phic-report','PHICController@report');
Route::match(['get','post'],'phic-update','PHICController@update');
Route::match(['get','post'],'phic-edit/{id}','PHICController@edit');
Route::match(['get','post'],'phic-summary-report','PHICController@report_summary');

#acpn
Route::match(['get','post'],'acpn-report','PHICController@acpn_report');
Route::match(['get','post'],'acpn-report-list','PHICController@acpn_report_list');

#Census
Route::match(['get','post'],'census-report','CensusController@report');
Route::match(['get','post'],'census_px-report','CensusController@report_px');
Route::match(['get','post'],'revenue-report','CensusController@revenue');

#Census
Route::match(['get','post'],'log-report','LogsController@report');
















