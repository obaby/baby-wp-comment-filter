<?php
/**
 * Baby WP 评论强化拦截插件 - 测试文件
 * 
 * 这个文件用于测试插件功能，仅在开发环境中使用
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
 * 测试类
 */
class Baby_WP_Comment_Filter_Test {
    
    /**
     * 运行所有测试
     */
    public static function run_all_tests() {
        echo "<h2>Baby WP 评论强化拦截插件 - 功能测试</h2>\n";
        
        self::test_helper_functions();
        self::test_comment_filtering();
        self::test_settings();
        self::test_wordpress_integration();
        
        echo "<h3>测试完成！</h3>\n";
    }
    
    /**
     * 测试辅助函数
     */
    public static function test_helper_functions() {
        echo "<h3>1. 辅助函数测试</h3>\n";
        
        // 测试获取WordPress禁止关键词
        $wp_keywords = baby_wp_get_disallowed_comment_keys();
        echo "<p>WordPress禁止关键词数量: " . count($wp_keywords) . "</p>\n";
        
        if (!empty($wp_keywords)) {
            echo "<p>WordPress禁止关键词: " . implode(', ', $wp_keywords) . "</p>\n";
        }
        
        // 测试中文字符检测
        $test_content = "这是一条包含中文的评论";
        $has_chinese = baby_wp_has_chinese($test_content);
        echo "<p>中文字符检测测试: " . ($has_chinese ? '通过' : '失败') . "</p>\n";
        
        // 测试字数统计
        $length = baby_wp_get_comment_length($test_content);
        echo "<p>字数统计测试: {$length} 字</p>\n";
        
        // 测试禁止关键词检测
        $banned_words = ['spam', '垃圾', '广告'];
        $test_comment = "这是一条包含垃圾内容的评论";
        $has_banned = baby_wp_has_banned_word($test_comment, $banned_words);
        echo "<p>禁止关键词检测测试: " . ($has_banned ? '通过' : '失败') . "</p>\n";
        
        echo "<hr>\n";
    }
    
    /**
     * 测试评论过滤功能
     */
    public static function test_comment_filtering() {
        echo "<h3>2. 评论过滤功能测试</h3>\n";
        
        // 模拟评论数据
        $test_comments = array(
            array(
                'content' => '这是一条正常的评论',
                'expected' => '通过',
                'description' => '正常评论'
            ),
            array(
                'content' => 'spam',
                'expected' => '被过滤',
                'description' => '包含禁止关键词'
            ),
            array(
                'content' => 'this is english only',
                'expected' => '被过滤',
                'description' => '纯英文评论'
            ),
            array(
                'content' => str_repeat('a', 2000),
                'expected' => '被过滤',
                'description' => '超长评论'
            )
        );
        
        foreach ($test_comments as $test) {
            echo "<p><strong>{$test['description']}:</strong> {$test['content']} - 预期结果: {$test['expected']}</p>\n";
        }
        
        echo "<hr>\n";
    }
    
    /**
     * 测试设置功能
     */
    public static function test_settings() {
        echo "<h3>3. 设置功能测试</h3>\n";
        
        // 获取当前设置
        $options = baby_wp_get_option(null);
        echo "<p>当前插件设置:</p>\n";
        echo "<pre>" . print_r($options, true) . "</pre>\n";
        
        // 测试设置更新
        $test_value = 'test_value_' . time();
        $result = baby_wp_update_option('test_option', $test_value);
        echo "<p>设置更新测试: " . ($result ? '成功' : '失败') . "</p>\n";
        
        // 验证设置
        $retrieved_value = baby_wp_get_option('test_option');
        echo "<p>设置验证测试: " . ($retrieved_value === $test_value ? '成功' : '失败') . "</p>\n";
        
        // 清理测试设置
        delete_option('baby_wp_comment_filter_options');
        
        echo "<hr>\n";
    }
    
    /**
     * 测试WordPress集成
     */
    public static function test_wordpress_integration() {
        echo "<h3>4. WordPress集成测试</h3>\n";
        
        // 测试WordPress讨论设置
        $discussion_info = baby_wp_get_discussion_info();
        echo "<p>WordPress讨论设置信息:</p>\n";
        echo "<pre>" . print_r($discussion_info, true) . "</pre>\n";
        
        // 测试插件版本信息
        $version_info = baby_wp_get_version_info();
        echo "<p>插件版本信息:</p>\n";
        echo "<pre>" . print_r($version_info, true) . "</pre>\n";
        
        // 测试统计信息
        $stats = baby_wp_get_plugin_stats();
        echo "<p>插件统计信息:</p>\n";
        echo "<pre>" . print_r($stats, true) . "</pre>\n";
        
        echo "<hr>\n";
    }
    
    /**
     * 模拟评论过滤测试
     */
    public static function simulate_comment_filter($comment_content) {
        echo "<h3>5. 模拟评论过滤测试</h3>\n";
        
        // 获取插件设置
        $options = baby_wp_get_option(null, array());
        
        $comment_length = baby_wp_get_comment_length($comment_content);
        echo "<p>评论内容: {$comment_content}</p>\n";
        echo "<p>评论字数: {$comment_length}</p>\n";
        
        // 检查字数限制
        $min_length = isset($options['min_length']) ? intval($options['min_length']) : 0;
        $max_length = isset($options['max_length']) ? intval($options['max_length']) : 1800;
        
        if ($min_length > 0 && $comment_length < $min_length) {
            echo "<p style='color: red;'>❌ 评论太短，少于 {$min_length} 字</p>\n";
            return false;
        }
        
        if ($comment_length > $max_length) {
            echo "<p style='color: red;'>❌ 评论太长，超过 {$max_length} 字</p>\n";
            return false;
        }
        
        // 检查中文字符要求
        $require_chinese = isset($options['require_chinese']) ? $options['require_chinese'] : 1;
        if ($require_chinese && !baby_wp_has_chinese($comment_content)) {
            echo "<p style='color: red;'>❌ 评论必须包含中文字符</p>\n";
            return false;
        }
        
        // 检查禁止关键词
        $banned_words = array();
        
        // 添加自定义禁止关键词
        if (isset($options['custom_banned_words']) && !empty($options['custom_banned_words'])) {
            $custom_words = explode("\n", $options['custom_banned_words']);
            $custom_words = array_filter(array_map('trim', $custom_words));
            $banned_words = array_merge($banned_words, $custom_words);
        }
        
        // 添加WordPress设置的禁止关键词
        $use_wp_keywords = isset($options['use_wp_keywords']) ? $options['use_wp_keywords'] : 1;
        if ($use_wp_keywords) {
            $wp_keywords = baby_wp_get_disallowed_comment_keys();
            $banned_words = array_merge($banned_words, $wp_keywords);
        }
        
        if (!empty($banned_words) && baby_wp_has_banned_word($comment_content, $banned_words)) {
            echo "<p style='color: red;'>❌ 评论包含禁止关键词</p>\n";
            return false;
        }
        
        echo "<p style='color: green;'>✅ 评论通过所有检查</p>\n";
        return true;
    }
}

// 如果是在WordPress后台且用户有管理权限，显示测试页面
if (is_admin() && current_user_can('manage_options')) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'options-general.php',
            'Baby WP 插件测试',
            'Baby WP 测试',
            'manage_options',
            'baby-wp-test',
            function() {
                Baby_WP_Comment_Filter_Test::run_all_tests();
                
                // 添加交互式测试
                if (isset($_POST['test_comment'])) {
                    $test_comment = sanitize_textarea_field($_POST['test_comment']);
                    if (!empty($test_comment)) {
                        Baby_WP_Comment_Filter_Test::simulate_comment_filter($test_comment);
                    }
                }
                
                echo "<h3>6. 交互式测试</h3>\n";
                echo "<form method='post'>\n";
                echo "<p><label>测试评论内容:</label></p>\n";
                echo "<textarea name='test_comment' rows='3' cols='50' placeholder='输入要测试的评论内容...'></textarea><br><br>\n";
                echo "<input type='submit' value='测试评论' class='button button-primary'>\n";
                echo "</form>\n";
            }
        );
    });
}
