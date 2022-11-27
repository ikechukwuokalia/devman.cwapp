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
\check_access("READ", "/apps", "project-dev","", false);

\header("Content-Type: application/json");

$post = !empty($_POST) ? $_POST : $_GET;
$gen = new Generic;
$params = $gen->requestParam([
  "name" => ["name","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "user" => ["user","pattern", "/^(352|252|052)(\s|\-|\.)?([0-9]{4,4})(\s|\-|\.)?([0-9]{4,4})$/"],
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
$data = new MultiForm($db_name, 'apps', 'name', $conn);
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$qs = "SELECT app.name, app.user, app.status, app._pu_key AS pu_key,
              app.prefix, app.domain, app.endpoint, 
              app.title, app.description, app._created, app._updated,
              (
                SELECT COUNT(*)
                FROM :db:.request_history
                WHERE `app` = app.name
              ) AS requests
      FROM :db:.:tbl: AS app ";
$cnd = " WHERE 1 ";
if(!empty($params['name'])) {
  $cnd .= " AND app.name = '{$database->escapeValue($params["name"])}' ";
} else {
  if (!empty($params["status"])) {
    $cnd .= " AND app.`status` = '{$params['status']}' ";
  } if (!empty($params["user"])) {
    $params['user'] = \str_replace(["-", ".", "_", " "], "", $params['user']);
    $cnd .= " AND app.`user` = '{$params['user']}' ";
  }
  if( !empty($params['search']) ){
    $params['search'] = \strtoupper($db->escapeValue(\strtolower($params['search'])));
    $cnd .= " AND (
      app.`name` = '{$params['search']}'
      OR app.`user` = '{$params['search']}'
      OR LOWER(app.`name`) LIKE '%{$params['search']}%'
      OR LOWER(app.`domain`) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS app {$cnd} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['name']) ? 1 : (
  (int)$params['limit'] > 0 ? (int)$params['limit'] : 25
);
$qs .= $cnd;
$sort = " ORDER BY app.`name` ASC ";

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
foreach ($found as $app) {
  $result["apps"][] = [
    "name" => $app->name,
    "puKey" => $app->pu_key,
    "user" => $app->user,
    "userSplit" => code_split($app->user, " "),
    "server" => $params['server'],
    "status" => $app->status,
    "name" => $app->name,
    "prefix" => $app->prefix,
    "domain" => $app->domain,
    "endpoint" => $app->endpoint,
    "title" => $app->title,
    "description" => $app->description,
    "requests" => (int)$app->requests,

    "created" => $tym->MDY($app->created()),
    "created_date" => $app->created(),
    "updated" => $tym->MDY($app->updated()),
    "updated_date" => $app->updated()
  ];
}
echo \json_encode($result);
exit;
