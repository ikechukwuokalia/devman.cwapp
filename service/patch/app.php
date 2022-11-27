<?php
namespace IO;
require_once "../../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\MultiForm,
    \TymFrontiers\MySQLDatabase,
    \TymFrontiers\InstanceError;

\require_login(false);
\check_access("UPDATE", "/apps", "project-dev","", false);
$errors = [];
$gen = new Generic;
$app = false;
$params = $gen->requestParam([
  "name" => ["name","pattern", "/^([a-z0-9\.\-]{4,128})$/s"],
  "server" => ["server","option", get_server_keys()],
  "callback" => ["callback","username",3,35,[],'MIXED']
], $_GET, ["name", "server"]);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if( $params ):
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
    $errors[] = "Failed to connect to server.";
  } else {
    $db_name = get_database("developer", $server_name);
    $conn->changeDB($db_name);
    if (!$app = (new MultiForm($db_name, "apps", "name", $conn))->findById($params["name"])) {
      $errors[] = "Dev App profile was not found for given [name].";
    }
  }
endif;
?>
<script type="text/javascript">
  if (typeof window["param"] == undefined || !window["param"]) window["param"] = {};
  <?php if (!empty($params) && \is_array($params)) { foreach ($params as $k=>$val) { echo "param['{$k}'] = '{$val}';"; } } ?>
</script>

<div id="fader-flow">
  <div class="view-space">
    <div class="paddn -pall -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-10-tablet grid-8-laptop center-tablet">
      <div class="sec-div theme-color asphalt bg-white drop-shadow">
        <header class="paddn -pall -p20 color-bg">
          <h1 class="fw-lighter"> <i class="fas fa-edit"></i> API App</h1>
        </header>

        <div class="paddn -pall -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <form
            id="do-post-form"
            class="block-ui"
            method="post"
            action="/app/developer/src/UpdateApp.php"
            data-validate="false"
            onsubmit="cwos.form.submit(this,doPost); return false;"
            >
            <input type="hidden" name="server" value="<?php echo @ $params['server']; ?>">
            <input type="hidden" name="name" value="<?php echo @ $params['name']; ?>">
            
            <div class="grid-6-tablet">
              <label for="domain">Domain</label>
              <input type="text" value="<?php echo $app ? $app->domain : ""; ?>" pattern="([a-z0-9\.\-]{4,128})" name="domain" id="domain" placeholder="domain-name.ext">
            </div>
            <div class="grid-5-tablet">
              <label for="endpoint">Endpoint</label>
              <input type="text" value="<?php echo $app ? $app->endpoint : ""; ?>" pattern="([a-z0-9\-\/]{4,56})" name="endpoint" id="endpoint" placeholder="/callback/endpoint">
            </div>
            <div class="grid-7-tablet">
              <label for="title"> <i class="fas fa-asterisk fa-border"></i> App Title</label>
              <input type="text" value="<?php echo $app ? $app->title : ""; ?>" minlength="5" maxlength="52" name="title" id="title" required placeholder="App/project title">
            </div>
            <div class="grid-12-tablet">
              <label for="description">Description</label>
              <textarea name="description" id="description" minlength="15" maxlength="127" required class="autosize" placeholder="Enter app description here"><?php echo $app ? $app->description : ""; ?></textarea>
            </div>

            <div class="grid-4-tablet"> 
              <button id="submit-form" type="submit" class="theme-btn asphalt"> <i class="fas fa-save"></i> Save </button>
            </div>

            <br class="c-f">
          </form>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  (function(){
    $("textarea.autosize").autosize();
  })();
</script>
