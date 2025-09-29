<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Files::index');
$routes->get('/files', 'Files::index');
$routes->post('/files/upload', 'Files::upload');
$routes->get('/files/view/(:num)', 'Files::view/$1');
$routes->post('/files/add-row/(:id)', 'Files::addRow/$1');
$routes->post('/files/update-row/(:id)', 'Files::updateRow/$1');
$routes->post('/files/delete-row/(:id)', 'Files::deleteRow/$1');
$routes->post('/files/delete/(:id)', 'Files::deleteFile/$1');
$routes->get('/files/download/(:id)', 'Files::download/$1');
$routes->get('/files/export/excel/(:id)', 'Files::exportExcel/$1');
$routes->get('/files/export/pdf/(:id)', 'Files::exportPdf/$1');