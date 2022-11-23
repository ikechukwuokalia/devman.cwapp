<?php
namespace Catali;
require_once ".appinit.php";
\require_login(true);
$nav_group = "developers";
$navs = get_navgroup($nav_group);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Developers | <?php echo get_constant("PRJ_TITLE"); ?></title>
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
<body class="theme-<?php echo \IO\get_constant("PRJ_THEME"); ?>">
  <div id="cwos-uiloadr"></div>
  <input type="hidden" data-setup="page" data-name="dev" data-group="dev">
  <input type="hidden" data-setup="ui" data-handler="DashUI" data-header="/app/index/get/dashui/header?rdt=<?php echo THIS_PAGE; ?>" data-sidebar="/app/index/get/dashui/sidebar" data-autoinit="true">
  <input type="hidden" data-setup="uiOption" data-hide="true" data-max-cart-item="6" data-max-notice-item="6">
  <input type="hidden" data-setup="uiNotification" data-delete="/app/helper/delete/notification" data-path="/app/user/notifications" data-get="/app/helper/get/notification">
  <input type="hidden" data-setup="uiCart" data-delete="/app/helper/delete/cart" data-path="/index/checkout" data-get="/app/helper/get/cart">

  <section id="cwos-content">
    <div class="view-space">
      <br class="c-f">
        <div class="grid-11-tablet grid-8-laptop center-tablet">
          <div class="sec-div paddn -pall -p30">
            <ul id="thumb-list">
              <?php if (!empty($navs)) { foreach ($navs as $nav):
                if (!\in_array($nav->path, ["/{$nav_group}", "/{$nav_group}/", "/"])) {
                  echo "<li";
                    if (!empty($nav->onclick)) {
                      echo " onclick=\"{$nav->onclick}();\"";
                    } else {
                      echo " onclick=\"redirectTo('{$nav->path}', ", ((bool)$nav->newtab ? 'true' : 'false'),")\"";
                    }
                  echo ">";
                    echo "<span class='ls-icon'>{$nav->icon}</span> <br>";
                  echo "{$nav->title}</li>";
                  echo PHP_EOL;
                }
              endforeach; } ?>
            </ul>
          </div>
        </div>
      <br class="c-f">
    </div>
  </section>

  <script src="/app/cataliwos/plugin.cwapp/js/jquery.min.js"></script>
  <script src="/app/cataliwos/plugin.cwapp/js/functions.min.js"></script>
  <script src="/app/cataliwos/plugin.cwapp/js/class-object.min.js"></script>
  <script src="/app/cataliwos/dashui.cwapp/js/dashui.min.js"></script>
  <script src="/app/cataliwos/plugin.cwapp/js/theme.min.js"></script>
  <script src="/assets/js/base.min.js"></script>
  <script src="/app/cataliws/devman.cwapp/js/devman.min.js"></script>
  <script>
  </script>
</body>
</html>