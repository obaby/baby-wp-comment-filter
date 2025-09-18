<?php
/**
 * Baby WP 评论强化拦截插件 - 错误消息演示
 * 
 * 这个文件演示了各种错误消息配置的效果
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
 * 错误消息演示类
 */
class Baby_WP_Error_Messages_Demo {
    
    /**
     * 显示错误消息演示
     */
    public static function display_demo() {
        echo "<div class='wrap'>\n";
        echo "<h1>Baby WP 评论强化拦截插件 - 错误消息演示</h1>\n";
        
        // 获取当前设置
        $messages = baby_wp_get_error_messages();
        $default_messages = baby_wp_get_default_error_messages();
        $titles = baby_wp_get_error_titles();
        $default_titles = baby_wp_get_default_error_titles();
        
        echo "<h2>当前错误消息设置</h2>\n";
        self::display_message_comparison($messages, $default_messages);
        
        echo "<h2>当前错误标题设置</h2>\n";
        self::display_title_comparison($titles, $default_titles);
        
        echo "<h2>消息预览效果</h2>\n";
        self::display_message_previews($messages);
        
        echo "<h2>不同风格的消息示例</h2>\n";
        self::display_style_examples();
        
        echo "<h2>不同风格的标题示例</h2>\n";
        self::display_title_style_examples();
        
        echo "<h2>占位符使用示例</h2>\n";
        self::display_placeholder_examples();
        
        echo "</div>\n";
    }
    
    /**
     * 显示消息对比
     */
    private static function display_message_comparison($current, $default) {
        echo "<table class='widefat'>\n";
        echo "<thead><tr><th>消息类型</th><th>当前设置</th><th>默认设置</th><th>状态</th></tr></thead>\n";
        echo "<tbody>\n";
        
        $message_types = array(
            'too_long' => '字数过多',
            'too_short' => '字数过少',
            'no_chinese' => '无中文字符',
            'banned_word' => '包含禁止词'
        );
        
        foreach ($message_types as $key => $label) {
            $current_msg = isset($current[$key]) ? $current[$key] : '';
            $default_msg = isset($default[$key]) ? $default[$key] : '';
            $is_default = ($current_msg === $default_msg);
            
            echo "<tr>\n";
            echo "<td><strong>{$label}</strong></td>\n";
            echo "<td>" . esc_html($current_msg) . "</td>\n";
            echo "<td>" . esc_html($default_msg) . "</td>\n";
            echo "<td>" . ($is_default ? '<span style="color: green;">默认</span>' : '<span style="color: blue;">自定义</span>') . "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</tbody></table>\n";
    }
    
    /**
     * 显示标题对比
     */
    private static function display_title_comparison($current, $default) {
        echo "<table class='widefat'>\n";
        echo "<thead><tr><th>标题类型</th><th>当前设置</th><th>默认设置</th><th>状态</th></tr></thead>\n";
        echo "<tbody>\n";
        
        $title_types = array(
            'too_long' => '字数过多',
            'too_short' => '字数过少',
            'no_chinese' => '无中文字符',
            'banned_word' => '包含禁止词'
        );
        
        foreach ($title_types as $key => $label) {
            $current_title = isset($current[$key]) ? $current[$key] : '';
            $default_title = isset($default[$key]) ? $default[$key] : '';
            $is_default = ($current_title === $default_title);
            
            echo "<tr>\n";
            echo "<td><strong>{$label}</strong></td>\n";
            echo "<td>" . esc_html($current_title) . "</td>\n";
            echo "<td>" . esc_html($default_title) . "</td>\n";
            echo "<td>" . ($is_default ? '<span style="color: green;">默认</span>' : '<span style="color: blue;">自定义</span>') . "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</tbody></table>\n";
    }
    
    /**
     * 显示消息预览效果
     */
    private static function display_message_previews($messages) {
        $placeholders = array(
            'min_length' => 10,
            'max_length' => 500
        );
        
        echo "<div class='message-previews'>\n";
        
        foreach ($messages as $key => $message) {
            $preview = baby_wp_format_error_message($message, $placeholders);
            $label = self::get_message_label($key);
            
            echo "<div class='message-preview' style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 4px;'>\n";
            echo "<h4>{$label}</h4>\n";
            echo "<p><strong>原始消息：</strong></p>\n";
            echo "<code style='background: #f0f0f0; padding: 5px; display: block;'>" . esc_html($message) . "</code>\n";
            echo "<p><strong>预览效果：</strong></p>\n";
            echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 4px;'>" . esc_html($preview) . "</div>\n";
            echo "</div>\n";
        }
        
        echo "</div>\n";
    }
    
    /**
     * 显示不同风格的消息示例
     */
    private static function display_style_examples() {
        $styles = array(
            '友好型' => array(
                'too_long' => '亲，你的评论有点长哦，最多{max_length}个字就够了~',
                'too_short' => '评论需要至少{min_length}个字呢，再写一点吧~',
                'no_chinese' => '请用中文评论哦，这样大家都能看懂~',
                'banned_word' => '评论内容不太合适呢，请重新编辑一下~'
            ),
            '正式型' => array(
                'too_long' => '评论长度超出限制，请控制在{max_length}字以内。',
                'too_short' => '评论内容过短，请至少输入{min_length}字。',
                'no_chinese' => '评论必须包含中文字符。',
                'banned_word' => '评论包含不当内容，请修改后重新提交。'
            ),
            '简洁型' => array(
                'too_long' => '评论过长，最多{max_length}字。',
                'too_short' => '评论过短，至少{min_length}字。',
                'no_chinese' => '请使用中文评论。',
                'banned_word' => '评论内容不当。'
            ),
            '幽默型' => array(
                'too_long' => '哇，你这是要写小说吗？最多{max_length}字就够了！',
                'too_short' => '太短了太短了，至少{min_length}字才能表达你的想法！',
                'no_chinese' => '说中文啦，英文我看不懂~',
                'banned_word' => '这个内容不太和谐哦，换个说法吧~'
            )
        );
        
        foreach ($styles as $style_name => $style_messages) {
            echo "<h3>{$style_name}</h3>\n";
            echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 10px 0;'>\n";
            
            foreach ($style_messages as $key => $message) {
                $label = self::get_message_label($key);
                $preview = baby_wp_format_error_message($message, array('min_length' => 10, 'max_length' => 500));
                
                echo "<p><strong>{$label}：</strong></p>\n";
                echo "<p style='margin-left: 20px; font-style: italic;'>" . esc_html($preview) . "</p>\n";
            }
            
            echo "</div>\n";
        }
    }
    
    /**
     * 显示不同风格的标题示例
     */
    private static function display_title_style_examples() {
        $title_styles = array(
            '友好型' => array(
                'too_long' => '评论出错了 - 网站名称',
                'too_short' => '评论出错了 - 网站名称',
                'no_chinese' => '评论出错了 - 网站名称',
                'banned_word' => '评论出错了 - 网站名称'
            ),
            '正式型' => array(
                'too_long' => '评论提交失败 - 网站名称',
                'too_short' => '评论提交失败 - 网站名称',
                'no_chinese' => '评论提交失败 - 网站名称',
                'banned_word' => '评论提交失败 - 网站名称'
            ),
            '简洁型' => array(
                'too_long' => '评论错误',
                'too_short' => '评论错误',
                'no_chinese' => '评论错误',
                'banned_word' => '评论错误'
            ),
            '幽默型' => array(
                'too_long' => '姐姐我不开心啦！ - obaby@mars',
                'too_short' => '姐姐我不开心啦！ - obaby@mars',
                'no_chinese' => '姐姐我不开心啦！ - obaby@mars',
                'banned_word' => '姐姐我不开心啦！ - obaby@mars'
            )
        );
        
        foreach ($title_styles as $style_name => $style_titles) {
            echo "<h3>{$style_name}</h3>\n";
            echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 4px; margin: 10px 0;'>\n";
            
            foreach ($style_titles as $key => $title) {
                $label = self::get_message_label($key);
                
                echo "<p><strong>{$label}：</strong></p>\n";
                echo "<p style='margin-left: 20px; font-style: italic; color: #666;'>" . esc_html($title) . "</p>\n";
            }
            
            echo "</div>\n";
        }
    }
    
    /**
     * 显示占位符使用示例
     */
    private static function display_placeholder_examples() {
        $examples = array(
            '基本使用' => '评论长度必须在{min_length}到{max_length}字之间。',
            '只使用最大长度' => '评论不能超过{max_length}字。',
            '只使用最小长度' => '评论至少需要{min_length}字。',
            '组合使用' => '请写{min_length}到{max_length}字的评论，不要太短也不要太长哦~',
            '不使用占位符' => '评论长度不符合要求，请重新编辑。'
        );
        
        echo "<table class='widefat'>\n";
        echo "<thead><tr><th>示例类型</th><th>原始消息</th><th>预览效果</th></tr></thead>\n";
        echo "<tbody>\n";
        
        foreach ($examples as $type => $message) {
            $preview = baby_wp_format_error_message($message, array('min_length' => 10, 'max_length' => 500));
            
            echo "<tr>\n";
            echo "<td><strong>{$type}</strong></td>\n";
            echo "<td><code>" . esc_html($message) . "</code></td>\n";
            echo "<td>" . esc_html($preview) . "</td>\n";
            echo "</tr>\n";
        }
        
        echo "</tbody></table>\n";
    }
    
    /**
     * 获取消息标签
     */
    private static function get_message_label($key) {
        $labels = array(
            'too_long' => '字数过多提示',
            'too_short' => '字数过少提示',
            'no_chinese' => '无中文字符提示',
            'banned_word' => '包含禁止词提示'
        );
        
        return isset($labels[$key]) ? $labels[$key] : $key;
    }
}

// 如果是在WordPress后台且用户有管理权限，显示演示页面
if (is_admin() && current_user_can('manage_options')) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'options-general.php',
            'Baby WP 错误消息演示',
            'Baby WP 演示',
            'manage_options',
            'baby-wp-demo',
            array('Baby_WP_Error_Messages_Demo', 'display_demo')
        );
    });
}
