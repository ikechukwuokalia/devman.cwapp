<?php
namespace IO;
require_once "../../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\MultiForm,
    \TymFrontiers\MySQLDatabase,
    \TymFrontiers\InstanceError;

\require_login(false);
\check_access("UPDATE", "/users", "project-dev","", false);
$errors = [];
$gen = new Generic;
$app = false;
$params = $gen->requestParam([
  "code" => ["code","pattern", "/^352([\d]{4,4})([\d]{4,4})$/"],
  "server" => ["server","option", get_server_keys()],
  "callback" => ["callback","username",3,35,[],'MIXED']
], $_GET, ["code", "server"]);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
$dev = false;
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
    $params["code"] = \str_replace([" ", "-", ".", "_"],"",$params["code"]);
    if (!$dev = (new MultiForm($db_name, "users", "code", $conn))->findById($params["code"])) {
      $errors[] = "Developer profile was not found for given [code].";
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
          <h1 class="fw-lighter"> <i class="fas fa-user-circle"></i> Developer's info</h1>
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
            action="/app/developer/src/UpdateUser.php"
            data-validate="false"
            onsubmit="cwos.form.submit(this,doPost); return false;"
            >
            <input type="hidden" name="server" value="<?php echo @ $params['server']; ?>">
            <input type="hidden" name="country_code" value="<?php echo $dev ? $dev->country_code : ""; ?>">
            <input type="hidden" name="code" value="<?php echo $dev ? $dev->code : ""; ?>">
            
            <div class="grid-6-tablet">
              <label for="name"> <i class="fas fa-asterisk fa-border"></i> Name</label>
              <input type="text" value="<?php echo $dev ? $dev->name : ''; ?>" name="name" id="name" required placeholder="Name">
            </div>
            <div class="grid-6-tablet">
              <label for="surname"> <i class="fas fa-asterisk fa-border"></i> Surname</label>
              <input type="text" value="<?php echo $dev ? $dev->surname : ''; ?>" name="surname" id="surname" required placeholder="Surname">
            </div>
            <div class="grid-7-tablet">
              <label for="email"> <i class="fas fa-asterisk fa-border"></i> Email</label>
              <input type="email" value="<?php echo $dev ? $dev->email : ''; ?>" name="email" id="email" required placeholder="email@domain.com">
            </div>
            <div class="grid-5-tablet">
              <label for="phone"> <i class="fas fa-asterisk fa-border"></i> Phone</label>
              <input type="tel" value="<?php echo $dev ? $dev->phone : ''; ?>" name="phone" id="phone" required placeholder="+234 801 2345 678">
            </div>
            <div class="grid-6-tablet">
              <label> Is this system user?</label> <br>
              <input type="checkbox" name="is_system" <?php echo $dev && (bool)$dev->is_system ? " checked" : ""; ?> value="1" id="is-sys">
              <label for="is-sys">Yes</label>
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
  })();
</script>
