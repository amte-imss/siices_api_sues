<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\insertaLogsModel;

class activosPosgradoModel extends Model
{
    protected $DBGroup = 'default';

    public function obtenerPorCampos($anio = null, $categoria = null, $delegacion = null, $especialidad = null, $genero = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('srm_per_reg_arc a');

        $builder->select(
            "a.pro_ano, 
            f.mta_ctg_nom AS categoria, 
            TRIM(CONCAT(i.edo_nom, ' ', h.del_nom)) AS DELEGACION, 
            CASE WHEN d.esp_tip_cve=1 THEN 'DIRECTA' ELSE 'RAMA' END AS TIPO_ESPECIALIDAD, 
            f.esp_nom AS ESPECIALIDAD, 
            g.clues AS CLUES, 
            g.sde_nom AS SEDE_ACADEMICA, 
            g.hsp_niv_cve AS NIVEL_DE_ATENCION, 
            CASE WHEN g.hsp_niv_cve=3 THEN 'UMAE' ELSE 'DELEGACION' END AS DELEGACION_UMAE, 
            f.grd_num AS GRADO, 
            f.curp AS CURP, 
            m.gnr_nom AS GENERO, 
            f.reg_cve AS FOLIO_IMSS, 
            f.per_ap1 AS AP_PATERNO, 
            f.per_ap2 AS AP_MATERNO, 
            f.per_nom AS NOMBRE, 
            c.mta_tip_ali AS TIPO, 
            t.pai_ncn_nom AS NACIONALIDAD, 
            CASE WHEN eml_url IS NULL THEN CONCAT(j.eml_ali, '@', k.eml_dmn_nom) ELSE eml_url END AS CORREO, 
            l.tel_ext_num AS CELULAR, 
            q.sde_nom AS SEDE, 
            r.sde_nom AS SUBSEDE, 
            w.aval_nom AS AVAL_ACADEMICO, 
            z.ims_mat AS MATRICULA, 
            u.desc_tc AS CONTRATACION, 
            a.vlt_num AS VUELTA, 
            v.edo_nom AS EDO_NACIMIENTO, 
            CASE WHEN n.cup_ocu_aud=99 THEN 'se queda en subsede ciclo 2021' ELSE '' END AS NOTA"
        );

        $builder->join('srm_mta_tip_cat c', 'a.mta_tip_cve = c.mta_tip_cve');
        $builder->join('srm_esp_cat d', 'a.esp_cve=d.esp_cve');
        $builder->join('srm_reg_ads_vis f', 'a.reg_cve=f.reg_cve');
        $builder->join('gra_sde_cat g', 'f.sde_cve=g.sde_cve');
        $builder->join('ims_del_cat h', 'g.del_cve=h.del_cve');
        $builder->join('gra_edo_cat i', 'h.edo_cve=i.edo_cve');

        $builder->join('gra_per_eml_arc j', 'a.curp=j.curp', 'left');
        $builder->join('gra_eml_dmn_cat k', 'j.eml_dmn_cve=k.eml_dmn_cve', 'left');
        $builder->join('gra_per_tel_num_arc l', 'l.curp=a.curp and l.uso_tip_cve=1', 'left');

        $builder->join('gra_per_gnr_cat m', 'm.gnr_cve=f.gnr_cve');
        $builder->join('srm_mta_prg_arc n', 'n.mta_cve=f.mta_cve');
        $builder->join('srm_cur_prg_arc o', 'o.mta_cve = n.mta_cve AND o.sde_tip_cve = 1');
        $builder->join('srm_cur_prg_arc p', 'p.mta_cve=n.mta_cve AND p.sde_tip_cve=2');
        $builder->join('gra_sde_cat q', 'q.sde_cve=o.sde_cve');
        $builder->join('gra_sde_cat r', 'r.sde_cve=p.sde_cve');
        $builder->join('gra_per_ncn_arc s', 'f.per_cve=s.per_cve');
        $builder->join('gra_pai_cat t', 't.pai_cve=s.pai_cve');
        $builder->join('gra_edo_cat v', 'v.edo_cve=s.edo_cve');
        $builder->join('srm_per_aval_arc w', 'w.ads_cve=f.ads_cve');

        $builder->join('ims_per_nom_arc z', 'z.curp=a.curp', 'left');
        $builder->join('personal.tbl_tipo_contratacion_cat u', 'u.cve_tc=z.ims_cnt_tip_cve', 'left');
        
        $builder->where(['f.grd_num !=' => 0, 'j.eml_tip_cve' => 1]);

        //$builder->limit(5);

        if(!is_null($anio)){
            $builder->where('a.pro_ano', $anio);
        }

        if(!is_null($categoria)){
            $builder->where('f.mta_ctg_nom', $categoria);
        }

        if(!is_null($delegacion)){
            $builder->where("TRIM(CONCAT(i.edo_nom, ' ', h.del_nom)) = '{$delegacion}'", null, false);
        }

        if(!is_null($especialidad)){
            $builder->where('f.esp_nom', $especialidad);
        }

        if(!is_null($genero)){
            $builder->where('m.gnr_nom', $genero);
        }
        //$builder->where('f.curp', 'CAHA971206MZSSRN09');

        $builder->orderBy('i.edo_cve', 'DESC');
        $builder->orderBy('i.edo_nom', 'DESC');
        $builder->orderBy('h.del_cve', 'DESC');
        $builder->orderBy('h.del_nom', 'DESC');
        $builder->orderBy('f.esp_nom', 'DESC');
        $builder->orderBy('g.sde_nom', 'DESC');
        $builder->orderBy('f.grd_num', 'DESC');
        $builder->orderBy('f.per_ap1', 'DESC');
        $builder->orderBy('f.per_ap2', 'DESC');
        $builder->orderBy(' f.per_nom', 'DESC');
        $builder->orderBy('c.mta_tip_nom', 'DESC');

        $query = $builder->get();
        $resultados = $query->getResultArray();

        if(CONTROL_LOGS){
            $logs = new insertaLogsModel();
            $logs -> guardaLog($categoria, $tipo_especialidad, $tipo, $curp);
        }

        /*$resultado = array_filter($resultados, function ($row) use ($categoria, $tipo_especialidad, $tipo, $curp) {
            $ok = (
                (isset($row['CATEGORIA']) && $row['CATEGORIA'] == $categoria) &&
                (isset($row['TIPO_ESPECIALIDAD']) && $row['TIPO_ESPECIALIDAD'] == $tipo_especialidad) &&
                (isset($row['TIPO']) && $row['TIPO'] == $tipo)
            );

            if ($curp !== null) {
                $ok = $ok && (isset($row['CURP']) && $row['CURP'] == $curp);
            }

            return $ok;
        });*/

        //return array_values($resultado);
        return $resultados;
    }

    public function obtenerAnios(){
        $anios=[
            "1"=>"2018",
            "2"=>"2019",
            "3"=>"2020",
            "4"=>"2021",
            "5"=>"2022",
            "6"=>"2023",
            "7"=>"2024",
            "8"=>"2025"];
        return $anios;
    }

    public function obtenerCombos($control){
try{
        $db = \Config\Database::connect();

        if($control == "2"){
            $builder = $db->table('srm_mta_ctg_cat');
            $builder->select("*");
        }

        if($control == "3"){
            $builder = $db->table('gra_edo_cat a');
            $builder->select("del_cve,a.edo_nom,b.del_nom");
            $builder->join('ims_del_cat b', 'a.edo_cve=b.edo_cve');
        }

        if($control == "4"){
            $builder = $db->table('srm_esp_cat');
            $builder->select("*");
            $builder->where('esp_sta_cve', "1");
        }

        if($control == "5"){
            $builder = $db->table('gra_per_gnr_cat');
            $builder->select("*");
        }

        $query = $builder->get();
        $resultados = $query->getResultArray();
        return $resultados;

    }catch(\Exception $e){

    }
}

}