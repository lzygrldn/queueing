<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Update PSA to BREQS
$routes->get('update-psa-to-breqs', 'UpdateController::updatePsaToBreqs');

// Landing page
$routes->get('/', 'Home::index');

// Admin Routes
$routes->get('admin', 'Admin::index');
$routes->post('admin/login', 'Admin::login');
$routes->get('admin/logout', 'Admin::logout');
$routes->post('admin/logout', 'Admin::logout');
$routes->get('admin/update-psa-to-breqs', 'Admin::updatePsaToBreqs');
$routes->post('admin/update-window-status', 'Admin::updateWindowStatus');
$routes->post('admin/reset-daily-stats', 'Admin::resetDailyStats');
$routes->post('admin/reset-monthly-stats', 'Admin::resetMonthlyStats');
$routes->get('admin/kiosk', 'Admin::kiosk');
$routes->get('admin/display', 'Admin::display');
$routes->post('admin/skip/(:num)', 'Admin::skipQueue/$1');
$routes->post('admin/reset-windows', 'Admin::resetWindows');
$routes->post('admin/reset-numbers', 'Admin::resetNumbers');
$routes->get('admin/get-data', 'Admin::getData');
$routes->get('admin/customer-records', 'CustomerRecords::index');

// Window Routes
$routes->get('window', 'Window::select');
$routes->get('window/(:num)', 'Window::index/$1');
$routes->post('window/callNext/(:num)', 'Window::callNext/$1');
$routes->post('window/complete/(:num)', 'Window::complete/$1');
$routes->post('window/skip/(:num)', 'Window::skip/$1');
$routes->get('window/data/(:num)', 'Window::getData/$1');
$routes->get('window/getCustomerData/(:any)', 'Window::getCustomerData/$1');
$routes->get('window/getCustomerDataByTransaction/(:any)', 'Window::getCustomerDataByTransaction/$1');
$routes->get('window/searchCustomers', 'Window::searchCustomers');
$routes->post('window/saveCustomer', 'Window::saveCustomer');
$routes->post('window/autoServeFirst/(:num)', 'Window::autoServeFirst/$1');

// Queue Routes
$routes->get('queue', 'QueueController::index');
$routes->post('queue/print', 'QueueController::printTicket');

// Display Routes
$routes->get('display', 'Display::index');
$routes->get('display/data', 'Display::getData');

// Customer Records Routes
$routes->get('customerRecords', 'CustomerRecords::index');
$routes->get('customerRecords/getData', 'CustomerRecords::getData');
$routes->post('customerRecords/createTicket', 'CustomerRecords::createTicket');
$routes->get('customerRecords/convertTimeColumns', 'CustomerRecords::convertTimeColumns');
$routes->get('customerRecords/updateDatabase', 'CustomerRecords::updateDatabase');
$routes->get('customerRecords/runMigration', 'CustomerRecords::runMigration');
$routes->get('customerRecords/export', 'CustomerRecords::export');
