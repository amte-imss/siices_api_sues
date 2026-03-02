<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AbecModel
 * 
 * Modelo para gestionar datos de cursos ABEC y sus participantes.
 * Incluye validación de seguridad y consultas optimizadas.
 * 
 * @package App\Models
 * @version 1.0.0
 */
class AbecModel extends Model
{
    protected $DBGroup = 'default';

    /**
     * Obtiene datos de cursos ABEC con filtros opcionales
     * 
     * @param array $filters Filtros opcionales (fechaInicio, fechaFin, folio, sde_cve, etc.)
     * @param int $limit Límite de registros
     * @param int $offset Offset para paginación
     * @return array Datos de cursos ABEC
     * @throws Exception Si hay error en la base de datos
     */
    public function obtenerCursosAbec(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('tbl_abec_curso a');

            // SELECT con campos específicos
            $builder->select(
                "a.fechaRegistro,
                a.folio,
                a.cTemaSesion AS curso,
                a.fechaSesion,
                b.cDescripcion AS tipo_sesion,
                CONCAT(g.EDO_NOM, ' ', f.DEL_NOM) AS ooad,
                h.SDE_NOM AS unidad,
                tur.cDescripcion AS turno,
                alum.matricula,
                CONCAT(alum.nombre, ' ', alum.apellidoPaterno, ' ', alum.apellidoMaterno) AS nombre_alumno,
                ii.cDescripcion AS Categoria,
                jj.cDescripcion AS Subcategoria"
            );

            // JOINs con validación de existencia de tablas
            $builder->join('tbl_abec_alumno alum', 'alum.id_abec_curso = a.id_abec_curso', 'LEFT');
            $builder->join('cat_general b', 'a.id_Gral_TipoSesion = b.id', 'LEFT');
            $builder->join('cat_general tur', 'a.id_Gral_Turno = tur.id', 'LEFT');
            $builder->join('edumed.ims_del_cat f', 'a.id_Del_Imss = f.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat g', 'f.edo_cve = g.edo_cve', 'LEFT');
            $builder->join('edumed.gra_sde_cat h', 'a.sde_cve = h.sde_cve', 'LEFT');
            $builder->join('cat_categoriacursos ii', 'ii.id_CategoriaCursos = alum.id_Categoria', 'LEFT');
            $builder->join('cat_subcategoriacursos jj', 'jj.id_SubCategoriaCursos = alum.id_SubCategoria', 'LEFT');

            // WHERE base: cEstatus debe estar definido
            $builder->where('a.cEstatus IS NOT NULL', null, false);

            // Filtros de fecha con validación
            if (!empty($filters['fechaInicio']) && !empty($filters['fechaFin'])) {
                $fechaInicio = $this->validarFecha($filters['fechaInicio']);
                $fechaFin = $this->validarFecha($filters['fechaFin']);
                $builder->where("a.fechaSesion BETWEEN '{$fechaInicio} 00:00:00' AND '{$fechaFin} 23:59:59'", null, false);
            } else {
                // Por defecto: año actual
                $anioActual = date('Y');
                $builder->where("YEAR(a.fechaSesion) = {$anioActual}", null, false);
            }

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('a.folio', $folio);
            }

            if (!empty($filters['sde_cve'])) {
                $sdeCve = (int)$filters['sde_cve'];
                $builder->where('a.sde_cve', $sdeCve);
            }

            if (!empty($filters['id_Del_Imss'])) {
                $idDelImss = (int)$filters['id_Del_Imss'];
                $builder->where('a.id_Del_Imss', $idDelImss);
            }

            if (!empty($filters['matricula'])) {
                $matricula = $this->validarString($filters['matricula'], 50);
                $builder->where('alum.matricula', $matricula);
            }

            if (!empty($filters['id_Gral_TipoSesion'])) {
                $tipoSesion = (int)$filters['id_Gral_TipoSesion'];
                $builder->where('a.id_Gral_TipoSesion', $tipoSesion);
            }

            if (!empty($filters['id_Gral_Turno'])) {
                $turno = (int)$filters['id_Gral_Turno'];
                $builder->where('a.id_Gral_Turno', $turno);
            }

            // ORDER BY
            $builder->orderBy('a.fechaSesion', 'DESC');
            $builder->orderBy('a.folio', 'ASC');

            // Paginación
            if ($limit > 0) {
                $builder->limit($limit, $offset);
            }

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerCursosAbec: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el total de registros sin paginación (para cálculo de paginación)
     * 
     * @param array $filters Mismo formato que obtenerCursosAbec
     * @return int Total de registros
     */
    public function contarCursosAbec(array $filters = []): int
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tbl_abec_curso a');

            $builder->join('tbl_abec_alumno alum', 'alum.id_abec_curso = a.id_abec_curso', 'LEFT');
            $builder->join('cat_general b', 'a.id_Gral_TipoSesion = b.id', 'LEFT');
            $builder->join('cat_general tur', 'a.id_Gral_Turno = tur.id', 'LEFT');
            $builder->join('edumed.ims_del_cat f', 'a.id_Del_Imss = f.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat g', 'f.edo_cve = g.edo_cve', 'LEFT');
            $builder->join('edumed.gra_sde_cat h', 'a.sde_cve = h.sde_cve', 'LEFT');

            $builder->where('a.cEstatus IS NOT NULL', null, false);

            // Aplicar mismos filtros
            if (!empty($filters['fechaInicio']) && !empty($filters['fechaFin'])) {
                $fechaInicio = $this->validarFecha($filters['fechaInicio']);
                $fechaFin = $this->validarFecha($filters['fechaFin']);
                $builder->where("a.fechaSesion BETWEEN '{$fechaInicio} 00:00:00' AND '{$fechaFin} 23:59:59'", null, false);
            } else {
                $anioActual = date('Y');
                $builder->where("YEAR(a.fechaSesion) = {$anioActual}", null, false);
            }

            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('a.folio', $folio);
            }

            if (!empty($filters['sde_cve'])) {
                $sdeCve = (int)$filters['sde_cve'];
                $builder->where('a.sde_cve', $sdeCve);
            }

            return $builder->countAllResults();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::contarCursosAbec: ' . $e->getMessage());
            return 0;
        }
    }

    // Nota: Los métodos relacionados con listado, detalle de curso,
    // alumnos por curso y estadísticas fueron removidos del modelo porque
    // los endpoints correspondientes fueron eliminados. Se conservan los
    // métodos de catálogos y utilidades (validación) en este modelo.

    /**
     * Valida y sanitiza una fecha
     * 
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return string Fecha validada
     * @throws Exception Si la fecha es inválida
     */
    private function validarFecha(string $fecha): string
    {
        $formato = 'Y-m-d';
        $d = \DateTime::createFromFormat($formato, $fecha);
        
        if (!$d || $d->format($formato) !== $fecha) {
            throw new \InvalidArgumentException("Fecha inválida: {$fecha}. Formato esperado: YYYY-MM-DD");
        }
        
        return $fecha;
    }

    /**
     * Valida y sanitiza un string
     * 
     * @param string $string Valor a validar
     * @param int $maxLength Longitud máxima permitida
     * @return string String sanitizado
     * @throws Exception Si la longitud es excesiva
     */
    private function validarString(string $string, int $maxLength = 255): string
    {
        $string = trim($string);
        
        if (strlen($string) > $maxLength) {
            throw new \InvalidArgumentException("String excede la longitud máxima de {$maxLength} caracteres");
        }
        
        // Sanitizar XSS
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obtiene detalles de un curso específico
     * 
     * @param int $idAbecCurso ID del curso ABEC
     * @return array|null Datos del curso
     */
    public function obtenerCursoPorId(int $idAbecCurso): ?array
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('tbl_abec_curso a');

            $builder->select(
                "a.*,
                b.cDescripcion AS tipo_sesion,
                CONCAT(g.EDO_NOM, ' ', f.DEL_NOM) AS ooad,
                h.SDE_NOM AS unidad,
                tur.cDescripcion AS turno"
            );

            $builder->join('cat_general b', 'a.id_Gral_TipoSesion = b.id', 'LEFT');
            $builder->join('edumed.ims_del_cat f', 'a.id_Del_Imss = f.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat g', 'f.edo_cve = g.edo_cve', 'LEFT');
            $builder->join('edumed.gra_sde_cat h', 'a.sde_cve = h.sde_cve', 'LEFT');
            $builder->join('cat_general tur', 'a.id_Gral_Turno = tur.id', 'LEFT');

            $builder->where('a.id_abec_curso', $idAbecCurso);
            $builder->where('a.cEstatus IS NOT NULL', null, false);

            $query = $builder->get();
            $result = $query->getRowArray();

            return $result ?: null;

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerCursoPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene catálogos solicitados (Años, OOAD, UMAE, Tipo de Sesión)
     * 
     * @param array $catalogos Array de nombres de catálogos a obtener
     *                         Valores válidos: 'anios', 'ooad', 'umae', 'tipo_sesion'
     * @return array Array con los catálogos solicitados
     */
    public function obtenerCatalogos(array $catalogos = []): array
    {
        try {
            $resultado = [];

            // Obtener años (2022 - año actual)
            if (in_array('anios', $catalogos) || empty($catalogos)) {
                $resultado['anios'] = $this->obtenerAnios();
            }

            // Obtener OOAD (Delegaciones con Estado)
            if (in_array('ooad', $catalogos) || empty($catalogos)) {
                $resultado['ooad'] = $this->obtenerOOAD();
            }

            // Obtener UMAE (Unidades con Delegación y Estado)
            if (in_array('umae', $catalogos) || empty($catalogos)) {
                $resultado['umae'] = $this->obtenerUMAE();
            }

            // Obtener Tipo de Sesión
            if (in_array('tipo_sesion', $catalogos) || empty($catalogos)) {
                $resultado['tipo_sesion'] = $this->obtenerTipoSesion();
            }

            return $resultado;

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerCatalogos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene lista de años (2022 - año actual)
     * 
     * @return array Array de años
     */
    private function obtenerAnios(): array
    {
        try {
            $anioActual = (int)date('Y');
            $anios = [];

            for ($anio = 2022; $anio <= $anioActual; $anio++) {
                $anios[] = [
                    'id_anio' => $anio,
                    'anio' => (string)$anio
                ];
            }

            return $anios;

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerAnios: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene OOAD (Delegaciones con Estado)
     * 
     * Query:
     * select a.del_cve id_ooad,CONCAT(b.EDO_NOM,' ' , a.DEL_NOM) ooad 
     * from edumed.ims_del_cat a 
     * inner join edumed.gra_edo_cat b on a.edo_cve=b.edo_cve 
     * where a.del_cve<>0;
     * 
     * @return array Array de OOAD
     */
    private function obtenerOOAD(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('edumed.ims_del_cat a');

            $builder->select("a.del_cve AS id_ooad, CONCAT(b.EDO_NOM, ' ', a.DEL_NOM) AS ooad");
            $builder->join('edumed.gra_edo_cat b', 'a.edo_cve = b.edo_cve', 'INNER');
            $builder->where('a.del_cve <>0', null, false);
            $builder->orderBy('b.EDO_NOM, a.DEL_NOM', 'ASC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerOOAD: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene UMAE (Unidades con Delegación y Estado)
     * 
     * Query:
     * SELECT sde_cve id_umae, CONCAT(sde_nom, ' (',b.EDO_NOM,' ' , a.DEL_NOM,')') umae 
     * from edumed.gra_sde_cat s
     * inner join edumed.ims_del_cat a ON a.DEL_CVE=s.DEL_CVE
     * inner join edumed.gra_edo_cat b on a.edo_cve=b.edo_cve
     * WHERE org_cve=1 AND s.HSP_NIV_CVE = 3 
     * order by b.EDO_NOM, a.DEL_NOM, sde_nom DESC;
     * 
     * @return array Array de UMAE
     */
    private function obtenerUMAE(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('edumed.gra_sde_cat s');

            $builder->select("s.sde_cve AS id_umae, CONCAT(s.sde_nom, ' (', b.EDO_NOM, ' ', a.DEL_NOM, ')') AS umae");
            $builder->join('edumed.ims_del_cat a', 'a.DEL_CVE = s.DEL_CVE', 'INNER');
            $builder->join('edumed.gra_edo_cat b', 'a.edo_cve = b.edo_cve', 'INNER');
            $builder->where('s.org_cve', 1);
            $builder->where('s.HSP_NIV_CVE', 3);
            $builder->orderBy('b.EDO_NOM, a.DEL_NOM, s.sde_nom', 'DESC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerUMAE: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene Tipo de Sesión
     * 
     * Query:
     * select id id_tipo_sesion, cDescripcion tipo_sesion 
     * from cat_general 
     * where cCatalogo='EC_SESIONES ABEC' and cEstatus='D' 
     * order by nOrden,cDescripcion;
     * 
     * @return array Array de tipos de sesión
     */
    private function obtenerTipoSesion(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('cat_general');

            $builder->select("id AS id_tipo_sesion, cDescripcion AS tipo_sesion");
            $builder->where('cCatalogo', 'EC_SESIONES ABEC');
            $builder->where('cEstatus', 'D');
            $builder->orderBy('nOrden, cDescripcion', 'ASC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerTipoSesion: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas agregadas desde la vista vw_abec_curso_alumno
     * Devuelve: total_registros, total_alumnos, total_alumnos_distinct, total_cursos, total_unidades
     * 
     * @param array $filters Opcionales: ['anio' => int, 'id_ooad' => int, 'id_umae' => int, 'id_tipo_sesion' => int]
     * @return array Resultado con los totales
     */
    public function obtenerEstadisticas(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select(
                'COUNT(*) AS total_registros,
                 COUNT(id_abec_alumno) AS total_alumnos,
                 COUNT(DISTINCT matricula) AS total_alumnos_distinct,
                 COUNT(DISTINCT id_abec_curso) AS total_cursos,
                 COUNT(DISTINCT sde_cve) AS total_unidades'
            );

            // Aplicar filtros opcionales
            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio . "-01-01 00:00:00";
                $fechaFin = $anio . "-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }

            if (!empty($filters['id_ooad'])) {
                $idOoad = (int)$filters['id_ooad'];
                $builder->where('v.del_cve', $idOoad);
            }

            if (!empty($filters['id_umae'])) {
                $idUmae = (int)$filters['id_umae'];
                $builder->where('v.sde_cve', $idUmae);
            }

            if (!empty($filters['id_tipo_sesion'])) {
                $idTipo = (int)$filters['id_tipo_sesion'];
                $builder->where('v.id_tipo_sesion', $idTipo);
            }

            $query = $builder->get();
            $row = $query->getRowArray();

            // Normalizar resultado
            if (!$row) {
                return [
                    'total_registros' => 0,
                    'total_alumnos' => 0,
                    'total_alumnos_distinct' => 0,
                    'total_cursos' => 0,
                    'total_unidades' => 0
                ];
            }

            return $row;

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::obtenerEstadisticas: ' . $e->getMessage());
            return [
                'total_registros' => 0,
                'total_alumnos' => 0,
                'total_alumnos_distinct' => 0,
                'total_cursos' => 0,
                'total_unidades' => 0
            ];
        }
    }

    /**
     * Estadísticas por OOAD (umae = false)
     */
    public function estadisticasPorOOAD(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select("v.ooad, COUNT(DISTINCT v.id_abec_curso) AS total_course, COUNT(v.id_abec_alumno) AS total_student, COUNT(v.id_abec_alumno)/NULLIF(COUNT(DISTINCT v.id_abec_curso),0) AS porcentaje", false);
            $builder->where('v.umae', 0);

            // filtros comunes
            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio."-01-01 00:00:00";
                $fechaFin = $anio."-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }
            if (!empty($filters['id_ooad'])) {
                $builder->where('v.del_cve', (int)$filters['id_ooad']);
            }
            if (!empty($filters['id_umae'])) {
                $builder->where('v.sde_cve', (int)$filters['id_umae']);
            }
            if (!empty($filters['id_tipo_sesion'])) {
                $builder->where('v.id_tipo_sesion', (int)$filters['id_tipo_sesion']);
            }

            $builder->groupBy('v.ooad');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::estadisticasPorOOAD: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Estadísticas por UMAE (umae = true) agrupado por ooad, unidad
     */
    public function estadisticasPorUMAE(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select("v.ooad, v.unidad, COUNT(DISTINCT v.id_abec_curso) AS total_course, COUNT(v.id_abec_alumno) AS total_student, COUNT(v.id_abec_alumno)/NULLIF(COUNT(DISTINCT v.id_abec_curso),0) AS porcentaje", false);
            $builder->where('v.umae', 1);

            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio."-01-01 00:00:00";
                $fechaFin = $anio."-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }
            if (!empty($filters['id_ooad'])) {
                $builder->where('v.del_cve', (int)$filters['id_ooad']);
            }
            if (!empty($filters['id_umae'])) {
                $builder->where('v.sde_cve', (int)$filters['id_umae']);
            }
            if (!empty($filters['id_tipo_sesion'])) {
                $builder->where('v.id_tipo_sesion', (int)$filters['id_tipo_sesion']);
            }

            $builder->groupBy(['v.ooad', 'v.unidad']);
            $builder->orderBy('v.ooad, v.unidad', 'ASC');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::estadisticasPorUMAE: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Categoría por alumno
     */
    public function categoriaPorAlumno(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select('v.categoria, COUNT(v.categoria) AS total', false);

            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio."-01-01 00:00:00";
                $fechaFin = $anio."-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }

            if (!empty($filters['id_ooad'])) {
                $builder->where('v.del_cve', (int)$filters['id_ooad']);
            }
            if (!empty($filters['id_umae'])) {
                $builder->where('v.sde_cve', (int)$filters['id_umae']);
            }
            if (!empty($filters['id_tipo_sesion'])) {
                $builder->where('v.id_tipo_sesion', (int)$filters['id_tipo_sesion']);
            }

            $builder->groupBy('v.categoria');
            $builder->orderBy('total', 'DESC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::categoriaPorAlumno: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Por mes: month(fechaSesion)
     */
    public function porMes(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select('MONTH(v.fechaSesion) AS mes, COUNT(DISTINCT v.id_abec_curso) AS total_course, COUNT(v.id_abec_alumno) AS total_student', false);

            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio."-01-01 00:00:00";
                $fechaFin = $anio."-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }

            if (!empty($filters['id_ooad'])) {
                $builder->where('v.del_cve', (int)$filters['id_ooad']);
            }
            if (!empty($filters['id_umae'])) {
                $builder->where('v.sde_cve', (int)$filters['id_umae']);
            }
            if (!empty($filters['id_tipo_sesion'])) {
                $builder->where('v.id_tipo_sesion', (int)$filters['id_tipo_sesion']);
            }

            $builder->groupBy('MONTH(v.fechaSesion)');
            $builder->orderBy('mes', 'ASC');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::porMes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Por sexo: conteo de estudiantes por sexo
     */
    public function porSexo(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select('v.sexo, COUNT(v.sexo) AS total', false);

            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio."-01-01 00:00:00";
                $fechaFin = $anio."-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }
            if (!empty($filters['id_ooad'])) {
                $builder->where('v.del_cve', (int)$filters['id_ooad']);
            }
            if (!empty($filters['id_umae'])) {
                $builder->where('v.sde_cve', (int)$filters['id_umae']);
            }
            if (!empty($filters['id_tipo_sesion'])) {
                $builder->where('v.id_tipo_sesion', (int)$filters['id_tipo_sesion']);
            }

            $builder->groupBy('v.sexo');
            $builder->orderBy('total', 'DESC');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::porSexo: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Listado de cursos (agrupado por curso)
     */
    public function listadoCursos(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select('v.id_abec_curso, v.fechaRegistro, v.folio, v.curso, v.fechaSesion, v.tipo_sesion, v.ooad, v.unidad, v.turno', false);

            if (!empty($filters['anio'])) {
                $anio = (int)$filters['anio'];
                $fechaInicio = $anio."-01-01 00:00:00";
                $fechaFin = $anio."-12-31 23:59:59";
                $builder->where("v.fechaSesion BETWEEN '{$fechaInicio}' AND '{$fechaFin}'", null, false);
            }
            if (!empty($filters['id_ooad'])) {
                $builder->where('v.del_cve', (int)$filters['id_ooad']);
            }
            if (!empty($filters['id_umae'])) {
                $builder->where('v.sde_cve', (int)$filters['id_umae']);
            }
            if (!empty($filters['id_tipo_sesion'])) {
                $builder->where('v.id_tipo_sesion', (int)$filters['id_tipo_sesion']);
            }

            $builder->groupBy(['v.id_abec_curso', 'v.fechaRegistro', 'v.folio', 'v.curso', 'v.fechaSesion', 'v.tipo_sesion', 'v.ooad', 'v.unidad', 'v.turno']);
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::listadoCursos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Detalle de alumnos por curso
     */
    public function detalleAlumnosPorCurso(int $idAbecCurso): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('vw_abec_curso_alumno v');

            $builder->select('v.id_abec_curso, v.id_abec_alumno, v.matricula, v.nombre_alumno, v.curp, v.categoria, v.subcategoria, v.especialidad, v.umae, v.sexo', false);
            $builder->where('v.id_abec_curso', $idAbecCurso);
            $builder->where('v.id_abec_alumno IS NOT NULL', null, false);
            
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en AbecModel::detalleAlumnosPorCurso: ' . $e->getMessage());
            return [];
        }
    }
}
