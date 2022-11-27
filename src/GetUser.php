<?php
namespace IO;

use TymFrontiers\BetaTym;
use TymFrontiers\Data;
use \TymFrontiers\Generic,
    \TymFrontiers\MySQLDatabase,
    \TymFrontiers\InstanceError,
    \TymFrontiers\MultiForm;
use TymFrontiers\Validator;

require_once "../.appinit.php";
\check_access("READ", "/users", "project-dev","", false);

\header("Content-Type: application/json");

$post = !empty($_POST) ? $_POST : $_GET;
$gen = new Generic;
$params = $gen->requestParam([
  "code" => ["code","pattern", "/^(252|052|352)(\s|\-|\.)?([\d]{4,4})(\s|\-|\.)?([\d]{4,4})$/"],
  "server" => ["server","option", get_server_keys()],
  "status" => ["status","option", ["ACTIVE", "DISABLED", "BANNED"]],
  "search" => ["search","text",3,56],
  "page" =>["page","int",1,0],
  "limit" =>["limit","int",1,0]
], $post, ["server"] );

if (!$params || !empty($gen->errors)) {
  $errors = (new InstanceError ($gen, false))->get("requestParam",true);
  echo \json_encode([
    "status" => "3." . \count($errors),
    "errors" => $errors,
    "message" => "Request halted"
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
$count = 0;
$data = new MultiForm($db_name, 'users', 'code', $conn);
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$qs = "SELECT usr.code, usr.user, usr.is_system, usr.status, usr.name, 
              usr.surname, usr.email, usr.phone, usr._created
      FROM :db:.:tbl: AS usr ";
$cnd = " WHERE 1 ";
if(!empty($params['code'])) {
  $params["code"] = \str_replace([" ", "-", ".", "_"],"",$params["code"]);
  $cnd .= " AND usr.code = '{$params["code"]}' ";
} else {
  if (!empty($params["status"])) {
    $cnd .= " AND usr.`status` = '{$params['status']}' ";
  }
  if( !empty($params['search']) ){
    $params['search'] = \strtoupper($db->escapeValue(\strtolower($params['search'])));
    $cnd .= " AND (
      usr.`code` = '{$params['search']}'
      OR usr.`user` = '{$params['search']}'
      OR LOWER(usr.`name`) LIKE '%{$params['search']}%'
      OR LOWER(usr.`surname`) LIKE '%{$params['search']}%'
      OR LOWER(usr.`email`) = '{$params['search']}'
      OR LOWER(usr.`phone`) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS usr {$cnd} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['code']) ? 1 : (
  (int)$params['limit'] > 0 ? (int)$params['limit'] : 25
);
$qs .= $cnd;
$sort = " ORDER BY usr.`name` ASC ";

$qs .= $sort;
$qs .= " LIMIT {$data->per_page} ";
$qs .= " OFFSET {$data->offset()}";

$found = $data->findBySql($qs);
$tym = new \TymFrontiers\BetaTym;

if( !$found ){
  die( \json_encode([
    "message" => "No result found for your query.",
    "errors" => [],
    "status" => "0.2"
    ]) );
}
$result = [
  'status' => '0.0',
  'errors' => [],
  'message' => 'Request completed',
  'records' => (int)$count,
  'page'  => $data->current_page,
  'pages' => $data->totalPages(),
  'limit' => $limit,
  'previousPage' => $data->hasPreviousPage() ? $data->previousPage() : false,
  'nextPage' => $data->hasNextPage() ? $data->nextPage() : false
];
if ($new_conn) $conn->closeConnection();
$data_obj = new Data;
$tym = new BetaTym;
foreach ($found as $usr) {
  $result["users"][] = [
    "code" => $usr->code,
    "codeSplit" => code_split($usr->code, " "),
    "user" => $usr->user,
    "userSplit" => code_split($usr->user, " "),
    "isSystem" => (bool)$usr->is_system,
    "server" => $params['server'],
    "status" => $usr->status,
    "name" => $usr->name,
    "surname" => $usr->surname,
    "email" => $usr->email,
    "emailMask" => email_mask($usr->email),
    "phone" => $usr->phone,
    "phoneLocal" => $data_obj->phoneToLocal($usr->phone),
    "phoneMask" => phone_mask($data_obj->phoneToLocal($usr->phone)),
    "created" => $tym->MDY($usr->created()),
    "created_date" => $usr->created()
  ];
}
echo \json_encode($result);
exit;
