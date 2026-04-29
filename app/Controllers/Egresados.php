<?php

namespace App\Controllers;

use App\Models\egresadosModel;
use App\Models\controlTiempoModel;
use CodeIgniter\RESTful\ResourceController;

class Egresados extends ResourceController
{
    public function obtenerFila()
    {//log_message('error', 'entra debug');
    //log_message('error', 'entra debug version ' . \CodeIgniter\CodeIgniter::CI_VERSION);

        helper('text');
        $modelo = new egresadosModel();
        
        if(CONTROL_PASS){
            $usuario = $this->request->getHeaderLine('API-USER');
            $password = $this->request->getHeaderLine('API-PASS');

                //usuario y password definidos en config/constants
            if ($usuario !== USUARIO || $password !== PASSWORD) {
                return $this->respond(["error" => "Acceso no autorizado"], 401);
            }
        }

        if(CONTROL_IP){
            $clienteIp = $this->request->getIPAddress();
            if ($clienteIp !== IP_CLIENTE) {
                return $this->respond(["error" => "Servidor no autorizado"], 403);
            }
        }

        if(CONTROL_FECHA){
            $tiempoModel = new controlTiempoModel();
            $pase = $tiempoModel->obtenerTiempo();

            if($pase == !TRUE){
                return $this->respond(["error" => "Fuera del periodo autorizado"], 403);
            }
        }
        $json = $this->request->getJSON(true);

        if (!$json || !isset($json['control'])) {
            return $this->respond([
                "error" => "Falta el parámetro de control"
            ], 400);
        }

        $control = $json['control'];
        $anio = isset($json['anio']) ? (is_array($json['anio']) ? $json['anio'] : [$json['anio']]) : null;
        $categoria = isset($json['categoria']) ? (is_array($json['categoria']) ? $json['categoria'] : [$json['categoria']]) : null;
        $delegacion = isset($json['delegacion']) ? (is_array($json['delegacion']) ? $json['delegacion'] : [$json['delegacion']]) : null;
        $especialidad = isset($json['especialidad']) ? (is_array($json['especialidad']) ? $json['especialidad'] : [$json['especialidad']]) : null;
        $genero = isset($json['genero']) ? (is_array($json['genero']) ? $json['genero'] : [$json['genero']]) : null;

        if($control == "0"){
            $data = $modelo->obtenerPorCampos($anio, $categoria, $delegacion, $especialidad, $genero);
        }else if($control == "1"){
            $data = $modelo->obtenerAnios();
        }else{
            $data = $modelo->obtenerCombos($control);
        }

        if (count($data) > 0) {
            return $this->respond($data);
        } else {
            return $this->respond([], 200);
        }    
    }
}
