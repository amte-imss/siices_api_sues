<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\insertaLogsModel;

class egresadosModel extends Model
{
    protected $DBGroup = 'default';

    public function obtenerPorCampos($anio = null, $categoria = null, $delegacion = null, $especialidad = null, $genero = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('srm_per_reg_arc a'); 

        $builder->select(
            "f.cur_ano + 1 AS ANO_EGRESO, 
            b.mta_ctg_nom AS CATEGORIA, 
            trim(CONCAT(i.EDO_NOM,' ',h.DEL_NOM)) AS DELEGACION, 
            d.esp_nom as ESPECIALIDAD,          
            case when d.esp_tip_cve=1 then 'DIRECTA' ELSE 'RAMA' END AS TIPO_ESPECIALIDAD, 
            g.sde_nom AS SEDE, 
            f.grd_num AS GRADO, 
            e.curp, 
            j.gnr_nom AS GENERO,              
            e.per_ap1, 
            e.per_ap2, 
            e.per_nom, 
            c.MTA_TIP_NOM AS TIPO",
            false
        );

        $builder->join('srm_mta_ctg_cat b', 'a.mta_ctg_cve=b.mta_ctg_cve');
        $builder->join('srm_mta_tip_cat c', 'a.mta_tip_cve=c.mta_tip_cve');
        $builder->join('srm_esp_cat d', 'a.esp_cve=d.esp_cve');
        $builder->join('gra_per_arc e', 'a.PER_CVE=e.PER_CVE');
        $builder->join('srm_per_ads_arc1 f', 'a.reg_cve=f.reg_cve');
        $builder->join('gra_sde_cat g', 'f.sde_cve=g.sde_cve');
        $builder->join('ims_del_cat h', 'g.del_cve=h.del_cve');
        $builder->join('gra_edo_cat i', 'g.edo_cve=i.edo_cve');
        $builder->join('gra_per_gnr_cat j', 'j.gnr_cve=e.gnr_cve ');
        
        $builder->where(['f.egr_sta_cve' => 1, 'f.ter_sta_cve' => 1]);

        //$builder->limit(5);

        if (!empty($anio)) {
            $builder->whereIn('f.cur_ano + 1', $anio, false);
        }

        if(!is_null($categoria)){
            $builder->whereIn('b.mta_ctg_nom', $categoria);
        }

        if (!empty($delegacion) && is_array($delegacion)) {
            $builder->whereIn(
                "TRIM(CONCAT(i.edo_nom, ' ', h.del_nom))",
                $delegacion
            );
        }

        if(!is_null($especialidad)){
            $builder->whereIn('d.esp_nom', $especialidad);
        }

        if(!is_null($genero)){
            $builder->whereIn('j.gnr_nom', $genero);
        }

        $builder->orderBy('f.cur_ano', 'DESC');
        $builder->orderBy('d.esp_nom', 'DESC');
        $builder->orderBy('e.per_ap1', 'DESC');
        $builder->orderBy('e.per_ap2', 'DESC');
        $builder->orderBy('e.per_nom ', 'DESC');

        $query = $builder->get();
        $resultados = $query->getResultArray();

        if(CONTROL_LOGS){
            $logs = new insertaLogsModel();
            $logs -> guardaLog($anio, $categoria, $delegacion, $especialidad, $genero);
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