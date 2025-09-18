<?php
/**
 * Baby WP 评论强化拦截插件 - 激活测试
 * 
 * 这个文件用于测试插件激活功能
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
 * 激活测试类
 */
class Baby_WP_Activation_Test {
    
    /**
     * 测试插件激活功能
     */
    public static function test_activation() {
        echo "<div class='wrap'>\n";
        echo "<h1>Baby WP 评论强化拦截插件 - 激活测试</h1>\n";
        
        // 检查插件选项是否存在
        $options = get_option('baby_wp_comment_filter_options');
        
        if ($options === false) {
            echo "<div class='notice notice-error'><p>❌ 插件选项不存在，激活可能失败</p></div>\n";
        } else {
            echo "<div class='notice notice-success'><p>✅ 插件选项存在，激活成功</p></div>\n";
        }
        
        // 显示当前选项
        echo "<h2>当前插件选项</h2>\n";
        echo "<pre>" . print_r($options, true) . "</pre>\n";
        
        // 检查默认值
        echo "<h2>默认值检查</h2>\n";
        $default_checks = array(
            'min_length' => isset($options['min_length']) ? $options['min_length'] : '未设置',
            'max_length' => isset($options['max_length']) ? $options['max_length'] : '未设置',
            'require_chinese' => isset($options['require_chinese']) ? $options['require_chinese'] : '未设置',
            'custom_banned_words' => isset($options['custom_banned_words']) ? $options['custom_banned_words'] : '未设置',
            'use_wp_keywords' => isset($options['use_wp_keywords']) ? $options['use_wp_keywords'] : '未设置',
            'error_messages' => isset($options['error_messages']) ? '已设置' : '未设置',
            'error_titles' => isset($options['error_titles']) ? '已设置' : '未设置'
        );
        
        echo "<table class='widefat'>\n";
        echo "<thead><tr><th>选项</th><th>值</th><th>状态</th></tr></thead>\n";
        echo "<tbody>\n";
        
        foreach ($default_checks as $key => $value) {
            $status = ($value !== '未设置') ? '<span style="color: green;">✅ 正常</span>' : '<span style="color: red;">❌ 异常</span>';
            echo "<tr><td><strong>{$key}</strong></td><td>" . esc_html(print_r($value, true)) . "</td><td>{$status}</td></tr>\n";
        }
        
        echo "</tbody></table>\n";
        
        // 测试重新激活
        echo "<h2>重新激活测试</h2>\n";
        echo "<p>点击下面的按钮可以模拟重新激活插件：</p>\n";
        echo "<form method='post'>\n";
        echo "<input type='hidden' name='action' value='reactivate_plugin'>\n";
        echo "<input type='submit' value='重新激活插件' class='button button-primary'>\n";
        echo "</form>\n";
        
        // 处理重新激活请求
        if (isset($_POST['action']) && $_POST['action'] === 'reactivate_plugin') {
            self::simulate_reactivation();
        }
        
        echo "</div>\n";
    }
    
    /**
     * 模拟重新激活
     */
    private static function simulate_reactivation() {
        // 删除现有选项
        delete_option('baby_wp_comment_filter_options');
        
        // 重新设置默认选项
        $default_options = array(
            'min_length' => 0,
            'max_length' => 1800,
            'require_chinese' => 1,
            'custom_banned_words' => '',
            'use_wp_keywords' => 1,
            'error_messages' => array(
                'too_long' => '额，你评论的内容太多啦，最多可以输入{max_length}个字，不要再评论区写论文啊！',
                'too_short' => '评论内容太短了，至少需要{min_length}个字哦！',
                'no_chinese' => '不要乱发哦，让姐姐我不开心就不好了嘛！(评论禁止纯英文字符、数字内容)',
                'banned_word' => '不要乱发哦，让姐姐我不开心就不好了嘛！(你tmd别发广告了ok？你是傻逼吗？！)'
            ),
            'error_titles' => array(
                'too_long' => '宝贝，出错了哦 - obaby@mars',
                'too_short' => '宝贝，出错了哦 - obaby@mars',
                'no_chinese' => '姐姐我不开心啦！ - obaby@mars',
                'banned_word' => '姐姐我不开心啦！ - obaby@mars'
            )
        );
        
        add_option('baby_wp_comment_filter_options', $default_options);
        
        echo "<div class='notice notice-success'><p>✅ 插件重新激活成功！</p></div>\n";
    }
}

// 如果是在WordPress后台且用户有管理权限，显示激活测试页面
if (is_admin() && current_user_can('manage_options')) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'options-general.php',
            'Baby WP 激活测试',
            'Baby WP 激活测试',
            'manage_options',
            'baby-wp-activation-test',
            array('Baby_WP_Activation_Test', 'test_activation')
        );
    });
}
