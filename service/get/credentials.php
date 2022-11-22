<?php
namespace TymFrontiers;
require_once "../.appinit.php";
\require_login(false);
\check_access("SECRETE", "/apps", "project-dev","", false);

$errors = [];
$gen = new Generic;
$data_obj = new Data;
$tym = new BetaTym;
$required = ['app'];
$pre_params = [
  "app" => ["app","username",5,55,[],'LOWER',['-',"."]],
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if (!empty($params['app'])) {
  if (!$app = API\DevApp::findById($params["app"]) ) $errors[] = "No app found!";
}
try {
  $cred = API\AuthHeader::generate($app->name, $app->publicKey(), $app->privateKey());
} catch (\Exception $e) {
  $errors[] = $e->getMessage();
}
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
          <h1 class="fw-lighter"> <i class="fas fa-layer-group"></i> App Auth header credentials</h1>
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
              <?php foreach ($cred as $key => $value): ?>
                <tr>
                  <th class="color-text"><?php echo $key; ?></th>
                  <td class="form block-ui"> <input class="sel-input" type="text" onmouseup="$(this).select()" readonly value="<?php echo $value; ?>"></td>
                </tr>
              <?php endforeach; ?>
            </table>
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
  })();
</script>
