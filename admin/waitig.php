<?php
/**
 * Created by PhpStorm.
 * User: lius
 * Date: 2017/2/20
 * Time: 19:56
 */
include("waitig-option.php");
function theme_check()
{
    global $themename;
    $url = get_bloginfo('url');
    if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
        return "Welcome to use $themename theme !";
    }
    $request = "http://www.waitig.com/themes/themecheck.php?url=$url&theme=$themename";
    @$fp = fopen($request, 'r');
    if (!$fp) {
        return "主题不可用，请检查授权是否已过期！";
    }
    $result = "";
    while (!feof($fp)) {
        $result .= fgets($fp, 1024);
    }
    fclose($fp);
    $resa = explode("|", $result);
    $canuse = $resa[0];
    $alter = $resa[1];
    $out = $resa[1];
    if (!$canuse) {
        echo "<br/>主题暂不可用，请确认主题授权是否过期！ <br/>更多信息请至<a href='https://www.waitig.com'> $themename 主题官网（waitig.com）</a>查看！";
        exit;
    }
    return $alter;
}

function waitig_add_admin_page()
{
    global $themename, $options, $notice;
    if ($_GET['page'] == basename(__FILE__)) {
        if ('update' == $_REQUEST['action']) {
            foreach ($options as $value) {
                if (isset($_REQUEST[$value['id']])) {
                    update_option($value['id'], $_REQUEST[$value['id']]);
                } else {
                    delete_option($value['id']);
                }
            }
            update_option('waitig_options_setup', true);
            header("Location: admin.php?page=waitig.php&update=true");
            die;
        } else if ('reset' == $_REQUEST['action']) {
            foreach ($options as $value) {
                delete_option($value['id']);
            }
            delete_option('waitig_options_setup');
            header('Location: themes.php?page=waitig.php&reset=true');
            die;
        }
    }
    add_menu_page($themename . "主题选项", $themename . "主题", 'edit_themes', basename(__FILE__), 'waitig_options_page', '', 26);
    add_submenu_page('waitig.php', $themename . '主题配置', '主题配置', 'edit_themes', basename(__FILE__), 'waitig_options_page');
}

add_action('admin_menu', 'waitig_add_admin_page');
function waitig_options_page()
{
    //wp_enqueue_media(); //在设置页面需要加载媒体中心 
    global $themename, $options, $notice;
    $i = 0;
    if ($_REQUEST['update']) echo '<div class="updated"><p><strong>设置已保存。</strong></p></div>';
    if ($_REQUEST['reset']) echo '<div class="updated"><p><strong>设置已重置。</strong></p></div>';
    ?>
    <div class="wrap d_wrap" id="waitig_theme_option_div">
        <?php $theme_check = theme_check($themename); ?>
        <h2><?php echo $themename; ?>主题选项</h2><input placeholder="筛选主题选项…" type="search" id="theme-options-search"/>
        <div class="d_formwrap">
            <div class="d_alter_w">
                <div class="d_alter">
                    <?php
                    if (!wp_is_mobile()) {
                        if ($theme_check) {
                            echo "<h3>主题信息</h3>";
                            echo $theme_check;
                        }
                        if (is_array($notice)) {
                            echo "<h3> 主题公告</h3>";
                            echo "<span>" . get_alert() . "</span><hr/>";
                            foreach ($notice as $value) {
                                echo "<h3>" . $value['name'] . "</h3>";
                                echo "<span>" . $value['desc'] . "</span><hr/>";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <form method="post">
                <h2 id="nav_tap_wrapper">
                    <?php
                    $panelIndex = 0;
                    foreach ($options as $value) {
                        if ($value['type'] == 'panelstart')
                            echo '<a href="#' . $value["id"] . '" class="nav-tab' . ($panelIndex == 0 ? ' nav-tab-active' : '') . '">' . $value['title'] . '</a>';
                        $panelIndex++;
                    }
                    ?>
                </h2>
                <?php
                $panelIndex = 0;
                $waitig_options_setup = get_option("waitig_options_setup");
                foreach ($options as $value) {
                    switch ($value['type']) {
                        case 'panelstart':
                            echo '<div class="panel" id="' . $value['id'] . '" ' . ($panelIndex == 0 ? ' style="display:block"' : '') . '><table class="form-table">';
                            $panelIndex++;
                            break;
                        case 'panelend':
                            echo '</table></div>';
                            break;
                        case 'subtitle':
                            echo '<tr><th colspan="2"><h3>' . $value['title'] . '</h3></th></tr>';
                            break;
                        case 'text':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <label>
                                        <input name="<?php echo $value['id']; ?>" class="regular-text"
                                               id="<?php echo $value['id']; ?>" type='text'
                                               value="<?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                   echo stripslashes(get_option($value['id']));
                                               } else {
                                                   echo $value['std'];
                                               } ?>"/>
                                        </br>
                                        <span class="description"><?php echo $value['desc']; ?></span>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'number':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <label>
                                        <input name="<?php echo $value['id']; ?>" class="small-text"
                                               id="<?php echo $value['id']; ?>" type="number"
                                               value="<?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                   echo get_option($value['id']);
                                               } else {
                                                   echo $value['std'];
                                               } ?>"/>
                                        <span class="description"><?php echo $value['desc']; ?></span>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'smalltext':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <label>
                                        <input name="<?php echo $value['id']; ?>" class="small-text"
                                               id="<?php echo $value['id']; ?>" type="text"
                                               value="<?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                   echo get_option($value['id']);
                                               } else {
                                                   echo $value['std'];
                                               } ?>"/>
                                        <span class="description"><?php echo $value['desc']; ?></span>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'password':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <label>
                                        <input name="<?php echo $value['id']; ?>" class="regular-text"
                                               id="<?php echo $value['id']; ?>" type="password"
                                               value="<?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                   echo get_option($value['id']);
                                               } else {
                                                   echo $value['std'];
                                               } ?>"/>
                                        <span class="description"><?php echo $value['desc']; ?></span>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'textarea':
                            ?>
                            <tr>
                                <th><?php echo $value['name']; ?></th>
                                <td>
                                    <p><label for="<?php echo $value['id']; ?>"><?php echo $value['desc']; ?></label>
                                    </p>
                                    <p><textarea name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>"
                                                 rows="5" cols="50"
                                                 class="large-text code"><?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                echo stripslashes(get_option($value['id']));
                                            } else {
                                                echo $value['std'];
                                            } ?></textarea></p>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'select':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <label>
                                        <select name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>">
                                            <?php foreach ($value['options'] as $option) : ?>
                                                <option value="<?php echo $option; ?>" <?php selected(get_option($value['id']), $option); ?>>
                                                    <?php echo $option; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="description"><?php echo $value['desc']; ?></span>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'radio':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <?php foreach ($value['options'] as $name => $option) : ?>
                                        <label>
                                            <input type="radio" name="<?php echo $value['id']; ?>"
                                                   id="<?php echo $value['id']; ?>"
                                                   value="<?php echo $option; ?>" <?php checked(get_option($value['id']), $option); ?>>
                                            <?php echo $name; ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <p><span class="description"><?php echo $value['desc']; ?></span></p>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'checkbox':
                            ?>
                            <tr>
                                <th><?php echo $value['name']; ?></th>
                                <td>
                                    <label>
                                        <input type='checkbox' name="<?php echo $value['id']; ?>"
                                               id="<?php echo $value['id']; ?>"
                                               value="1" <?php echo checked(get_option($value['id']), 1); ?> />
                                        <span><?php echo $value['desc']; ?></span>
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'checkboxs':
                            ?>
                            <tr>
                                <th><?php echo $value['name']; ?></th>
                                <td>
                                    <?php $checkboxsValue = get_option($value['id']);
                                    if (!is_array($checkboxsValue)) $checkboxsValue = array();
                                    foreach ($value['options'] as $id => $title) : ?>
                                        <label>
                                            <input type="checkbox" name="<?php echo $value['id']; ?>[]"
                                                   id="<?php echo $value['id']; ?>[]"
                                                   value="<?php echo $id; ?>" <?php checked(in_array($id, $checkboxsValue), true); ?>>
                                            <?php echo $title; ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <span class="description"><?php echo $value['desc']; ?></span>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'images':
                            ?>
                            <tr>
                                <th><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label></th>
                                <td>
                                    <label>
                                        <input name="<?php echo $value['id']; ?>" class="regular-text"
                                               id="<?php echo $value['id']; ?>" type='text' style="width: 80%;"
                                               value="<?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                   echo stripslashes(get_option($value['id']));
                                               } else {
                                                   echo $value['std'];
                                               } ?>"/>
                                        <input type="button" class="button button-primary"
                                               onclick="insertImage('<?php echo $value['id']; ?>')" value="上传图片"/>
                                        <br>
                                        <span class="description"><?php echo $value['desc']; ?></span>
                                        <br>
                                        <img id="img_<?php echo $value['id']; ?>" style="max-width:80%;"
                                             src="<?php if ($waitig_options_setup || get_option($value['id']) != '') {
                                                 echo stripslashes(get_option($value['id']));
                                             } else {
                                                 echo $value['std'];
                                             } ?>">
                                    </label>
                                </td>
                            </tr>
                            <?php
                            break;
                        case 'text_show':
                            echo '<tr class="textshow"><th>' . $value['name'] . '</th><td>' . $value['desc'] . '</td></tr>';
                            break;
                    }
                }
                wp_enqueue_media();//在设置页面需要加载媒体中心   
                ?>
                <p class="submit">
                    <input id="waitig_save_btn" name="submit" type="submit" class="button button-primary" value="保存选项"/>
                    <input type="hidden" name="action" value="update"/>
                </p>
            </form>
            <form method="post">
                <p>
                    <input name="reset" type="submit" class="button button-secondary" value="重置选项"
                           onclick="return confirm('你确定要重置选项吗？重置之后您的全部设置将被清空! ');"/>
                    <input type="hidden" name="action" value="reset"/>
                </p>
            </form>
        </div>
    </div>
    <style>
        .d_alter_w {
            display: inline;
            /*float: right;*/
            height: 100%;
            width: 29%;
            margin-top: 10px;
            position: absolute;
            top: 220px;
            right: 1%
        }

        .d_alter {
            padding: 20px 10px;
            border: 5px solid #CCC;
            min-height: 500px;
        }

        .catlist {
            border: 2px solid #FFB6C1;
            padding: 5px;
            margin-top: 12px;
            text-align: center;
            color: #000;
        }

        .d_formwrap {
            float: left;
            width: 100%
        }

        #nav_tap_wrapper {
            clear: both;
            font-size: 23px;
            font-weight: 400;
            line-height: 29px;
            padding: 9px 0px 4px;
        }

        .textshow {
            color: #999;
        }

        .d_formwrap tr {
            border: 2px /*solid #ccc*/;
            padding: 20px
        }

        .panel {
            display: none;
            width: 69%
        }

        .panel input[type="text"] {
            width: 99%;
        }

        .panel input[type="text"].small-text {
            width: auto;
            max-width: 80px;
        }

        .panel h3 {
            margin: 0;
            font-size: 1.2em
        }

        #panel_update ul {
            list-style-type: disc
        }

        .nav-tab-wrapper {
            clear: both
        }

        .nav-tab {
            position: relative
        }

        .nav-tab i:before {
            position: absolute;
            top: -10px;
            right: -8px;
            display: inline-block;
            padding: 2px;
            border-radius: 50%;
            background: #30e14a;
            color: #ff0323;
            content: "\f463";
            vertical-align: text-bottom;
            font: 400 18px/1 dashicons;
            speak: none
        }

        #theme-options-search {
            display: none;
            float: right;
            margin-top: -34px;
            width: 280px;
            font-weight: 300;
            font-size: 16px;
            line-height: 1.5
        }

        .updated + #theme-options-search {
            margin-top: -91px
        }

        .wrap.searching .nav-tab-wrapper a, .wrap.searching .panel tr, #attrselector {
            display: none
        }

        .wrap.searching .panel {
            display: block !important
        }

        #attrselector[attrselector*=ok] {
            display: block
        }
    </style>
    <style id="theme-options-filter"></style>
    <div id="attrselector" attrselector="ok"></div>
    <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/jquery.min.js"></script>
    <script>
        function insertImage(value_id) {
            var ashu_upload_frame;
            event.preventDefault();
            if (ashu_upload_frame) {
                ashu_upload_frame.open();
                return;
            }
            ashu_upload_frame = wp.media({
                title: '插入图片',
                button: {
                    text: '插入'
                },
                multiple: false
            });
            ashu_upload_frame.on('select', function () {
                attachment = ashu_upload_frame.state().get('selection').first().toJSON();
                $('#' + value_id).val(attachment.url);
                $('#img_' + value_id).attr("src", attachment.url);
            });
            ashu_upload_frame.open();
        }

        jQuery(function ($) {
            /*var netUrl = '';
            var netHost = '';
            var themeName = '';
            if(netHost==='localhost'||netHost==='127.0.0.1'){
                $("#theme_info").html("<h3>主题信息</h3>欢迎使用"+themeName+"主题！");
            }
            else{
                $.ajax({
                    url: "http://www.waitig.com/themes/themecheck.php?url="+netUrl+"&theme="+themeName,
                    type: 'get',
                    success: function (r) {
                        var themeStatus = r.split("|")[0];
                        var themeInfo = r.split("|")[1];
                        if(themeStatus==='0'){
                            $("#waitig_theme_option_div").html("<br/>主题暂不可用，请确认主题授权是否过期！ <br/>更多信息请至<a href='https://www.waitig.com'> "+themeName+" 主题官网（waitig.com）</a>查看！");
                        }
                        else if(themeStatus==='1'){
                            $("#theme_info").html("<h3>主题信息</h3>"+themeInfo);
                        }
                    },
                    error: function (msg) {
                        $("#theme_info").html("<h3>主题信息</h3>欢迎使用"+themeName+"主题！");
                    }
                });
            }*/
            $(".nav-tab").click(function () {
                $(this).addClass("nav-tab-active").siblings().removeClass("nav-tab-active");
                $(".panel").hide();
                $($(this).attr("href")).show();
                return false;
            });
            var themeOptionsFilter = $("#theme-options-filter");
            themeOptionsFilter.text("ok");
            if ($("#attrselector").is(":visible") && themeOptionsFilter.text() !== "") {
                $(".panel tr").each(function (el) {
                    $(this).attr("data-searchtext", $(this).text().replace(/\r|\n/g, '').replace(/ +/g, ' ').toLowerCase());
                });
                var wrap = $(".wrap");
                $("#theme-options-search").show().on("input propertychange", function () {
                    var text = $(this).val().replace(/^ +| +$/, "").toLowerCase();
                    if (text !== "") {
                        wrap.addClass("searching");
                        themeOptionsFilter.text(".wrap.searching .panel tr[data-searchtext*='" + text + "']{display:block}");
                    } else {
                        wrap.removeClass("searching");
                        themeOptionsFilter.text("");
                    }
                    ;
                });
            }
            ;
        });
    </script>
    <?php

}

//启用主题后自动跳转至选项页面
global $pagenow;
if (is_admin() && isset($_GET['activated']) && $pagenow == 'themes.php') {
    wp_redirect(admin_url('themes.php?page=waitig.php'));
    exit;
}
function waitig_enqueue_pointer_script_style($hook_suffix)
{
    $enqueue_pointer_script_style = false;
    $dismissed_pointers = explode(',', get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true));
    if (!in_array('waitig_options_pointer', $dismissed_pointers)) {
        $enqueue_pointer_script_style = true;
        add_action('admin_print_footer_scripts', 'waitig_pointer_print_scripts');
    }
    if ($enqueue_pointer_script_style) {
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');
    }
}

add_action('admin_enqueue_scripts', 'waitig_enqueue_pointer_script_style');
function waitig_pointer_print_scripts()
{
    ?>
    <script>
        jQuery(document).ready(function ($) {
            var $menuAppearance = $("#menu-appearance");
            $menuAppearance.pointer({
                content: '<h3>恭喜，主题安装成功！</h3><p>该主题支持选项，请访问<a href="themes.php?page=waitig.php">主题选项</a>页面进行配置。</p>',
                position: {
                    edge: "left",
                    align: "center"
                },
                close: function () {
                    $.post(ajaxurl, {
                        pointer: "waitig_options_pointer",
                        action: "dismiss-wp-pointer"
                    });
                }
            }).pointer("open").pointer("widget").find("a").eq(0).click(function () {
                var href = $(this).attr("href");
                $menuAppearance.pointer("close");
                setTimeout(function () {
                    location.href = href;
                }, 700);
                return false;
            });
            $(window).on("resize scroll", function () {
                $menuAppearance.pointer("reposition");
            });
            $("#collapse-menu").click(function () {
                $menuAppearance.pointer("reposition");
            });

        });
    <?php
}

?>