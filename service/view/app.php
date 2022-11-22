<?php
namespace TymFrontiers;
require_once "../.appinit.php";
\require_login(false);
\check_access("READ", "/apps", "project-dev","", false);

$errors = [];
$gen = new Generic;
$data_obj = new Data;
$tym = new BetaTym;
$required = ['name'];
$pre_params = [
  "name" => ["name","username",5,55,[],'LOWER',['-',"."]],
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if (!empty($params['name'])) {
  if (!$app = API\DevApp::findById($params["name"]) ) $errors[] = "No app found!";
}
$data =  new Data;
?>
<input
  type="hidden"
  id="rparam"
  <?php if($params){ foreach($params as $k=>$v){
    echo "data-{$k}=\"{$v}\" ";
  } }?>
  >
<div id="fader-flow">
  <div class="view-space">
    <div class="padding -p20">&nbsp;</div>
    <br class="c-f">
    <div class="grid-10-tablet grid-8-laptop center-tablet">
      <div class="sec-div color face-primary bg-white drop-shadow">
        <header class="padding -p20 color-bg">
          <h1 class="fw-lighter"> <i class="fas fa-layer-group"></i> App info</h1>
        </header>

        <div class="padding -p20">
          <?php if(!empty($errors)){ ?>
            <h3>Unresolved error(s)</h3>
            <ol>
              <?php foreach($errors as $err){
                echo " <li>{$err}</li>";
              } ?>
            </ol>
          <?php }else{ ?>
            <h1 class="color-text"><?php echo $app->title; ?></h1>
            <table class="horizontal">
              <tr>
                <th class="color-text">Status</th>
                <td><?php echo ((bool)$app->live ? "Live | " : "" ). $app->status; ?></td>
              </tr>
              <tr>
                <th class="color-text">Name</th>
                <td><?php echo $app->name; ?></td>
              </tr>
              <tr>
                <th class="color-text">Parameter prefix</th>
                <td><?php echo $app->prefix; ?></td>
              </tr>
              <tr>
                <th class="color-text">Domain</th>
                <td><?php echo $app->domain; ?></td>
              </tr>
              <tr>
                <th class="color-text">Endpoint</th>
                <td><?php echo $app->endpoint; ?></td>
              </tr>
              <tr>
                <th class="color-text">Request timeout</th>
                <td><?php echo (new API\DevApp)->request_timeout_opt[$app->request_timeout]; ?></td>
              </tr>
              <tr>
                <th class="color-text">Public key</th>
                <td class="form block-ui"> <input class="sel-input" type="password" onmouseup="$(this).attr('type','text').select()" readonly value="<?php echo $app->publicKey() ?>"></td>
              </tr>
              <tr>
                <th class="color-text">Private key</th>
                <td class="form block-ui"> <input class="sel-input" type="password" onmouseup="$(this).attr('type','text').select()" readonly value="<?php echo $app->privateKey() ?>"></td>
              </tr>
              <tr>
                <th class="color-text">Created</th>
                <td><?php echo $tym->dateTym($app->created()); ?></td>
              </tr>
              <tr>
                <th class="color-text">Last updated</th>
                <td><?php echo $tym->dateTym($app->updated()); ?></td>
              </tr>
            </table>
            <h3>[Description]</h3>
            <?php echo $app->description; ?>
        <?php } ?>
      </div>
    </div>
  </div>
  <br class="c-f">
</div>
</div>

<script type="text/javascript">
  var param = $('#rparam').data();
  (function(){
    $(".form.block-ui, body").on("click","",function(){
      $(".sel-input").attr("type","password").trigger("blur");
    });
    $(".sel-input").on("click","",function(evt){
      evt.stopPropagation();
    });
  })();
</script>
