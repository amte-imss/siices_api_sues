<?php

namespace App\Models;

use CodeIgniter\Model;

class controlTiempoModel extends Model{

    public function obtenerTiempo()
    {  
        $pase = true;
        $db = \Config\Database::connect();
        //$query = $db->query("SELECT inicio, fin, activo FROM tiempos_api WHERE id = 1");
        $query = $db->query("SELECT FEC_INI, FEC_MAX, STA_CVE FROM srm_con_per_cnf_api_arc WHERE INS_CNF_CVE = 1");
        //$row = $query->getRow();
        $resultados = $query->getResultArray();

        $hoyfecha = date("Y-m-d H:i:s");
        //$hoyhora = date("H:i:s");

        if ($hoyfecha < $resultados[0]["FEC_INI"] || $hoyfecha > $resultados[0]["FEC_MAX"] || $resultados[0]["STA_CVE"] == 0 ) {
            $pase=false;
        }
         return $pase;   
    }
}
