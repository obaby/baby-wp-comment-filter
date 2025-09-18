<?php
/**
 * Baby WP 评论强化拦截插件 - 安装脚本
 * 
 * 这个文件用于检查插件安装环境和依赖
 * 
 * @package Baby_WP_Comment_Filter
 * @author obaby
 * @link https://h4ck.org.cn
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 安装检查类
 */
class Baby_WP_Comment_Filter_Install {
    
    /**
     * 检查安装环境
     */
    public static function check_environment() {
        $errors = array();
        $warnings = array();
        
        // 检查WordPress版本
        global $wp_version;
        if (version_compare($wp_version, '3.0', '<')) {
            $errors[] = 'WordPress版本过低，需要3.0或更高版本';
        }
        
        // 检查PHP版本
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            $errors[] = 'PHP版本过低，需要5.6或更高版本';
        }
        
        // 检查必要的PHP扩展
        $required_extensions = array('mbstring', 'pcre');
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = "缺少必要的PHP扩展: {$ext}";
            }
        }
        
        // 检查文件权限
        $plugin_dir = plugin_dir_path(__FILE__);
        if (!is_writable($plugin_dir)) {
            $warnings[] = '插件目录不可写，可能影响某些功能';
        }
        
        // 检查数据库连接
        global $wpdb;
        if (!$wpdb->check_connection()) {
            $errors[] = '数据库连接失败';
        }
        
        return array(
            'errors' => $errors,
            'warnings' => $warnings,
            'wp_version' => $wp_version,
            'php_version' => PHP_VERSION
        );
    }
    
    /**
     * 显示安装检查结果
     */
    public static function display_check_results() {
        $results = self::check_environment();
        
        echo "<div class='wrap'>\n";
        echo "<h1>Baby WP 评论强化拦截插件 - 环境检查</h1>\n";
        
        echo "<h2>系统信息</h2>\n";
        echo "<table class='widefat'>\n";
        echo "<tr><td><strong>WordPress版本</strong></td><td>" . esc_html($results['wp_version']) . "</td></tr>\n";
        echo "<tr><td><strong>PHP版本</strong></td><td>" . esc_html($results['php_version']) . "</td></tr>\n";
        echo "<tr><td><strong>插件版本</strong></td><td>" . BABY_WP_COMMENT_FILTER_VERSION . "</td></tr>\n";
        echo "</table>\n";
        
        if (!empty($results['errors'])) {
            echo "<h2 style='color: red;'>错误</h2>\n";
            echo "<ul style='color: red;'>\n";
            foreach ($results['errors'] as $error) {
                echo "<li>" . esc_html($error) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        if (!empty($results['warnings'])) {
            echo "<h2 style='color: orange;'>警告</h2>\n";
            echo "<ul style='color: orange;'>\n";
            foreach ($results['warnings'] as $warning) {
                echo "<li>" . esc_html($warning) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        if (empty($results['errors']) && empty($results['warnings'])) {
            echo "<h2 style='color: green;'>✅ 环境检查通过</h2>\n";
            echo "<p>你的系统环境完全满足插件运行要求。</p>\n";
        }
        
        echo "<h2>下一步</h2>\n";
        echo "<p><a href='" . admin_url('options-general.php?page=baby-wp-comment-filter') . "' class='button button-primary'>进入插件设置</a></p>\n";
        echo "<p><a href='" . admin_url('options-general.php?page=baby-wp-test') . "' class='button'>运行功能测试</a></p>\n";
        
        echo "</div>\n";
    }
    
    /**
     * 创建必要的数据库表（如果需要）
     */
    public static function create_tables() {
        global $wpdb;
        
        // 这里可以添加创建自定义表的代码
        // 目前插件使用WordPress的options表，不需要创建新表
        
        return true;
    }
    
    /**
     * 清理安装数据
     */
    public static function cleanup() {
        // 删除插件选项
        delete_option('baby_wp_comment_filter_options');
        delete_option('baby_wp_comment_filter_stats');
        
        // 清理其他数据（如果有）
        
        return true;
    }
}

// 如果是在WordPress后台且用户有管理权限，显示安装检查页面
if (is_admin() && current_user_can('manage_options')) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'options-general.php',
            'Baby WP 环境检查',
            'Baby WP 检查',
            'manage_options',
            'baby-wp-install-check',
            array('Baby_WP_Comment_Filter_Install', 'display_check_results')
        );
    });
}
