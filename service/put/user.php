<?php
namespace Catali;
require_once "../../.appinit.php";
use \TymFrontiers\Generic,
    \TymFrontiers\MultiForm,
    \TymFrontiers\InstanceError;

\require_login(false);
\check_access("WRITE", "/users", "project-dev","", false);
$errors = [];
$gen = new Generic;
$app = false;
$params = $gen->requestParam([
  "user" => ["user","pattern", "/^252([\d]{4,4})([\d]{4,4})$/"],
  "country_code" => ["country_code","username", 2,2],
  "email" => ["email","email"],
  "phone" => ["phone","tel"],
  "name" => ["name","name"],
  "surname" => ["surname","name"],
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
          <h1 class="fw-lighter"> <i class="fal fa-user-circle"></i> Developer's info</h1>
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
            action="/app/developer/src/PutUser.php"
            data-validate="false"
            onsubmit="cwos.form.submit(this,doPost); return false;"
            >
            <input type="hidden" name="form" value="devuser-form">
            <input type="hidden" name="CSRF_token" value="<?php echo ($session->createCSRFtoken("devuser-form"));?>">
            
            <div class="grid-6-tablet">
              <label> <i class="fas fa-flag"></i> Country/regions</label>
              <select name="country_code" required id="countr-code">
                <option value="">* Choose a country</option>
                <optgroup label="Countries">
                  <?php if ($countries = (new MultiForm(get_database("CWS", "data"), "countries", "code"))->findAll()) {
                    foreach ($countries as $country) {
                      echo "<option value=\"{$country->code}\"";
                        echo !empty($params['country_code']) && $country->code == $params['country_code'] ? " selected" : "";
                      echo ">{$country->name}</option>";
                    }
                  } ?>
                </optgroup>
              </select>
            </div>
            <div class="grid-6-tablet">
              <label> <i class="fas fa-database"></i> Server</label>
              <select name="server" required id="server">
                <optgroup label="API Servers">
                  <?php foreach (get_cws_server() as $name => $info) {
                    echo "<option value=\"{$name}\">{$name} ({$info['domain']})</option>";
                  }?>
                </optgroup>
              </select>
            </div> <br class="c-f">
            <div class="grid-5-tablet">
              <label for="user">User</label>
              <input type="text" value="<?php echo !empty($params['user']) ? $params['user'] : ''; ?>" name="user" required pattern="252([\-|\s]{1,1})?([\d]{4,4})([\-|\s]{1,1})?([\d]{4,4})" id="user" placeholder="252 0000 0000">
            </div>
            <br class="c-f">
            <div class="grid-6-tablet">
              <label for="name"> <i class="fal fa-asterisk fa-border"></i> Name</label>
              <input type="text" value="<?php echo !empty($params['name']) ? $params['name'] : ''; ?>" name="name" id="name" required placeholder="Name">
            </div>
            <div class="grid-6-tablet">
              <label for="surname"> <i class="fal fa-asterisk fa-border"></i> Surname</label>
              <input type="text" value="<?php echo !empty($params['surname']) ? $params['user'] : ''; ?>" name="surname" id="surname" required placeholder="Surname">
            </div>
            <div class="grid-7-tablet">
              <label for="email"> <i class="fal fa-asterisk fa-border"></i> Email</label>
              <input type="email" value="<?php echo !empty($params['email']) ? $params['email'] : ''; ?>" name="email" id="email" required placeholder="email@domain.com">
            </div>
            <div class="grid-5-tablet">
              <label for="phone"> <i class="fal fa-asterisk fa-border"></i> Phone</label>
              <input type="tel" value="<?php echo !empty($params['phone']) ? $params['phone'] : ''; ?>" name="phone" id="phone" required placeholder="+234 801 2345 678">
            </div>
            <div class="grid-3-tablet">
              <label> Status</label> <br>
              <input type="checkbox" name="status" value="ACTIVE" id="status-active">
              <label for="status-active">Activate</label>
            </div>
            <div class="grid-5-tablet">
              <label> Is this system user?</label> <br>
              <input type="checkbox" name="is_system" value="1" id="is-sys">
              <label for="is-sys">Yes</label>
            </div>
            <div class="grid-4-tablet"> 
              <button id="submit-form" type="submit" class="theme-btn asphalt"> <i class="fal fa-save"></i> Save </button>
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
    $("select[name=server]").on("change", "", function(){
      if ($(this).val().length) {
        $("select#auth-app").val("");
        $("select#auth-app optgroup").html("");
        helpr_rsc(`/app/developer/get/server-app`, function(data) {
          update_select("select#auth-app optgroup", data);
        }, {server : $(this).val()}, {type: "GET", strict: true});  
      }
    });
  })();
</script>
