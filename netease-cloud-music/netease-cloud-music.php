<?php
/**
 * Plugin Name: 本地音乐Ultimate
 * Description: 完美适配知更鸟主题，网易云音乐风格本地音乐插件，后台专辑/歌曲管理，前端高颜值播放器，纯本地，无全局污染。
 * Version: 1.2.0
 * Author: Mruei
 */

if (!defined('ABSPATH')) exit;

define('NCMU_DIR', plugin_dir_path(__FILE__));
define('NCMU_URL', plugin_dir_url(__FILE__));

require_once NCMU_DIR . 'admin/settings-page.php';
require_once NCMU_DIR . 'includes/ajax.php';

// 内容渲染
add_filter('the_content', function($content) {
    $bind_page = get_option('ncmu_bind_page', '');
    if ($bind_page && is_page($bind_page)) {
        ob_start();
        echo '<div class="ncmusic-root">';
        $id = $_GET['local_album_id'] ?? null;
        if ($id !== null) {
            include NCMU_DIR . 'templates/album-local.php';
        } else {
            include NCMU_DIR . 'templates/music.php';
        }
        echo '</div>';
        return ob_get_clean();
    }
    return $content;
}, 99);

// 模板兼容
add_filter('template_include', function($template) {
    $bind_page = get_option('ncmu_bind_page', '');
    if ($bind_page && is_page($bind_page)) return $template;
    if (preg_match('#/music/?$#', $_SERVER['REQUEST_URI']) || isset($_GET['music']) || isset($_GET['local_album_id'])) {
        $id = $_GET['local_album_id'] ?? null;
        if ($id !== null) {
            $new_template = NCMU_DIR . 'templates/album-local.php';
            if (file_exists($new_template)) return $new_template;
        }
        $new_template = NCMU_DIR . 'templates/music.php';
        if (file_exists($new_template)) return $new_template;
    }
    return $template;
});

// 插件静态资源
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('ncmu-style', NCMU_URL.'assets/style.css', [], '1.2.0');
    wp_enqueue_script('ncmu-js', NCMU_URL.'assets/player.js', ['jquery'], '1.2.0', true);
    wp_localize_script('ncmu-js', 'NCMU_AJAX', [
        'ajaxurl'=>admin_url('admin-ajax.php'),
        'nonce'=>wp_create_nonce('ncmu_ajax'),
        'loggedin'=>is_user_logged_in(),
        'user_id'=>get_current_user_id(),
    ]);
});

// 插件设置按钮
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $url = admin_url('options-general.php?page=ncmu-settings');
    $settings_link = '<a href="' . esc_url($url) . '">设置</a>';
    array_unshift($links, $settings_link);
    return $links;
});