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
\check_access("READ", "/request-history", "project-dev","", false);

\header("Content-Type: application/json");

$post = !empty($_POST) ? $_POST : $_GET;
$gen = new Generic;
$params = $gen->requestParam([
  "id" => ["id","int"],
  "app" => ["app","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "server" => ["server","option", get_server_keys()],
  "search" => ["search","text",1,56],
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
$data = new MultiForm($db_name, 'request_history', 'id', $conn);
$data->current_page = $page = (int)$params['page'] > 0 ? (int)$params['page'] : 1;
$qs = "SELECT hst.id, hst.app, hst.path, hst.param, hst._created
      FROM :db:.:tbl: AS hst ";
$cnd = " WHERE 1 ";
if(!empty($params['id'])) {
  $cnd .= " AND hst.id = {$params["id"]} ";
} else {
  if (!empty($params["app"])) {
    $cnd .= " AND hst.`app` = '$database->escapeValue({$params['app']})' ";
  }
  if( !empty($params['search']) ){
    $params['search'] = \strtoupper($db->escapeValue(\strtolower($params['search'])));
    $cnd .= " AND (
      hst.`id` = '{$params['search']}'
      OR LOWER(hst.`app`) LIKE '%{$params['search']}%'
      OR LOWER(hst.`path`) LIKE '%{$params['search']}%'
    ) ";
  }
}

$count = $data->findBySql("SELECT COUNT(*) AS cnt FROM :db:.:tbl: AS hst {$cnd} ");
// echo $db->last_query;
$count = $data->total_count = $count ? $count[0]->cnt : 0;

$data->per_page = $limit = !empty($params['id']) ? 1 : (
  (int)$params['limit'] > 0 ? (int)$params['limit'] : 25
);
$qs .= $cnd;
$sort = " ORDER BY hst.`_created` DESC ";

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
foreach ($found as $hst) {
  $result["histories"][] = [
    "id" => (int)$hst->id,
    "app" => $hst->app,
    "path" => $hst->path,
    "param" => $hst->param,

    "created" => $tym->MDY($hst->created()),
    "createdDate" => $hst->created()
  ];
}
echo \json_encode($result);
exit;
