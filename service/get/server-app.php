<?php
namespace Catali;
use \TymFrontiers\Generic,
    \TymFrontiers\InstanceError,
    \TymFrontiers\MultiForm;
require_once "../../.appinit.php";
\check_access("SECRETE", "/apps", "project-dev","", false);

\header("Content-Type: application/json");

$gen = new Generic;
$params = $gen->requestParam([
  "server" => ["server","option", \array_keys($cws_servers)],
], $_GET, ["server"] );

if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError ($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
$server_name = get_constant("PRJ_SERVER_NAME");
$found = (new MultiForm(get_database($server_name, "developer"), "server_apps", "id"))
  ->findBySql("SELECT `name`, `server`
              FROM :db:.:tbl:
              WHERE `server` = '{$params['server']}'
              LIMIT 1");
if( !$found ){
  die( \json_encode([
    "message" => "No app was found.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}

// process result
$result = [
  "message" => "Request completed.",
  "errors"  => [],
  "status"  => "0.0",
  "data" => [
    "name" => $found[0]->name,
    "description" => $found[0]->server,
    "title" => "{$found[0]->server} /{$found[0]->name}"
  ]
];
echo \json_encode($result);
exit;
