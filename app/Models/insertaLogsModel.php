<?php

namespace App\Models;

use CodeIgniter\Model;

class insertaLogsModel extends Model{

    public function guardaLog($anio=null, $categoria=null, $delegacion=null, $especialidad=null, $genero=null)
    { 
        $campos=[
            "anio" => $anio,
            "categoria" => $categoria,
            "delegacion" => $delegacion,
            "especialidad" => $especialidad,
            "genero" => $genero
        ];

        $data = [
            "fecha_consulta" => date("Y-m-d H:i:s"),
            "campos" => json_encode($campos)
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('srm_con_per_log_api_arc');
        $builder->insert($data);
        return TRUE;
    }
}