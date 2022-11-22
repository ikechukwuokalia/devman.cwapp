<?php
namespace Catali;
require_once "../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\HTTP,
    \TymFrontiers\MySQLDatabase,
    \TymFrontiers\MultiForm,
    \TymFrontiers\InstanceError,
    \TymFrontiers\API;

\header("Content-Type: application/json");
\require_login(false);
\check_access("WRITE", "/apps", "project-dev","", false);

$post = $_POST;
$gen = new Generic;
$params = $gen->requestParam([
  "name" => ["name","username",5,55,[],'LOWER',['-',"."]],
  "domain" => ["domain","username",5,125,[],'LOWER',['-',"."]],
  "prefix" => ["prefix","username",3,7,[],'UPPER'],
  "endpoint" => ["endpoint","username",5,55,[],'LOWER',['-',".","/","_"]],
  "title" => ["title","text",5,65],
  "description" => ["description","text",25,256],
  "user" => ["owner","username",3,12],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
], $post, ["name", "prefix", "title", "description", "form", "CSRF_token"]);

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
if (empty($params['user'])) {

} else {
  if (!(new MultiForm(get_database("BASE", "developer"), "users", "code"))
    ->findBySql("SELECT `code` FROM :db:.:tbl: WHERE `code` = '{$database->escapeValue($params['user'])}' AND status = 'ACTIVE' LIMIT 1")) {
    echo \json_encode([
      "status" => "3.1",
      "errors" => ["No active record found for app [owner/user]"],
      "message" => "Request halted."
    ]);
    exit;
  }
}
include PRJ_ROOT . "/src/Pre-Process.php";
// run command
// get dev user
try {
  //code...
} catch (\Throwable $th) {
  //throw $th;
}
$dev_user = 
// $app = new API\DevApp($params);
// if (empty($app->name)) {
//   $do_errors = [];
//   // echo "<tt> <pre>";
//   // \print_r($app->errors);
//   // echo "</pre></tt>";
//   $more_errors = (new InstanceError($app,true))->get('self',true);
//   if (!empty($more_errors)) {
//     foreach ($more_errors as $err){
//       $do_errors[] = $err;
//     }
//     echo \json_encode([
//       "status" => "4." . \count($do_errors),
//       "errors" => $do_errors,
//       "message" => "Request incomplete."
//     ]);
//     exit;
//   } else {
//     echo \json_encode([
//       "status" => "0.1",
//       "errors" => [],
//       "message" => "No task was performed."
//     ]);
//     exit;
//   }
// }
// // disconnect to dev
// if ($http_auth) {
//   $GLOBALS["database"]->closeConnection();
//   $GLOBALS["database"] = new \TymFrontiers\MySQLDatabase(MYSQL_SERVER, MYSQL_GUEST_USERNAME, MYSQL_GUEST_PASS);
// }

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
