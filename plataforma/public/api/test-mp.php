<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use App\Services\MercadoPagoService;

header("Content-Type: application/json");

$service = new MercadoPagoService();

echo json_encode(

    $service->crearPreferencia([

        "codigo" => "TEST-123",

        "evento" => "Evento AGAE",

        "cantidad" => 3,

        "precio_unitario" => 65000

    ]),

    JSON_PRETTY_PRINT

);