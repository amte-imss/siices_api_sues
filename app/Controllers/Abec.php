<?php

namespace App\Controllers;

use App\Models\AbecModel;
use App\Models\controlTiempoModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Abec Controller
 * 
 * Controlador para gestionar endpoints de cursos ABEC.
 * Implementa seguridad completa (autenticación, IP, tiempo).
 * 
 * @package App\Controllers
 * @version 1.0.0
 */
class Abec extends ResourceController
{
    protected AbecModel $abecModel;
    protected $format = 'json';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->abecModel = new AbecModel();
    }
    
    // Nota: Los endpoints `obtenerCursos`, `obtenerCurso`, `obtenerAlumnos` y
    // `obtenerEstadisticas` fueron eliminados del enrutamiento y removidos del
    // controlador para mantener el API reducido. La única operación disponible
    // en este controlador es `obtenerCatalogos` (ver método abajo).

    /**
     * Obtiene catálogos (Años, OOAD, UMAE, Tipo de Sesión)
     * 
     * POST /api/abec/obtenerCatalogos
     * 
     * @return ResponseInterface JSON con catálogos solicitados
     * 
     * Parámetros esperados (JSON):
     * {
     *     "catalogos": ["anios", "ooad", "umae", "tipo_sesion"]
     * }
     * 
     * Si no se especifican catálogos, se retornan todos.
     * 
     * Valores válidos:
     * - "anios": Años desde 2022 hasta el año actual
     * - "ooad": Delegaciones con estado
     * - "umae": Unidades UMAE con delegación y estado
     * - "tipo_sesion": Tipos de sesión ABEC
     */
    public function obtenerCatalogos(): ResponseInterface
    {
        try {
            // Validar seguridad
            if (!$this->validarSeguridad()) {
                return $this->respond(
                    ["error" => "Acceso no autorizado"],
                    401
                );
            }

            // Obtener parámetros JSON
            $json = $this->request->getJSON(true);
            $catalogos = $json['catalogos'] ?? [];

            // Validar que sea un array
            if (!is_array($catalogos)) {
                return $this->respond(
                    ["error" => "'catalogos' debe ser un array"],
                    400
                );
            }

            // Validar catálogos válidos
            $catalogosValidos = ['anios', 'ooad', 'umae', 'tipo_sesion'];
            foreach ($catalogos as $catalogo) {
                if (!in_array($catalogo, $catalogosValidos)) {
                    return $this->respond(
                        ["error" => "Catálogo inválido: '{$catalogo}'. Válidos: " . implode(', ', $catalogosValidos)],
                        400
                    );
                }
            }

            // Obtener catálogos
            $datos = $this->abecModel->obtenerCatalogos($catalogos);

            return $this->respond([
                "success" => true,
                "data" => $datos,
                "catalogosSolicitados" => empty($catalogos) ? $catalogosValidos : $catalogos,
                "timestamp" => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            log_message('error', 'AbecController::obtenerCatalogos - Error: ' . $e->getMessage());
            return $this->respond(
                ["error" => "Error al obtener catálogos"],
                500
            );
        }
    }

    private function obtenerFiltrosDesdeJson($json): array
    {
        $filters = [];
        if (isset($json['anios'])) {
            $anios = is_array($json['anios']) 
                ? array_map('intval', $json['anios']) 
                : array_filter(array_map('intval', explode(',', $json['anios'])));

            $filters['anios'] = array_values(array_filter($anios, fn($a) => $a > 1900));
        }
        if (isset($json['id_ooad'])) $filters['id_ooad'] = $this->parseIds($json['id_ooad']);
        if (isset($json['id_umae'])) $filters['id_umae'] = $this->parseIds($json['id_umae']);
        if (isset($json['id_tipo_sesion'])) $filters['id_tipo_sesion'] = $this->parseIds($json['id_tipo_sesion']);
        if (isset($json['curso'])) $filters['curso'] = trim($json['curso']);

        return $filters;
    }

    /**
     * Obtiene estadísticas agregadas (totales) desde la vista vw_abec_curso_alumno
     * 
     * POST /api/abec/obtenerEstadisticas
     * 
     * Filtros opcionales (JSON):
     * - anios: año (int) — si se envía, filtra fechaSesion BETWEEN 'YYYY-01-01 00:00:00' AND 'YYYY-12-31 23:59:59'
     * - id_ooad: filtra del_cve
     * - id_umae: filtra sde_cve
     * - id_tipo_sesion: filtra id_tipo_sesion
     * 
     * @return ResponseInterface
     */
    public function obtenerEstadisticas(): ResponseInterface
    {
        try {
            /*if (!$this->validarSeguridad()) {
                return $this->respond(["error" => "Acceso no autorizado"], 401);
            }

            $json = $this->request->getJSON(true);
            if ($json === null) {
                return $this->respond(["error" => "JSON inválido o faltante"], 400);
            }*/

            $json = $this->validarSeguridadYJson();

            $filters = $this->obtenerFiltrosDesdeJson($json);

            /*
            // anios: aceptar tanto número como array (tomar primer valor)
            if (isset($json['anios'])) {
                $anioVal = $json['anios'];
                if (is_array($anioVal)) {
                    $anioVal = $anioVal[0] ?? null;
                }
                if ($anioVal !== null) {
                    $anio = $this->validarInteger($anioVal, 1900, 2100, 'Año inválido');
                    $filters['anio'] = $anio;
                }
            }

            if (isset($json['id_ooad'])) {
                $filters['id_ooad'] = $this->validarInteger($json['id_ooad'], 1, PHP_INT_MAX, 'id_ooad inválido');
            }

            if (isset($json['id_umae'])) {
                $filters['id_umae'] = $this->validarInteger($json['id_umae'], 1, PHP_INT_MAX, 'id_umae inválido');
            }

            if (isset($json['id_tipo_sesion'])) {
                $filters['id_tipo_sesion'] = $this->validarInteger($json['id_tipo_sesion'], 1, PHP_INT_MAX, 'id_tipo_sesion inválido');
            }*/

            $result = $this->abecModel->obtenerEstadisticas($filters);

            return $this->respond([
                'success' => true,
                'data' => $result,
                'filters' => $filters,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::obtenerEstadisticas - Error: ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    protected function validarSeguridadYJson(): array
    {
        if (!$this->validarSeguridad()) {
            throw new \InvalidArgumentException('Acceso no autorizado');
            //return $this->respond(["error" => "Acceso no autorizado"], 401);
        }

        $json = $this->request->getJSON(true);
        if ($json === null || !is_array($json)) {
            throw new \InvalidArgumentException('JSON inválido o cuerpo de la petición vacío');
            //return $this->respond(["error" => "JSON inválido o cuerpo de la petición vacío"], 400);
        }

        return $json;
    }

    /**
     * Estadísticas: courses/students by OOAD (umae = false)
     * POST /api/abec/estadisticas/ooad
     */
    public function estadisticasPorOOAD(): ResponseInterface
    {
        try {
            /*if (!$this->validarSeguridad()) {
                return $this->respond(['error' => 'Acceso no autorizado'], 401);
            }*/
            $json = $this->validarSeguridadYJson();

            $filters = $this->obtenerFiltrosDesdeJson($json);

            $data = $this->abecModel->estadisticasPorOOAD($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::estadisticasPorOOAD - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener estadísticas OOAD'], 500);
        }
    }

    

    /**
     * Estadísticas: courses/students by UMAE (umae = true)
     * POST /api/abec/estadisticas/umae
     */
    public function estadisticasPorUMAE(): ResponseInterface
    {
        try {
            $json = $this->validarSeguridadYJson();

            $filters = $this->obtenerFiltrosDesdeJson($json);

            $data = $this->abecModel->estadisticasPorUMAE($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::estadisticasPorUMAE - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener estadísticas UMAE'], 500);
        }
    }

    /**
     * Categoría por alumno
     * POST /api/abec/estadisticas/categoria
     */
    public function categoriaPorAlumno(): ResponseInterface
    {
        try {
            $json = $this->validarSeguridadYJson();
            
            $filters = $this->obtenerFiltrosDesdeJson($json);
            /*if (isset($json['anios'])) {
                $anioVal = is_array($json['anios']) ? ($json['anios'][0] ?? null) : $json['anios'];
                if ($anioVal !== null) $filters['anio'] = $this->validarInteger($anioVal, 1900, 2100);
            }
            if (isset($json['id_ooad'])) $filters['id_ooad'] = $this->validarInteger($json['id_ooad'], 1, PHP_INT_MAX);
            if (isset($json['id_umae'])) $filters['id_umae'] = $this->validarInteger($json['id_umae'], 1, PHP_INT_MAX);
            if (isset($json['id_tipo_sesion'])) $filters['id_tipo_sesion'] = $this->validarInteger($json['id_tipo_sesion'], 1, PHP_INT_MAX);*/

            $data = $this->abecModel->categoriaPorAlumno($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::categoriaPorAlumno - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener categoría por alumno'], 500);
        }
    }

    /**
     * Estadísticas por mes
     * POST /api/abec/estadisticas/mes
     */
    public function porMes(): ResponseInterface
    {
        try {
            $json = $this->validarSeguridadYJson();
            
            $filters = $this->obtenerFiltrosDesdeJson($json);
            /*if (isset($json['anios'])) {
                $anioVal = is_array($json['anios']) ? ($json['anios'][0] ?? null) : $json['anios'];
                if ($anioVal !== null) $filters['anio'] = $this->validarInteger($anioVal, 1900, 2100);
            }
            if (isset($json['id_ooad'])) $filters['id_ooad'] = $this->validarInteger($json['id_ooad'], 1, PHP_INT_MAX);
            if (isset($json['id_umae'])) $filters['id_umae'] = $this->validarInteger($json['id_umae'], 1, PHP_INT_MAX);
            if (isset($json['id_tipo_sesion'])) $filters['id_tipo_sesion'] = $this->validarInteger($json['id_tipo_sesion'], 1, PHP_INT_MAX);*/

            $data = $this->abecModel->porMes($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::porMes - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener estadísticas por mes'], 500);
        }
    }

    /**
     * Estadísticas por sexo
     * POST /api/abec/estadisticas/sexo
     */
    public function porSexo(): ResponseInterface
    {
        try {
            $json = $this->validarSeguridadYJson();
            $filters = $this->obtenerFiltrosDesdeJson($json);
            
            /*if (isset($json['anios'])) {
                $anioVal = is_array($json['anios']) ? ($json['anios'][0] ?? null) : $json['anios'];
                if ($anioVal !== null) $filters['anio'] = $this->validarInteger($anioVal, 1900, 2100);
            }
            if (isset($json['id_ooad'])) $filters['id_ooad'] = $this->validarInteger($json['id_ooad'], 1, PHP_INT_MAX);
            if (isset($json['id_umae'])) $filters['id_umae'] = $this->validarInteger($json['id_umae'], 1, PHP_INT_MAX);
            if (isset($json['id_tipo_sesion'])) $filters['id_tipo_sesion'] = $this->validarInteger($json['id_tipo_sesion'], 1, PHP_INT_MAX);*/

            $data = $this->abecModel->porSexo($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::porSexo - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener estadísticas por sexo'], 500);
        }
    }

    /**
     * Listado de cursos (agrupado por curso)
     * POST /api/abec/listadoCursos
     */
    public function listadoCursos(): ResponseInterface
    {
        try {
            $json = $this->validarSeguridadYJson();
            $filters = $this->obtenerFiltrosDesdeJson($json);
            /*if (isset($json['anios'])) {
                $anioVal = is_array($json['anios']) ? ($json['anios'][0] ?? null) : $json['anios'];
                if ($anioVal !== null) $filters['anio'] = $this->validarInteger($anioVal, 1900, 2100);
            }
            if (isset($json['id_ooad'])) $filters['id_ooad'] = $this->validarInteger($json['id_ooad'], 1, PHP_INT_MAX);
            if (isset($json['id_umae'])) $filters['id_umae'] = $this->validarInteger($json['id_umae'], 1, PHP_INT_MAX);
            if (isset($json['id_tipo_sesion'])) $filters['id_tipo_sesion'] = $this->validarInteger($json['id_tipo_sesion'], 1, PHP_INT_MAX);*/
            
            $data = $this->abecModel->listadoCursos($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::listadoCursos - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener listado de cursos'], 500);
        }
    }

    /**
     * Detalle de alumnos por curso (GET con id)
     * GET /api/abec/detalleAlumno/{id}
     */
    public function detalleAlumnoXId($id = null): ResponseInterface
    {
        try {
            if (!$this->validarSeguridad()) return $this->respond(['error' => 'Acceso no autorizado'], 401);
            $idVal = $this->validarInteger($id, 1, PHP_INT_MAX, 'ID de curso inválido');
            $data = $this->abecModel->detalleAlumnosPorCurso($idVal);
            return $this->respond(['success' => true, 'data' => $data]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::detalleAlumnos - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener detalle de alumnos'], 500);
        }
    }

    /**
     * Listado de cursos (agrupado por curso)
     * POST /api/abec/detalleAlumnos
     */
    public function detalleAlumnos(): ResponseInterface
    {
        try {
            $json = $this->validarSeguridadYJson();
            $filters = $this->obtenerFiltrosDesdeJson($json);
            
            $data = $this->abecModel->detalleAlumnos($filters);
            return $this->respond(['success' => true, 'data' => $data, 'filters' => $filters]);

        } catch (\InvalidArgumentException $e) {
            return $this->respond(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'AbecController::detalleAlumnos - ' . $e->getMessage());
            return $this->respond(['error' => 'Error al obtener detalle de alumnos'], 500);
        }
    }

    
    /**
     * Valida que el cliente tenga acceso (autenticación, IP, tiempo)
     * 
     * @return bool true si tiene acceso, false en caso contrario
     */
    private function validarSeguridad(): bool
    {
        // Validar contraseña y usuario
        if (CONTROL_PASS) {
            $usuario = $this->request->getHeaderLine('API-USER');
            $password = $this->request->getHeaderLine('API-PASS');

            if ($usuario !== USUARIO || $password !== PASSWORD) {
                log_message('warning', 'Intento de acceso con credenciales inválidas desde ' . $this->request->getIPAddress());
                return false;
            }
        }

        // Validar IP
        if (CONTROL_IP) {
            $clienteIp = $this->request->getIPAddress();
            if ($clienteIp !== IP_CLIENTE) {
                log_message('warning', 'Intento de acceso desde IP no autorizada: ' . $clienteIp);
                return false;
            }
        }

        // Validar período de tiempo
        if (CONTROL_FECHA) {
            $tiempoModel = new controlTiempoModel();
            $pase = $tiempoModel->obtenerTiempo();

            if (!$pase) {
                log_message('warning', 'Intento de acceso fuera del período autorizado');
                return false;
            }
        }

        return true;
    }

    /**
     * Valida un número entero dentro de un rango
     * 
     * @param mixed $valor Valor a validar
     * @param int $minimo Valor mínimo permitido
     * @param int $maximo Valor máximo permitido
     * @param string $mensaje Mensaje de error personalizado
     * @return int Valor validado
     * @throws InvalidArgumentException Si la validación falla
     */
    private function validarInteger($valor, int $minimo = 0, int $maximo = PHP_INT_MAX, string $mensaje = ""): int
    {
        if (!is_numeric($valor)) {
            throw new \InvalidArgumentException($mensaje ?: "Valor debe ser numérico");
        }

        $valor = (int)$valor;

        if ($valor < $minimo || $valor > $maximo) {
            throw new \InvalidArgumentException($mensaje ?: "Valor debe estar entre {$minimo} y {$maximo}");
        }

        return $valor;
    }

    /**
     * Maneja errores de método no permitido
     * 
     * @return ResponseInterface JSON con error
     */
    public function metodoNoPermitido(): ResponseInterface
    {
        return $this->respond(
            ["error" => "Método HTTP no permitido"],
            405
        );
    }
    
    // Función auxiliar para sanitizar y convertir a array de enteros (soporta array o string separado por comas)
    public function parseIds($input): array 
    {
        if (empty($input)) {
            return [];
        }
        $items = is_array($input) ? $input : explode(',', (string)$input);
        return array_values(array_filter(array_map('intval', array_map('trim', $items)), fn($id) => $id > 0));
    }
}
