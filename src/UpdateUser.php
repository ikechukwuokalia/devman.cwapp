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
\check_access("UPDATE", "/users", "project-dev","", false);

$post = $_POST;
$data = new Data;
if( !empty($post['phone']) && !empty($post["country_code"]) ){
  $post['phone'] = $data->phoneToIntl(\trim($post['phone']),$post["country_code"]);
}
// echo "<tt><pre>";
// print_r($post);
// echo "</pre></tt>";
// exit;
$gen = new Generic;
$params = $gen->requestParam([
  "code" => ["code","pattern", "/^352(\s|\-|\.)?([\d]{4,4})(\s|\-|\.)?([\d]{4,4})$/"],
  "server" => ["server","option", get_server_keys()],
  "country_code" => ["country_code","username", 2, 2],
  "name" => ["name","name"],
  "surname" => ["surname","name"],
  "email" => ["email","email"],
  "phone" => ["phone","tel"],
  "is_system" => ["is_system","boolean"],

  "form" => ["form","text",2,72],
  "CSRF_token" => ["CSRF_token","text",5,1024]
], $post, ["server", "code", "form", "CSRF_token"]);

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

$params["code"] = \str_replace([" ", "-", ".", "_"],"",$params["code"]);
$dev = (new MultiForm($db_name, "users", "code", $conn))->findById($params["code"]);

if (!$dev) {
  echo \json_encode([
    "status" => "3.1",
    "errors" => ["Developer profile was not found on the given [server]"],
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
 if (!empty($value)) $dev->$prop = $value;
}
$dev->empty_prop = ["is_system"];
$dev->is_system = (bool)$params['is_system'] ? 1 : 0;
if (!$dev->update()) {
  $do_errors = [];

  $dev->mergeErrors();
  $more_errors = (new InstanceError($dev,true))->get('',true);
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
// send notice email
$site_name = get_server_url($server_name);
$code = code_split($dev->code, " ");
$msg = <<<MSG
<p>Dear {$dev->name}, <br>
Your Developer profile was updated on <a href="{$site_name}">{$site_name}</a>
</p>
<h3>New profile detail</h3>
<table class="horizontal">
  <tr>
    <th>Unique ID</th>
    <td>{$code}</td>
  </tr>
  <tr>
    <th>Name</th>
    <td>{$dev->name}</td>
  </tr>
  <tr>
    <th>Surname</th>
    <td>{$dev->surname}</td>
  </tr>
  <tr>
    <th>Email</th>
    <td>{$dev->email}</td>
  </tr>
  <tr>
    <th>Phone</th>
    <td>{$dev->phone}</td>
  </tr>
  <tr>
    <th>Country</th>
    <td>{$dev->country_code}</td>
  </tr>
</table>
<p>Best regards</p>
MSG;
$new_cred = db_cred(get_constant("PRJ_EMAIL_SERVER"), "DEVELOPER");
$eml_conn = new MySQLDatabase(get_dbserver(get_constant("PRJ_EMAIL_SERVER")), $new_cred[0], $new_cred[1]);
$acronym = domain_acronym(get_constant("PRJ_DOMAIN"));
try {
  $eml = new Email("","",$eml_conn);
  $eml->prep($system_user, "Your Developer profile was updated.", $msg);
  if ($acronym) {
    $eml->setOrigin($acronym);
  }
  if (!$eml->queue(
    3, 
    (new Mailer\Profile(Generic::splitEmailName(get_constant("PRJ_AUTO_EMAIL")), "", "", $eml_conn)), 
    (new Email\Recipient($eml->code(), Generic::splitEmailName("{$params["name"]} {$params["surname"]} <{$params["email"]}>"), "to", "", $eml_conn))
  )) {
    $do_errors = [];
    $eml->mergeErrors();
    $more_errors = (new InstanceError($eml, true))->get('',true);
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
    }
  }
} catch (\Throwable $th) {
  die(\json_encode([
    "status" => "5.1",
    "errors" => ["Failed to queue/send response email.", $th->getMessage()],
    "message" => "Request halted."
  ]));
} 

echo \json_encode([
  "status" => "0.0",
  "errors" => [],
  "message" => "Request was successful!"
]);
exit;
