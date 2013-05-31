<?php
require 'vendor/autoload.php';
include "vendor/notorm/NotORM.php";

$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());

$dsn = "pgsql:dbname=sd_ventures_development;host=localhost";
$pdo = new PDO($dsn, "sd_ventures", "");
$sd_ventures = new NotORM($pdo);
date_default_timezone_set('UTC');

$app->get('/device_types', function () use ($sd_ventures, $app) {
  $res = $app->response();
  $res['Content-Type'] = 'application/json';

  $device_types = $sd_ventures->device_types();
  echo json_encode(array_map('iterator_to_array', iterator_to_array($device_types)));
});

$app->get('/devices', function () use ($sd_ventures, $app) {
  $res = $app->response();
  $res['Content-Type'] = 'application/json';

  $devices = $sd_ventures->devices();
  echo json_encode(array_map('iterator_to_array', iterator_to_array($devices)));
});

$app->get('/readings', function () use ($sd_ventures, $app) {
  $res = $app->response();
  $res['Content-Type'] = 'application/json';

  $readings = $sd_ventures->readings();
  echo json_encode(array_map('iterator_to_array', iterator_to_array($readings)));
});

$app->get('/devices/:device_mac_addr/readings', function ($device_mac_addr) use ($sd_ventures, $app) {
  $res = $app->response();
  $res['Content-Type'] = 'application/json';

  $readings = $sd_ventures->readings->where("device_mac_addr = ?", $device_mac_addr);
  echo json_encode(array_map('iterator_to_array', iterator_to_array($readings)));
});

$app->post('/devices/:device_mac_addr/readings', function ($device_mac_addr) use ($sd_ventures, $app) {
  $req = $app->request();
  $res = $app->response();
  $res['Content-Type'] = 'application/json';
  $json_body = $req->getBody();

  $result = $sd_ventures->readings->insert(array(
    "device_mac_addr" => $device_mac_addr,
    "value"           => $json_body["value"],
    "created_at"      => new NotORM_Literal("NOW()"),
  ));
  if($result) {
    echo json_encode(array("status" => "ok"));
  } else {
    echo json_encode(array("status" => "notOk"));
  }
});

$app->run();
?>
