
let login = require('./components/auth/login.vue').default;
let register = require('./components/auth/register.vue').default
let forget = require('./components/auth/forget.vue').default
let logout = require('./components/auth/logout.vue').default

//End Aithetication
let home = require('./components/home.vue').default

//Employee Component
/* let storeEmployee = require('./components/employee/create.vue').default
let all_employee = require('./components/employee/index.vue').default
let editEmployee = require('./components/employee/edit.vue').default */


/* let diagnose_from = require('./components/employee/diagnose.vue').default
let diagnose_from_dctr = require('./components/Forms/diagnose.vue').default
 */

let userslist = require('./components/users/index.vue').default
let usersadd = require('./components/users/create.vue').default

//Products
let product_list = require('./components/products/index.vue').default
let product_add = require('./components/products/create.vue').default

//Company
let company_list = require('./components/company/index.vue').default
let company_add = require('./components/company/create.vue').default

//Collections
let collection_list = require('./components/collections/index.vue').default
let collection_add = require('./components/collections/create.vue').default
let collection_reports = require('./components/collections/Reports.vue').default

//Transaction
let transaction = require('./components/transactions/create.vue').default
let transaction_list = require('./components/transactions/index.vue').default
let transaction_report = require('./components/transactions/DailyReports.vue').default

//reports
let reports = require('./components/transactions/Reports.vue').default
let yearly_report = require('./components/transactions/YearlyReports.vue').default

//ReceivedProducts
let rproduct_list = require('./components/rec_products/index.vue').default
let rproduct_add = require('./components/rec_products/create.vue').default


let stocks = require('./components/stocks/index.vue').default

//Patient
let patients_list = require('./components/patients/index.vue').default
let patients_add = require('./components/patients/create.vue').default

//Doctors
let doctors_list = require('./components/doctors/index.vue').default
let doctors_add = require('./components/doctors/create.vue').default

//Doctors
let batch_list = require('./components/batch/index.vue').default
let batch_add = require('./components/batch/create.vue').default

//CoPay
let copay_list = require('./components/copay/index.vue').default
//let copay_add = require('./components/copay/create.vue').default
//let copay_reports = require('./components/copay/Reports.vue').default

//CoPay
let revenue_list = require('./components/revenue/index.vue').default
let revenue_add = require('./components/revenue/create.vue').default
let revenue_reports = require('./components/revenue/Reports.vue').default

//Phic
let phic_list = require('./components/phic/index.vue').default
//let phic_add = require('./components/phic/create.vue').default
//let phic_reports = require('./components/phic/Reports.vue').default

//Acpn
let acpn_list = require('./components/acpn/index.vue').default
let acpn_report = require('./components/acpn/report.vue').default

//census
let census_doctor = require('./components/census/census_doctor.vue').default
let census_patient = require('./components/census/census_patient.vue').default

//logs
let logs = require('./components/logs/index.vue').default

//ledger
let ledger = require('./components/ledger/index.vue').default

//payment
let payment = require('./components/payment/create.vue').default

//sales
let sales = require('./components/sales/index.vue').default
/*
    path, component & name should be the same inorder to work
*/

//Sessions
let manage_session = require('./components/sessions/index.vue').default

export const routes = [
    { path: '/', component: login, name: '/' },
    { path: '/register', component: register, name: 'register' },
    { path: '/forget', component: forget, name: 'logout' },
    { path: '/logout', component: logout, name: 'forget' },
    { path: '/home', component: home, name: 'home' },

    //employee routes
    /* { path: '/add_employee', component: storeEmployee, name: 'storeEmployee' },
    { path: '/all_employee', component: all_employee, name: 'all_employee' },
    { path: '/edit-employee/:id', component: editEmployee, name: 'edit-employee' },
    { path: '/diagnose-from/:id', component: diagnose_from, name: 'diagnose-from' },
    { path: '/diagnose-from-dctr/:id', component: diagnose_from_dctr, name: 'diagnose-from-dctr' }, */


    //Users
    { path: '/userslist', component: userslist, name: 'userslist' },
    { path: '/usersadd', component: usersadd, name: 'usersadd' },
    
    //Products
    { path: '/product_list', component: product_list, name: 'product_list' },
    { path: '/product_add/:id', component: product_add, name: 'product_add' },

    
    //Batches
    { path: '/batch_list', component: batch_list, name: 'batch_list' },
    { path: '/batch_add/:id', component: batch_add, name: 'batch_add' },


    //Company
    { path: '/company_list', component: company_list, name: 'company_list' },
    { path: '/company_add/:id', component: company_add, name: 'company_add' },
    
    //Transaction
    //{ path: '/transaction/:id', component: transaction, name: 'transaction' },
    { path: '/manage_session', component: manage_session, name: 'manage_session' },
    //{ path: '/transaction_report', component: transaction_report, name: 'transaction_report' },
    
    //Reports
    { path: '/reports', component: reports, name: 'reports' },
    { path: '/yearly_report', component: yearly_report, name: 'yearly_report' },
    
    //Products
    { path: '/rproduct_list', component: rproduct_list, name: 'rproduct_list' },
    { path: '/rproduct_add/:id', component: rproduct_add, name: 'rproduct_add' },
    
    { path: '/stocks', component: stocks, name: 'stocks' },

    //Collections
    { path: '/collection_list', component: collection_list, name: 'collection_list' },
    { path: '/collection_add/:id', component: collection_add, name: 'collection_add' },
    { path: '/collection_reports', component: collection_reports, name: 'collection_reports' },

    //Patients
    { path: '/patients_list', component: patients_list, name: 'patients_list' },
    { path: '/patients_add/:id', component: patients_add, name: 'patients_add' },
    
    //Doctors
    { path: '/doctors_list', component: doctors_list, name: 'doctors_list' },
    { path: '/doctors_add/:id', component: doctors_add, name: 'doctors_add' },

    //CoPay
    { path: '/copay_list', component: copay_list, name: 'copay_list' },
    //{ path: '/copay_add/:id', component: copay_add, name: 'copay_add' },
    //{ path: '/copay_reports', component: copay_reports, name: 'copay_reports' },

    //Phic
    { path: '/phic_list', component: phic_list, name: 'phic_list' },
    //{ path: '/phic_add/:id', component: phic_add, name: 'phic_add' },
    //{ path: '/phic_reports', component: phic_reports, name: 'phic_reports' },

    //Acpn
    { path: '/acpn_list', component: acpn_list, name: 'acpn_list' },    
    { path: '/acpn_report', component: acpn_report, name: 'acpn_report' },    

    //census
    { path: '/census_doctor', component: census_doctor, name: 'census_doctor' },
    { path: '/census_patient', component: census_patient, name: 'census_patient' },

    //Revenue
    { path: '/revenue_list', component: revenue_list, name: 'revenue_list' },
    { path: '/revenue_reports', component: revenue_reports, name: 'revenue_reports' },

    //Logs
    { path: '/logs', component: logs, name: 'logs' },

    //Ledger
    { path: '/ledger', component: ledger, name: 'ledger' },

    //sales
    { path: '/sales', component: sales, name: 'sales' },

    //Payment
    { path: '/payment', component: payment, name: 'payment' },

    
    { path: '/transaction/:id', component: transaction, name: 'transaction' },
    { path: '/transaction_list', component: transaction_list, name: 'transaction_list' },
    { path: '/transaction_report', component: transaction_report, name: 'transaction_report' },
]


