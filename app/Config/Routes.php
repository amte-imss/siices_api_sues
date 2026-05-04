<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// ========== ENDPOINT: ActivosPosgrado ==========
$routes->post('activosPosgrado/obtenerFila', 'ActivosPosgrado::obtenerFila');
$routes->post('egresados/obtenerFila', 'Egresados::obtenerFila');

// ========== ENDPOINT: Abec (Mejorado con seguridad) ==========
$routes->post('abec/obtenerCatalogos', 'Abec::obtenerCatalogos');
// Estadísticas agregadas con filtros opcionales (anios, id_ooad, id_umae, id_tipo_sesion)
$routes->post('abec/obtenerEstadisticas', 'Abec::obtenerEstadisticas');
// Agregados: informes y listados derivados de vw_abec_curso_alumno
$routes->post('abec/estadisticas/ooad', 'Abec::estadisticasPorOOAD');
$routes->post('abec/estadisticas/umae', 'Abec::estadisticasPorUMAE');
$routes->post('abec/estadisticas/categoria', 'Abec::categoriaPorAlumno');
$routes->post('abec/estadisticas/mes', 'Abec::porMes');
$routes->post('abec/estadisticas/sexo', 'Abec::porSexo');
$routes->post('abec/listadoCursos', 'Abec::listadoCursos');
$routes->get('abec/detalleAlumnos/(:num)', 'Abec::detalleAlumnos/$1');

// ========== ENDPOINT: ProgramaAnual (Mejorado con seguridad) ==========
$routes->post('programa/obtenerCatalogos', 'ProgramaAnual::obtenerCatalogos');
// Estadísticas agregadas con filtros opcionales (anios, id_ooad, id_umae, id_tipo_sesion)
$routes->post('programa/obtenerEstadisticas', 'ProgramaAnual::obtenerEstadisticas');
// Agregados: informes y listados derivados de vw_abec_curso_alumno
$routes->post('programa/estadisticas/ooad', 'ProgramaAnual::estadisticasPorOOAD');
$routes->post('programa/estadisticas/umae', 'ProgramaAnual::estadisticasPorUMAE');
$routes->post('programa/estadisticas/categoria', 'ProgramaAnual::categoriaPorAlumno');
$routes->post('programa/estadisticas/mes', 'ProgramaAnual::porMes');
$routes->post('programa/estadisticas/sexo', 'ProgramaAnual::porSexo');
$routes->post('programa/listadoCursos', 'ProgramaAnual::listadoCursos');
$routes->get('programa/detalleAlumnos/(:num)', 'ProgramaAnual::detalleAlumnos/$1');
