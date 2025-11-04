<?php

namespace App\Models;

use CodeIgniter\Model;

class insertaLogsModel extends Model{

    public function guardaLog($categoria, $tipo_especialidad, $tipo, $curp = null)
    { 
        $campos=[
            "categoria" => $categoria,
            "tipo_especialidad" => $tipo_especialidad,
            "tipo" => $tipo,
            "curp" => $curp
        ];

        $data = [
            "fecha_consulta" => date("Y-m-d H:i:s"),
            "campos" => json_encode($campos)
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('logs_api');
        $builder->insert($data);
        return TRUE;
    }
}