<?php
namespace IO;
require_once "../../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\MultiForm,
    \TymFrontiers\InstanceError;

\require_login(false);
\check_access("WRITE", "/apps", "project-dev","", false);
$errors = [];
$gen = new Generic;
$app = false;
$params = $gen->requestParam([
  "user" => ["user","pattern", "/^(352|252|052)(\s|\-|\.)?([0-9]{4,4})(\s|\-|\.)?([0-9]{4,4})$/"],
  "callback" => ["callback","username",3,35,[],'MIXED']
], $_GET, []);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if( $params ):
endif;
if (!empty($params["user"]))  $params["user"] = \str_replace([" ", "-", ".", "_"],"",$params["user"]);
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
          <h1 class="fw-lighter"> <i class="fas fa-plus"></i> API App</h1>
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
            action="/app/developer/src/PostApp.php"
            data-validate="false"
            onsubmit="cwos.form.submit(this,doPost); return false;"
            >
            
            <div class="grid-6-tablet">
              <label> <i class="fas fa-database"></i> Server</label>
              <select name="server" required id="server">
                <optgroup label="API Servers">
                  <?php foreach (get_servers() as $name => $info) {
                    echo "<option value=\"{$name}\">{$name} ({$info['domain']})</option>";
                  }?>
                </optgroup>
              </select>
            </div>
            <div class="grid-5-tablet">
              <label for="user">User</label>
              <input type="text" value="<?php echo !empty($params['user']) ? $params['user'] : ''; ?>" name="user" required pattern="(352|252|052)([\-|\s]{1,1})?([\d]{4,4})([\-|\s]{1,1})?([\d]{4,4})" id="user" value="<?php echo !empty($params['user']) ? $params['user'] : "";  ?>" placeholder="252 0000 0000">
            </div>
            <br class="c-f">
            <div class="grid-5-tablet">
              <label for="name"> <i class="fas fa-asterisk fa-border"></i> App Name</label>
              <input type="text" pattern="([a-z0-9\.\-]{4,128})" name="name" id="name" required placeholder="app-name">
            </div>
            <div class="grid-7-tablet">
              <label for="domain">Domain</label>
              <input type="text" pattern="([a-z0-9\.\-]{4,128})" name="domain" id="domain" placeholder="domain-name.ext">
            </div> <br class="c-f">
            <div class="grid-6-tablet">
              <label for="endpoint">Endpoint</label>
              <input type="text" pattern="([a-z0-9\-\/]{4,56})" name="endpoint" id="endpoint" placeholder="/callback/endpoint">
            </div> <br class="c-f">
            <div class="grid-3-tablet">
              <label for="prefix"> <i class="fas fa-asterisk fa-border"></i> Prefix</label>
              <input type="text" pattern="([A-Z0-9]{2,5})" name="prefix" id="prefix" required placeholder="APP">
            </div>
            <div class="grid-7-tablet">
              <label for="title"> <i class="fas fa-asterisk fa-border"></i> App Title</label>
              <input type="text" minlength="5" maxlength="52" name="title" id="title" required placeholder="App/project title">
            </div>
            <div class="grid-12-tablet">
              <label for="description">Description</label>
              <textarea name="description" id="description" minlength="15" maxlength="127" required class="autosize" placeholder="Enter app description here"></textarea>
            </div>
            <div class="grid-5-tablet">
              <label> Activate</label> <br>
              <input type="checkbox" name="status" value="ACTIVE" id="activate">
              <label for="activate">Yes</label>
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
