<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Landing page
$routes->get('/', 'Home::index');

// Admin Routes
$routes->get('admin', 'Admin::index');
$routes->post('admin/login', 'Admin::login');
$routes->get('admin/logout', 'Admin::logout');
$routes->post('admin/logout', 'Admin::logout');
$routes->get('admin/get-queue-data', 'Admin::getQueueData');
$routes->get('admin/kiosk', 'Admin::kiosk');
$routes->get('admin/display', 'Admin::display');
$routes->post('admin/complete/(:num)', 'Admin::completeQueue/$1');
$routes->post('admin/skip/(:num)', 'Admin::skipQueue/$1');
$routes->post('admin/reset-windows', 'Admin::resetWindows');
$routes->post('admin/reset-numbers', 'Admin::resetNumbers');
$routes->get('admin/get-data', 'Admin::getData');

// Window Routes
$routes->get('window', 'Window::index');
$routes->get('window/(:num)', 'Window::index/$1');
$routes->post('window/complete/(:num)', 'Window::complete/$1');
$routes->post('window/skip/(:num)', 'Window::skip/$1');
$routes->get('window/data/(:num)', 'Window::getData/$1');

// Kiosk Routes
$routes->get('kiosk', 'Kiosk::index');
$routes->post('kiosk/print', 'Kiosk::printTicket');

// Display Routes
$routes->get('display', 'Display::index');
$routes->get('display/data', 'Display::getData');
