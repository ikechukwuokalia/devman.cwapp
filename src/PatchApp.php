<?php
namespace IO;
require_once "../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\MultiForm,
    \TymFrontiers\MySQLDatabase,
    \TymFrontiers\InstanceError;

\header("Content-Type: application/json");
\require_login(false);
\check_access("ALTER", "/users", "project-dev","", false);

$post = $_POST;
$gen = new Generic;
$params = $gen->requestParam([
  "name" => ["name","username",5,55,[],'LOWER',['-',"."]],
  "server" => ["server","option", get_server_keys()],
  "status" => ["status","option", ["ACTIVE", "SUSPENDED", "BANNED", "DISABLED"]]
],$post,["name", "server", "status"]);

if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError($gen,true))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request failed"
  ]);
  exit;
}
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
$db_name = get_database("developer", $server_name);
$conn->changeDB($db_name);

include PRJ_ROOT . "/src/Pre-Process.php";
$user = (new MultiForm($db_name, 'apps', 'name', $conn))->findById($params['name']);

if ( !$user ) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["[app] with [name]: '{$params['name']}' not found."],
    "message" => "Request halted."
  ]);
  exit;
}
$user->status = $params['status'];
if (!$user->update()) {
  $do_errors = [];

  $user->mergeErrors();
  $more_errors = (new InstanceError($user,true))->get('',true);
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
