<?php
namespace TymFrontiers;
require_once "../.appinit.php";
\require_login(false);
\check_access("READ", "/request-history", "project-dev","", false);

$errors = [];
$gen = new Generic;
$data_obj = new Data;
$tym = new BetaTym;
$required = ['id'];
$pre_params = [
  "id" => ["id","int"],
];
// if( empty($_GET['id']) ) $required[] = 'owner';
$params = $gen->requestParam($pre_params,$_GET,$required);
if (!$params || !empty($gen->errors)) {
  $errs = (new InstanceError($gen,true))->get("requestParam",true);
  foreach ($errs as $er) {
    $errors[] = $er;
  }
}
if (!empty($params['id'])) {
  if (!$log = (new MultiForm(MYSQL_DEV_DB, "request_history", "id"))->findById($params["id"]) ) $errors[] = "No record found!";
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
          <h1 class="fw-lighter"> <i class="fas fa-info-circle"></i> Log info</h1>
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
            <table class="horizontal">
              <tr>
                <th class="color-text">ID</th>
                <td><?php echo $log->id; ?></td>
              </tr>
              <tr>
                <th class="color-text">App</th>
                <td><?php echo $log->app; ?></td>
              </tr>
              <tr>
                <th class="color-text">Path</th>
                <td><?php echo $log->path; ?></td>
              </tr>
              <tr>
                <th class="color-text">Created</th>
                <td><?php echo $tym->dateTym($log->created()); ?></td>
              </tr>
            </table>
            <h3>[Request data]</h3>
            <div class="form block-ui">
              <textarea name="name" class="autosize" readonly onclick="$(this).select();"><?php echo $log->param; ?></textarea>
            </div>

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
    $("textarea.autosize").autosize();
  })();
</script>
