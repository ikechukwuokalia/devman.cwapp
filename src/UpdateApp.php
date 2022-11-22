<?php
namespace Catali;
require_once "../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\HTTP,
    \TymFrontiers\MySQLDatabase,
    \TymFrontiers\MultiForm,
    \TymFrontiers\Data,
    \TymFrontiers\InstanceError,
    \TymFrontiers\API;

\header("Content-Type: application/json");
\require_login(false);
\check_access("UPDATE", "/apps", "project-dev","", false);

$post = $_POST;
$data = new Data;
$gen = new Generic;
$params = $gen->requestParam([
  "name" => ["name","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "endpoint" => ["endpoint","username", 5, 52, [], "LOWER", ["/","-"]],
  "domain" => ["domain","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "title" => ["title","text", 5, 56],
  "description" => ["description", "text", 15, 250],
  
  "server" => ["server","option", get_server_keys()],
  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
], $post, ["server", "name", "form", "CSRF_token"]);

if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
  ]);
  exit;
}
if ( !$gen->checkCSRF($params["form"],$params["CSRF_token"]) ) {
  $errors = (new InstanceError($gen,true))->get("checkCSRF",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted."
  ]);
  exit;
}
// init server
$server_name = $params["server"];
if ($server_name !== get_constant("PRJ_SERVER_NAME")) {
  $new_conn = true;
  $cred = get_dbuser($server_name, $session->access_group());
  $conn = new MySQLDatabase(get_dbserver($server_name), $cred[0], $cred[1]);
} else {
  $new_conn = false;
  $conn = $database;
}
if (!$conn instanceof MySQLDatabase) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => ["Failed to connect to server."],
    "message" => "Request failed."
  ]);
  exit;
}
$db_name = get_database($server_name, "developer");
$conn->changeDB($db_name);

$app = (new MultiForm($db_name, "apps", "name", $conn))->findById($params["name"]);

if (!$app) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Dev App profile was not found on the given [server]"],
    "message" => "Request failed."
  ]);
  exit;
} 
include PRJ_ROOT . "/src/Pre-Process.php";
// run command
unset($params["form"]);
unset($params["CSRF_token"]);
unset($params["server"]);
foreach ($params as $prop=>$value) {
 if (!empty($value)) $app->$prop = $value;
}
if (!$app->update()) {
  $do_errors = [];

  $app->mergeErrors();
  $more_errors = (new InstanceError($app,true))->get('',true);
  if (!empty($more_errors)) {
    foreach ($more_errors as $method=>$errs) {
      foreach ($errs as $err){
        $do_errors[] = $err;
      }
    }
    echo \json_encode([
      "status" => "4." . \count($do_errors),
      "errors" => $do_errors,
      "message" => "Request incomplete."
    ]);
    exit;
  } else {
    echo \json_encode([
      "status" => "0.1",
      "errors" => [],
      "message" => "Request completed with no changes made."
    ]);
    exit;
  }
}

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
