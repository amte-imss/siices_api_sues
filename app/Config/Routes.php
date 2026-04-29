<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('activosPosgrado/obtenerFila', 'ActivosPosgrado::obtenerFila');
$routes->post('egresados/obtenerFila', 'Egresados::obtenerFila');

