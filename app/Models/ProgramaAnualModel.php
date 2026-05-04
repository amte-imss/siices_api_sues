<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AbecModel
 * 
 * Modelo para gestionar datos de cursos programa anual y sus participantes.
 * Incluye validación de seguridad y consultas optimizadas.
 * 
 * @package App\Models
 * @version 1.0.0
 */
class ProgramaAnualModel extends Model
{
    protected $DBGroup = 'default';

    /**
     * Obtiene datos de cursos Programa con filtros opcionales
     * 
     * @param array $filters Filtros opcionales (fechaInicio, fechaFin, folio, sde_cve, etc.)
     * @param int $limit Límite de registros
     * @param int $offset Offset para paginación
     * @return array Datos de cursos programa
     * @throws Exception Si hay error en la base de datos
     */
    /*public function obtenerCursosPrograma(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('tbl_curso a');

            // SELECT con campos específicos
            $builder->select(
                "a.id_Curso as folio, a.cNombreCurso as nombre_curso, f.cDescripcion as modalidad,
                a.cEstatus as cve_tipo_curso, a.cTipoCurso as tipo_curso, dd.cDescripcion as estatus_curso, b.EDO_CVE as cve_estado_curso, c.EDO_NOM as estado_curso,
                a.id_DelSolicita as id_delsolicita, concat(convert(c.EDO_NOM using utf8), ' ', b.DEL_NOM) as delegacion_solicita, a.id_UnidadSolicita as clave_unidad_solicita, d.SDE_NOM as unidad_solicita,
                a.id_DelImparte as id_delimparte, concat(convert(gg.EDO_NOM using utf8), ' ', ff.DEL_NOM) as delegacion_imparte, a.id_UnidadImparte as clave_unidad_imparte, e.SDE_NOM as unidad_imparte,
                a.dFechaRegistro as fecha_registro, left(a.dFechaInicio, 11) as fecha_inicio, left(a.dFechaTermino, 11) as fecha_termino, a.dFechaEnvio as fecha_envio,
                a.nCupo as cupo, a.nDiasHabilesDuracion as dias_habiles_duracion, a.nDiasNaturales as dias_naturales, a.nDuracionHoras as duracion_horas,
                i.cDescripcion as catalogo, j.cDescripcion as beca, ee.cDescripcion as lineaprioritaria, turno.cDescripcion as turno, ss.cDescripcion as tema_prioritario, tt.cDescripcion as enfoque_preventivo"
            );
            
            // JOINs con validación de existencia de tablas
            $builder->join('edumed.ims_del_cat b', 'a.id_DelSolicita=b.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat c', 'b.edo_cve=c.edo_cve ', 'LEFT');
            $builder->join('edumed.gra_sde_cat d', 'a.id_UnidadSolicita=d.sde_cve', 'LEFT');
            $builder->join('edumed.gra_sde_cat e', 'a.id_UnidadImparte=e.sde_cve', 'LEFT');
            $builder->join('cat_general f', 'a.id_Gral_Modalidad=f.id', 'LEFT');
            $builder->join('cat_general g', 'a.id_Gral_TemaCentral=g.id', 'LEFT');
            $builder->join('cat_general h', 'a.id_Gral_EspTemaCentral =h.id', 'LEFT');
            $builder->join('cat_general i', 'a.id_Gral_Catalogo =i.id ', 'LEFT');
            $builder->join('cat_general j', 'a.id_Gral_Beca =j.id', 'LEFT');
            $builder->join('cat_general k0', 'a.id_Gral_HorarioCurso =k0.id', 'LEFT');
            $builder->join('cat_estatuscurso dd', 'a.cEstatus=dd.cve_Estatus', 'LEFT');
            $builder->join('cat_general ee', 'a.id_gral_LineasP=ee.id', 'LEFT');
            $builder->join('cat_general turno', 'a.id_gral_horariocurso=turno.id', 'LEFT');
            $builder->join('edumed.ims_del_cat ff', 'a.id_DelImparte=ff.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat gg', 'ff.edo_cve=gg.edo_cve', 'LEFT');
            $builder->join('cat_general ss', 'a.TemaPrioritario=ss.id', 'LEFT');
            $builder->join('cat_general tt', 'a.EnfoquePreventivo=tt.id', 'LEFT');

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('a.id_Curso', $folio);
            }

            if (!empty($filters['sde_cve'])) {
                $sdeCve = (int)$filters['sde_cve'];
                $builder->where('a.id_UnidadSolicita', $sdeCve);
            }

            if (!empty($filters['id_Del_Imss'])) {
                $idDelImss = (int)$filters['id_Del_Imss'];
                $builder->where('a.id_DelSolicita', $idDelImss);
            }

            if (!empty($filters['cTipoCurso'])) {
                $tipoSesion = (int)$filters['cTipoCurso'];
                $builder->where('a.cTipoCurso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['cTipoCurso']) && $filters['cTipoCurso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(a.dFechaRegistro) = {$anio_anterior} and month(a.dFechaRegistro) in (8,9)) OR (YEAR(a.dFechaRegistro) = {$anio_actual} and month(a.dFechaRegistro) = 2)", null, false);
                } else {                    
                    $builder->where("a.fechaSesion BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cEstatus'])) {
                $turno = (int)$filters['cEstatus'];
                $builder->where('a.cEstatus', $turno);
            }

            if (!empty($filters['id_Gral_Catalogo'])) {
                $turno = (int)$filters['id_Gral_Catalogo'];
                $builder->where('a.id_Gral_Catalogo', $turno);
            }

            if (!empty($filters['id_Gral_Beca'])) {
                $turno = (int)$filters['id_Gral_Beca'];
                $builder->where('a.id_Gral_Beca', $turno);
            }

            // ORDER BY
            $builder->orderBy('a.dFechaInicio', 'DESC');
            $builder->orderBy('a.id_Curso', 'ASC');

            // Paginación
            if ($limit > 0) {
                $builder->limit($limit, $offset);
            }

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerCursosPrograma: ' . $e->getMessage());
            throw $e;
        }
    }*/

    /**
     * Obtiene el total de registros sin paginación (para cálculo de paginación)
     * 
     * @param array $filters Mismo formato que obtenerCursosPrograma
     * @return int Total de registros
     */
    /*public function contarCursosPrograma(array $filters = []): int
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('tbl_curso a');

            // JOINs con validación de existencia de tablas
            $builder->join('edumed.ims_del_cat b', 'a.id_DelSolicita=b.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat c', 'b.edo_cve=c.edo_cve ', 'LEFT');
            $builder->join('edumed.gra_sde_cat d', 'a.id_UnidadSolicita=d.sde_cve', 'LEFT');
            $builder->join('edumed.gra_sde_cat e', 'a.id_UnidadImparte=e.sde_cve', 'LEFT');
            $builder->join('cat_general f', 'a.id_Gral_Modalidad=f.id', 'LEFT');
            $builder->join('cat_general g', 'a.id_Gral_TemaCentral=g.id', 'LEFT');
            $builder->join('cat_general h', 'a.id_Gral_EspTemaCentral =h.id', 'LEFT');
            $builder->join('cat_general i', 'a.id_Gral_Catalogo =i.id ', 'LEFT');
            $builder->join('cat_general j', 'a.id_Gral_Beca =j.id', 'LEFT');
            $builder->join('cat_general k0', 'a.id_Gral_HorarioCurso =k0.id', 'LEFT');
            $builder->join('cat_estatuscurso dd', 'a.cEstatus=dd.cve_Estatus', 'LEFT');
            $builder->join('cat_general ee', 'a.id_gral_LineasP=ee.id', 'LEFT');
            $builder->join('cat_general turno', 'a.id_gral_horariocurso=turno.id', 'LEFT');
            $builder->join('edumed.ims_del_cat ff', 'a.id_DelImparte=ff.DEL_CVE', 'LEFT');
            $builder->join('edumed.gra_edo_cat gg', 'ff.edo_cve=gg.edo_cve', 'LEFT');
            $builder->join('cat_general ss', 'a.TemaPrioritario=ss.id', 'LEFT');
            $builder->join('cat_general tt', 'a.EnfoquePreventivo=tt.id', 'LEFT');

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('a.id_Curso', $folio);
            }

            if (!empty($filters['sde_cve'])) {
                $sdeCve = (int)$filters['sde_cve'];
                $builder->where('a.id_UnidadSolicita', $sdeCve);
            }

            if (!empty($filters['id_Del_Imss'])) {
                $idDelImss = (int)$filters['id_Del_Imss'];
                $builder->where('a.id_DelSolicita', $idDelImss);
            }

            if (!empty($filters['cTipoCurso'])) {
                $tipoSesion = (int)$filters['cTipoCurso'];
                $builder->where('a.cTipoCurso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['cTipoCurso']) && $filters['cTipoCurso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(a.dFechaRegistro) = {$anio_anterior} and month(a.dFechaRegistro) in (8,9)) OR (YEAR(a.dFechaRegistro) = {$anio_actual} and month(a.dFechaRegistro) = 2)", null, false);
                } else {                    
                    $builder->where("a.fechaSesion BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cEstatus'])) {
                $turno = (int)$filters['cEstatus'];
                $builder->where('a.cEstatus', $turno);
            }

            if (!empty($filters['id_Gral_Catalogo'])) {
                $turno = (int)$filters['id_Gral_Catalogo'];
                $builder->where('a.id_Gral_Catalogo', $turno);
            }

            if (!empty($filters['id_Gral_Beca'])) {
                $turno = (int)$filters['id_Gral_Beca'];
                $builder->where('a.id_Gral_Beca', $turno);
            }

            return $builder->countAllResults();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::contarCursosPrograma: ' . $e->getMessage());
            return 0;
        }
    }*/

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
    /*public function obtenerCursoPorId(int $idAbecCurso): ?array
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
            log_message('error', 'Error en ProgramaAnualModel::obtenerCursoPorId: ' . $e->getMessage());
            return null;
        }
    }*/

    /**
     * Obtiene catálogos solicitados (Años, OOAD, UMAE, Modalidad, Tipo de curso, Estado, Catálogo, Beca)
     * 
     * @param array $catalogos Array de nombres de catálogos a obtener
     *                         Valores válidos: 'anios', 'ooad', 'umae', 'modalidad', 'tipo_curso', 'estatus', 'catalogo', 'beca'
     * @return array Array con los catálogos solicitados
     */
    public function obtenerCatalogos(array $catalogos = []): array
    {
        try {
            $resultado = [];

            // Obtener años (2012 - año actual)
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

            // Obtener Modalidad
            if (in_array('modalidad', $catalogos) || empty($catalogos)) {
                $resultado['modalidad'] = $this->obtenerModalidad();
            }

            // Obtener Tipo de curso
            if (in_array('tipo_curso', $catalogos) || empty($catalogos)) {
                $resultado['tipo_curso'] = $this->obtenerTipoCurso();
            }

            // Obtener estatus de curso
            if (in_array('estatus', $catalogos) || empty($catalogos)) {
                $resultado['estatus'] = $this->obtenerEstatusCurso();
            }

            // Obtener catalogo
            if (in_array('catalogo', $catalogos) || empty($catalogos)) {
                $resultado['catalogo'] = $this->obtenerCatalogo();
            }

            // Obtener beca
            if (in_array('beca', $catalogos) || empty($catalogos)) {
                $resultado['beca'] = $this->obtenerBeca();
            }

            return $resultado;

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerCatalogos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene lista de años (2012 - año actual)
     * 
     * @return array Array de años
     */
    private function obtenerAnios(): array
    {
        try {
            $anioActual = (int)date('Y');
            $anios = [];

            for ($anio = 2012; $anio <= $anioActual; $anio++) {
                $anios[] = [
                    'id_anio' => $anio,
                    'anio' => (string)$anio
                ];
            }

            return $anios;

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerAnios: ' . $e->getMessage());
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
            log_message('error', 'Error en ProgramaAnualModel::obtenerOOAD: ' . $e->getMessage());
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
            log_message('error', 'Error en ProgramaAnualModel::obtenerUMAE: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene Tipo de Sesión
     * 
     * Query:
     * select id id_modalidad, cDescripcion modalidad 
     * from cat_general 
     * where cCatalogo='EC_MODALIDAD' and cEstatus='D' 
     * order by nOrden,cDescripcion;
     * 
     * @return array Array de modalidades
     */
    private function obtenerModalidad(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('cat_general');

            $builder->select("id AS id_modalidad, cDescripcion AS modalidad");
            $builder->where('cCatalogo', 'EC_MODALIDAD');
            $builder->where('cEstatus', 'D');
            $builder->orderBy('nOrden, cDescripcion', 'ASC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerModalidad: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene Tipo de curso
     *      * 
     * @return array Array de tipos de curso
     */
    private function obtenerTipoCurso(): array
    {
        try {
            $tipo_curso[] = [
                'PROGRAMA ANUAL' => "PROGRAMA ANUAL",
                'EXTEMPORANEO' => "EXTEMPORANEO",
                'EXTRAORDINARIO' => "EXTRAORDINARIO"
            ];

            return $tipo_curso;

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerTipoCurso: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene Catálogo
     * 
     * Query:
     * select id id_catalogo, cDescripcion catalogo 
     * from cat_general 
     * where cCatalogo='EC_CATALOGOS' and cEstatus='D' 
     * order by nOrden,cDescripcion;
     * 
     * @return array Array de modalidades
     */
    private function obtenerCatalogo(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('cat_general');

            $builder->select("id AS id_catalogo, cDescripcion AS catalogo");
            $builder->where('cCatalogo', 'EC_CATALOGOS');
            $builder->where('cEstatus', 'D');
            $builder->orderBy('nOrden, cDescripcion', 'ASC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerCatalogo: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene Beca
     * 
     * Query:
     * select id id_beca, cDescripcion beca 
     * from cat_general 
     * where cCatalogo='EC_BECA' and cEstatus='D' 
     * order by nOrden,cDescripcion;
     * 
     * @return array Array de modalidades
     */
    private function obtenerBeca(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('cat_general');

            $builder->select("id AS id_beca, cDescripcion AS beca");
            $builder->where('cCatalogo', 'EC_BECA');
            $builder->where('cEstatus', 'D');
            $builder->orderBy('nOrden, cDescripcion', 'ASC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerBeca: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene Estatus de curso
     * 
     * Query:
     * select cve_Estatus id_estatus, cDescripcion estatus 
     * from cat_estatuscurso ce 
     * where cEstatus = 'D' 
     * order by cve_Estatus ;
     * 
     * @return array Array de modalidades
     */
    private function obtenerEstatusCurso(): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $builder = $db->table('cat_estatuscurso ce');

            $builder->select("cve_Estatus AS id_estatus, cDescripcion AS estatus");
            $builder->where('cEstatus', 'D');
            $builder->orderBy('cve_Estatus', 'ASC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerEstatusCurso: ' . $e->getMessage());
            return [];
        }
    }

    
    private function subqueryCursoAlumnos()
    {
        try {
            $db = \Config\Database::connect('dbces');
            
            $subquery = $builder = $db->table('tbl_curso a');

            $subquery = $builder->select("a.id_Curso as folio, a.cNombreCurso as nombre_curso, f.cDescripcion as modalidad,
                a.cEstatus as cve_estatus_curso, dd.cDescripcion as estatus_curso, a.cTipoCurso as tipo_curso, b.EDO_CVE as cve_estado_curso, c.EDO_NOM as estado_curso,
                a.id_DelSolicita as id_delsolicita, concat(convert(c.EDO_NOM using utf8), ' ', b.DEL_NOM) as delegacion_solicita, a.id_UnidadSolicita as clave_unidad_solicita, d.SDE_NOM as unidad_solicita, d.HSP_NIV_CVE nivel_unidad_solicita,
                a.id_DelImparte as id_delimparte, concat(convert(gg.EDO_NOM using utf8), ' ', ff.DEL_NOM) as delegacion_imparte, a.id_UnidadImparte as clave_unidad_imparte, e.SDE_NOM as unidad_imparte, e.HSP_NIV_CVE nivel_unidad_imparte,
                a.dFechaRegistro as fecha_registro, left(a.dFechaInicio, 11) as fecha_inicio, left(a.dFechaTermino, 11) as fecha_termino, a.dFechaEnvio as fecha_envio,
                a.nCupo as cupo, a.nDiasHabilesDuracion as dias_habiles_duracion, a.nDiasNaturales as dias_naturales, a.nDuracionHoras as duracion_horas,
                a.id_Gral_Catalogo id_catalogo, i.cDescripcion as catalogo, a.id_Gral_Beca id_beca, j.cDescripcion as beca, ee.cDescripcion as lineaprioritaria, turno.cDescripcion as turno, ss.cDescripcion as tema_prioritario, tt.cDescripcion as enfoque_preventivo, 
                nn.id_Persona_Alumno, fff1.EMP_KEYEMP as matricula_alumno, oo.CURP as curp_alumno, concat(oo.PER_NOM, ' ', oo.PER_AP1, ' ', oo.PER_AP2) as nombre_alumno, substring(oo.CURP, 11, 1) sexo, nn.id_Categoria as id_categoria,
                cat_cat.cDescripcion as categoria, nn.id_SubCategoria as id_subcategoria, cat_sub.cDescripcion as subcategoria, (case when (nn.cEspecialidad = '0') then '' else nn.cEspecialidad end) as especialidad,
                edo.EDO_CVE as edo_cve, edo.EDO_NOM as estado, hhh.DEL_CVE as del_cve, concat(convert(edo.EDO_NOM using utf8), ' ', del.DEL_NOM) as delegacion, hhh.SDE_CVE as clave_sede, hhh.SDE_NOM as sede,
                fff1.EMP_KEYPRO clave_tipo_contratacion2, ctc.IMS_CNT_TIP_NOM AS tipo_contratacion2,
                nn.cServicioAdscripcion as servicio_adscripcion, (case when (nn.id_Gral_Asistencia = '81') then 'SI' when (nn.id_Gral_Asistencia = '82') then 'NO' else nn.id_Gral_Asistencia end) as asistio,
                (case when (nn.id_Gral_TipoAsistencia = '493') then 'BECA' when (nn.id_Gral_TipoAsistencia = '492') then 'COMISION' when (nn.id_Gral_TipoAsistencia = '495') then 'EXTRAINSTITUCIONAL'
                    when (nn.id_Gral_TipoAsistencia = '494') then 'INICIATIVA PROPIA' end) as tipo_asistencia,
                (case when (nn.id_Gral_Acredito = '81') then 'SI' when (nn.id_Gral_Acredito = '82') then 'NO' else nn.id_Gral_Acredito end) as acredito, nn.Acuerdos as acuerdos, nn.Desacuerdos as desacuerdos,
                concat(lpad(hhh.DEL_CVE, 2, '0'), '05', a.id_Curso, lpad(fec3.nConsecutivo, 3, '0'), '/', right(year(a.dFechaTermino), 2)) as folio_constancia");
            //$builder->where('cEstatus', 'D');
            
            $subquery = $builder->join('edumed.ims_del_cat b', 'a.id_DelSolicita=b.DEL_CVE', 'LEFT');
            $subquery = $builder->join('edumed.gra_edo_cat c', 'b.edo_cve=c.edo_cve', 'LEFT');
            $subquery = $builder->join('edumed.gra_sde_cat d', 'a.id_UnidadSolicita=d.sde_cve', 'LEFT');
            $subquery = $builder->join('edumed.gra_sde_cat e', 'a.id_UnidadImparte=e.sde_cve', 'LEFT');
            $subquery = $builder->join('cat_general f', 'a.id_Gral_Modalidad=f.id', 'LEFT');
            $subquery = $builder->join('cat_general g', 'a.id_Gral_TemaCentral=g.id', 'LEFT');
            $subquery = $builder->join('cat_general h', 'a.id_Gral_EspTemaCentral=h.id', 'LEFT');
            $subquery = $builder->join('cat_general i', 'a.id_Gral_Catalogo=i.id', 'LEFT');
            $subquery = $builder->join('cat_general j', 'a.id_Gral_Beca=j.id', 'LEFT');
            $subquery = $builder->join('cat_general k0', 'a.id_Gral_HorarioCurso=k0.id', 'LEFT');
            $subquery = $builder->join('cat_estatuscurso dd', 'a.cEstatus=dd.cve_Estatus', 'LEFT');
            $subquery = $builder->join('cat_general ee', 'a.id_Gral_LineasP=ee.id', 'LEFT');
            $subquery = $builder->join('cat_general turno', 'a.id_Gral_HorarioCurso=turno.id', 'LEFT');
            $subquery = $builder->join('edumed.ims_del_cat ff', 'a.id_DelImparte=ff.DEL_CVE', 'LEFT');
            $subquery = $builder->join('edumed.gra_edo_cat gg', 'ff.edo_cve=gg.edo_cve', 'LEFT');
            $subquery = $builder->join('dbces.tbl_alumnocurso nn', 'nn.id_Curso=a.id_Curso', 'LEFT');
            $subquery = $builder->join('edumed.gra_sde_cat hhh', 'hhh.SDE_CVE=nn.id_Sede_Alumno', 'LEFT');
            $subquery = $builder->join('edumed.ims_del_cat del', 'hhh.DEL_CVE=del.DEL_CVE', 'LEFT');
            $subquery = $builder->join('edumed.gra_edo_cat edo', 'del.edo_cve=edo.edo_cve', 'LEFT');
            $subquery = $builder->join('gra_per_arc oo', 'oo.PER_CVE=nn.id_Persona_Alumno', 'LEFT');
            $subquery = $builder->join('edumed.ims_per_nom_vis fff', 'fff.CURP = oo.CURP', 'LEFT');
            $subquery = $builder->join('nomina_imss fff1', 'oo.Curp=fff1.EMP_RECURP', 'LEFT');
            $subquery = $builder->join('edumed.ims_cnt_tip_cat ctc', 'ctc.IMS_CNT_TIP_CVE = fff1.EMP_KEYPRO', 'LEFT');
            $subquery = $builder->join('dbces.cat_categoriacursos cat_cat', 'cat_cat.id_CategoriaCursos=nn.id_Categoria', 'LEFT');
            $subquery = $builder->join('dbces.cat_subcategoriacursos cat_sub', 'cat_sub.id_SubCategoriaCursos=nn.id_SubCategoria', 'LEFT');
            $subquery = $builder->join('dbces.tbl_folios_ec3 fec3', 'fec3.idCurso = a.id_Curso AND fec3.id_Persona = nn.id_Persona_Alumno', 'LEFT');
            $subquery = $builder->join('cat_general ss', 'a.TemaPrioritario=ss.id', 'LEFT');
            $subquery = $builder->join('cat_general tt', 'a.EnfoquePreventivo=tt.id', 'LEFT');

            /*$query = $builder->get();
            return $query->getResultArray();*/
            return $subquery;

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::subqueryCursoAlumnos: ' . $e->getMessage());
            return "";
        }
    }

    /**
     * Obtiene estadísticas agregadas desde la vista vw_abec_curso_alumno|
     * Devuelve: total_registros, total_alumnos, total_alumnos_distinct, total_cursos, total_unidades
     * 
     * @param array $filters Opcionales: ['anio' => int, 'id_ooad' => int, 'id_umae' => int, 'id_tipo_sesion' => int]
     * @return array Resultado con los totales
     */
    public function obtenerEstadisticas(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');

            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos

            //$builder = $db->table('vw_abec_curso_alumno v');
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select(
                'COUNT(*) AS total_registros,
                COUNT(DISTINCT matricula_alumno) AS total_alumnos_distinct,
                COUNT(DISTINCT folio) AS total_cursos,
                COUNT(DISTINCT clave_unidad_solicita) AS total_unidades'
            );

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $query = $builder->get();
            $row = $query->getRowArray();

            //echo (string)$db->getLastQuery();// exit();

            // Normalizar resultado
            if (!$row) {
                return [
                    'total_registros' => 0,
                    'total_alumnos_distinct' => 0,
                    'total_cursos' => 0,
                    'total_unidades' => 0
                ];
            }

            return $row;

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::obtenerEstadisticas: ' . $e->getMessage());
            return [
                'total_registros' => 0,
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

            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select("delegacion_solicita, COUNT(DISTINCT folio) AS total_course, COUNT(id_Persona_Alumno) AS total_student, COUNT(id_Persona_Alumno)/NULLIF(COUNT(DISTINCT folio),0) AS porcentaje", false);
            
            $builder->where('nivel_unidad_solicita<>', 3);

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $builder->groupBy('delegacion_solicita');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::estadisticasPorOOAD: ' . $e->getMessage());
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
            
            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');
            
            $builder->select("delegacion_solicita, unidad_solicita, COUNT(DISTINCT folio) AS total_course, COUNT(id_Persona_Alumno) AS total_student, COUNT(id_Persona_Alumno)/NULLIF(COUNT(DISTINCT folio),0) AS porcentaje", false);
            $builder->where('nivel_unidad_solicita', 3);

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $builder->groupBy(['delegacion_solicita', 'unidad_solicita']);
            $builder->orderBy('delegacion_solicita, unidad_solicita', 'ASC');
            $query = $builder->get();

            //echo (string)$db->getLastQuery(); // Para depuración, muestra la consulta generada
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::estadisticasPorUMAE: ' . $e->getMessage());
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
            
            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select('categoria, COUNT(categoria) AS total', false);

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $builder->groupBy('categoria');
            $builder->orderBy('total', 'DESC');

            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en 
            ProgramaAnualModel::categoriaPorAlumno: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Por mes: month(fecha_termino)
     */
    public function porMes(array $filters = []): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            
            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select('MONTH(fecha_termino) AS mes, COUNT(DISTINCT folio) AS total_course, COUNT(id_Persona_Alumno) AS total_student', false);

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $builder->groupBy('MONTH(fecha_termino)');
            $builder->orderBy('mes', 'ASC');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::porMes: ' . $e->getMessage());
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
            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select('sexo, COUNT(sexo) AS total', false);

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $builder->groupBy('sexo');
            $builder->orderBy('total', 'DESC');
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::porSexo: ' . $e->getMessage());
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
            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select('folio, fecha_registro, nombre_curso, fecha_inicio, fecha_termino, tipo_curso, estatus_curso, delegacion_solicita, unidad_solicita, nivel_unidad_solicita, delegacion_imparte, unidad_imparte, nivel_unidad_imparte, catalogo, beca, modalidad, tema_prioritario, turno', false);

            // Filtros adicionales opcionales
            if (!empty($filters['folio'])) {
                $folio = $this->validarString($filters['folio'], 255);
                $builder->where('folio', $folio);
            }

            if (!empty($filters['id_umae'])) {
                $sdeCve = (int)$filters['id_umae'];
                $builder->where('clave_unidad_solicita', $sdeCve);
            }

            if (!empty($filters['id_ooad'])) {
                $idDelImss = (int)$filters['id_ooad'];
                $builder->where('id_delsolicita', $idDelImss);
            }

            if (!empty($filters['tipo_curso'])) {
                $tipoSesion = $filters['tipo_curso'];
                $builder->where('tipo_curso', $tipoSesion);
            }

            // Filtros de fecha
            if (!empty($filters['anio'])) {
                $anio_actual = (int)$filters['anio'];
                $anio_anterior = (int)$filters['anio']-1;
                if (!empty($filters['tipo_curso']) && $filters['tipo_curso'] === "PROGRAMA ANUAL") {
                    $builder->where("(YEAR(fecha_registro) = {$anio_anterior} and month(fecha_registro) in (8,9)) OR (YEAR(fecha_registro) = {$anio_actual} and month(fecha_registro) = 2)", null, false);
                } else {                    
                    $builder->where("fecha_termino BETWEEN '{$anio_actual}-01-01 00:00:00' AND '{$anio_actual}-12-31 23:59:59'", null, false);
                }
            }

            if (!empty($filters['cve_estatus_curso'])) {
                $turno = (int)$filters['cve_estatus_curso'];
                $builder->where('cve_estatus_curso', $turno);
            }

            if (!empty($filters['id_catalogo'])) {
                $turno = (int)$filters['id_catalogo'];
                $builder->where('id_catalogo', $turno);
            }

            if (!empty($filters['id_beca'])) {
                $turno = (int)$filters['id_beca'];
                $builder->where('id_beca', $turno);
            }

            $builder->groupBy(['folio', 'fecha_registro', 'nombre_curso', 'fecha_inicio', 'fecha_termino', 'tipo_curso', 'estatus_curso', 'delegacion_solicita', 'unidad_solicita', 'nivel_unidad_solicita', 'delegacion_imparte', 'unidad_imparte', 'nivel_unidad_imparte', 'catalogo', 'beca', 'modalidad', 'tema_prioritario', 'turno']);
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::listadoCursos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Detalle de alumnos por curso
     */
    public function detalleAlumnosPorCurso(int $folio): array
    {
        try {
            $db = \Config\Database::connect('dbces');
            $subquery = $this->subqueryCursoAlumnos(); // Construye la subconsulta para obtener los datos de cursos y alumnos
            $builder = $db->newQuery()->fromSubquery($subquery, 't');

            $builder->select('folio, id_Persona_Alumno, matricula_alumno, nombre_alumno, curp_alumno, categoria, subcategoria, especialidad, delegacion, sede, sexo', false);
            $builder->where('folio', $folio);
            $builder->where('id_Persona_Alumno IS NOT NULL', null, false);
            
            $query = $builder->get();
            return $query->getResultArray();

        } catch (\Exception $e) {
            log_message('error', 'Error en ProgramaAnualModel::detalleAlumnosPorCurso: ' . $e->getMessage());
            return [];
        }
    }
}
