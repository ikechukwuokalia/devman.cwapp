<?php
namespace Catali;
require_once "../.appinit.php";

\require_login(true);
\check_access("READ", "/server-apps", "project-dev","", false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Dev Apps | <?php echo get_constant("PRJ_TITLE"); ?></title>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <?php include get_constant("PRJ_INC_ICONSET"); ?>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'>
  <meta name="author" content="<?php echo get_constant("PRJ_AUTHOR"); ?>">
  <meta name="creator" content="<?php echo get_constant("PRJ_CREATOR"); ?>">
  <meta name="publisher" content="<?php echo get_constant("PRJ_PUBLISHER"); ?>">
  <meta name="robots" content='nofollow'>
  
    <!-- Theming styles -->
  <link rel="stylesheet" href="/app/tymfrontiers/font-awesome-pro.soswapp/css/font-awesome-pro.min.css">
  <!-- Project styling -->
  <link rel="stylesheet" href="/app/cataliwos/plugin.cwapp/css/theme.min.css">
  <link rel="stylesheet" href="/app/cataliwos/dashui.cwapp/css/dashui.min.css">
  <link rel="stylesheet" href="/app/cataliws/helper.cwapp/css/helper.min.css">
  <link rel="stylesheet" href="/assets/css/base.min.css">
  <link rel="stylesheet" href="/app/cataliws/devman.cwapp/css/devman.min.css">
</head>
<body class="theme-asphalt">
  <div id="cwos-uiloadr"></div>
  <input type="hidden" data-setup="page" data-name="dev-apps" data-group="dev">
  <input type="hidden" data-setup="ui" data-handler="DashUI" data-header="/app/index/get/dashui/header?rdt=<?php echo THIS_PAGE; ?>" data-sidebar="/app/index/get/dashui/sidebar" data-autoinit="true">
  <input type="hidden" data-setup="uiOption" data-hide="true" data-max-cart-item="6" data-max-notice-item="6">
  <input type="hidden" data-setup="uiNotification" data-delete="/app/helper/delete/notification" data-path="/app/user/notifications" data-get="/app/helper/get/notification">
  <input type="hidden" data-setup="uiCart" data-delete="/app/helper/delete/cart" data-path="/index/checkout" data-get="/app/helper/get/cart">
  <input type="hidden" data-setup="dnav" data-group="dev" data-clear-elem="#cwos-content" data-pos="affix" data-container="#cwos-content" data-get="/app/index/get/navigation" data-ini-top-pos="0" data-stick-on="">

  <section id="cwos-content">
    <form id="post-form" method="post" action="/app/dev/post/app" data-validate="false" onsubmit="cwos.form.submit(this,checkPost); return false;" >
      <input type="hidden" name="form" value="app-update-form">
      <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("app-update-form");?>">
      <input type="hidden" name="name" value="">
      <input type="hidden" name="status" value="">
      <input type="hidden" name="live" value="">
    </form>
    <div class="view-space">
      <br class="c-f">
        <div class="grid-10-tablet grid-8-laptop grid-8-desktop center-tablet">
          <form
            id="query-form"
            class="block-ui theme-color asphalt paddn -pall -p20"
            method="post"
            action="/app/dev/get/app"
            data-validate="false"
            onsubmit="cwos.form.submit(this, doFetch); return false;"
            >
            <input type="hidden" name="form" value="app-query-form">
            <input type="hidden" name="CSRF_token" value="<?php echo $session->createCSRFtoken("app-query-form");?>">

            <div class="grid-6-tablet">
              <label> <i class="fas fa-database"></i> Server</label>
              <select name="server" id="">
                <option value="">* All</option>
                <optgroup label="Servers">
                <?php
                  foreach ($cws_servers as $name => $prop) {
                    if ($name !== "BASE") {
                      echo "<option value=\"{$name}\"> {$name} ({$prop['domain']})";
                    }
                  }
                ?>
                </optgroup>
              </select>
            </div>
            <div class="grid-6-tablet">
              <label for="search"> <i class="fas fa-search"></i> Search</label>
              <input type="search" name="search" value="<?php echo !empty($_GET['search']) ? $_GET['search'] :''; ?>" id="search" placeholder="Keyword search">
            </div>
            <br class="c-f">
            <div class="grid-6-phone grid-4-tablet grid-3-laptop">
              <label for="page"> <i class="fas fa-file-alt"></i> Page</label>
              <input type="number" name="page" id="page" class="page-val" placeholder="1" value="1">
            </div>
            <div class="grid-6-phone grid-4-tablet grid-3-laptop">
              <label for="limit"> <i class="fas fa-sort-numeric-up"></i> Limit</label>
              <input type="number" name="limit" id="limit" class="page-limit" placeholder="25" value="25">
            </div>
            <div class="grid-6-phone grid-4-tablet"> <br>
              <button type="submit" class="theme-button asphalt"> <i class="fas fa-search"></i></button>
            </div>
            <br class="c-f">
          </form>
          <p class="align-c">
            <b>Records:</b> <span class="records-text">00</span> |
            <b>Pages:</b> <span class="pages-text">00</span>
          </p>
        </div>

        <div class="sec-div paddn -pall -p20">
          <h2>App list</h2>
          <table class="vertical theme-color asphalt clear-padding">
            <thead class="color-text align-l border -bthin -bbottom">
              <tr>
                <th>Name | Staus</th>
                <th>Title</th>
                <th>Owner</th>
                <th>Requests</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="app-list"></tbody>
          </table>
          <div id="data-pager">
          </div>
          <br class="c-f">
        </div>

      <br class="c-f">
    </div>
    <div class="push-foot">&nbsp;</div>
  </section>

    <div id="actn-btns">
      <div id="actn-btn-wrp">
        <div id="scrl-wrp">
          <button class="theme-button ashalt block" onclick="cwos.faderBox.url('/app/dev/put/app', {callback : 'requery'}, {exitBtn: true});"> <i class="fal fa-plus fa-lg"></i> New App</button>
        </div>
      </div>
      <button id="actvt" type="button" class="cwos-button"> <i class="fad fa-angle-right"></i> <span class="btn-txt">Start</span></button>
    </div>
    <!-- Required scripts -->
    <script src="/app/cataliwos/plugin.cwapp/js/jquery.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/functions.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/class-object.min.js"></script>
    <script src="/app/cataliwos/dashui.cwapp/js/dashui.min.js"></script>
    <script src="/app/cataliwos/plugin.cwapp/js/theme.min.js"></script>
    <script src="/assets/js/base.min.js"></script>
    <script src="/app/cataliws/devman.cwapp/js/devman.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function() {
        // requery();
      });
    </script>
  </body>
</html>
