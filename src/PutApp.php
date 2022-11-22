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
\check_access("WRITE", "/apps", "project-dev","", false);

$post = $_POST;
$data = new Data;
$gen = new Generic;
$params = $gen->requestParam([
  "prefix" => ["prefix", "username", 2,5, [], "UPPDER", ['-', "_", "."]],
  "name" => ["name","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "endpoint" => ["endpoint","username", 5, 52, [], "LOWER", ["/","-"]],
  "domain" => ["domain","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "user" => ["user","pattern", "/^(352|252|052)(\s|\-|\.)?([0-9]{4,4})(\s|\-|\.)?([0-9]{4,4})$/"],
  "title" => ["title","text", 5, 56],
  "description" => ["description", "text", 15, 250],
  "status" => ["status", "option", ["PENDING", "ACTIVE"]],
  "server" => ["server","option", get_server_keys()],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
], $post, ["server", "user", "name", "prefix", "title", "description", "form", "CSRF_token"]);

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

$params["user"] = \str_replace([" ", "-", ".", "_"],"",$params["user"]);
// look for user on the selected server
if (!$user = (new MultiForm($db_name, "users", "code", $conn))->findById($params['user'])) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[user] profile does not exist on the server."],
    "message" => "Request failed."
  ]);
  exit;
} if ($user->status !== "ACTIVE") {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[user] profile is not active."],
    "message" => "Request failed."
  ]);
  exit;
}
// die ($conn->getServer());
include PRJ_ROOT . "/src/Pre-Process.php";
// run command
unset($params["form"]);
unset($params["CSRF_token"]);
unset($params["server"]);

// create app
$dev_app = new API\DevApp($conn, get_database($server_name, "developer"), "apps");
try {
  $dev_app->register($params, $params["status"] == "ACTIVE");
} catch (\Throwable $th) {
  echo \json_encode([
    "status" => "4.1",
    "errors" => [$th->getMessage()],
    "message" => "Request failed"
  ]);
  exit;
}
if (empty($dev_app->name)) {
  // failed to create it, find errors;
  $errors = (new InstanceError($dev_app))->get("register", true);
  if (!empty($errors)) {
    echo \json_encode([
      "status" => "4." . \count($errors),
      "errors" => [$errors],
      "message" => "Request failed"
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
