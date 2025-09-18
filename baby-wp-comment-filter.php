<?php
/**
 * Plugin Name: Baby WP 评论强化拦截插件
 * Plugin URI: https://h4ck.org.cn
 * Description: 一个强大的WordPress评论过滤插件，支持字数限制、中文检测、关键词过滤等功能
 * Version: 1.0.0
 * Author: obaby
 * Author URI: https://h4ck.org.cn
 * License: GPL v2 or later
 * Text Domain: baby-wp-comment-filter
 * Domain Path: /languages
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('BABY_WP_COMMENT_FILTER_VERSION', '1.0.0');
define('BABY_WP_COMMENT_FILTER_PLUGIN_FILE', __FILE__);
define('BABY_WP_COMMENT_FILTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BABY_WP_COMMENT_FILTER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * 主插件类
 */
class Baby_WP_Comment_Filter {
    
    /**
     * 插件实例
     */
    private static $instance = null;
    
    /**
     * 获取插件实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * 初始化钩子
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('preprocess_comment', array($this, 'filter_comment'));
    }
    
    /**
     * 加载依赖文件
     */
    private function load_dependencies() {
        require_once BABY_WP_COMMENT_FILTER_PLUGIN_DIR . 'includes/helper-functions.php';
    }
    
    /**
     * 初始化插件
     */
    public function init() {
        // 加载文本域
        load_plugin_textdomain('baby-wp-comment-filter', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_options_page(
            'Baby WP 评论设置',
            'Baby WP 评论',
            'manage_options',
            'baby-wp-comment-filter',
            array($this, 'admin_page')
        );
    }
    
    /**
     * 注册设置
     */
    public function register_settings() {
        register_setting('baby_wp_comment_filter_settings', 'baby_wp_comment_filter_options');
        
        add_settings_section(
            'baby_wp_comment_filter_main',
            '评论过滤设置',
            array($this, 'settings_section_callback'),
            'baby-wp-comment-filter'
        );
        
        // 最少字数设置
        add_settings_field(
            'min_length',
            '最少字数',
            array($this, 'min_length_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
        
        // 最多字数设置
        add_settings_field(
            'max_length',
            '最多字数',
            array($this, 'max_length_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
        
        // 是否要求中文
        add_settings_field(
            'require_chinese',
            '要求包含中文',
            array($this, 'require_chinese_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
        
        // 自定义禁止关键词
        add_settings_field(
            'custom_banned_words',
            '自定义禁止关键词',
            array($this, 'custom_banned_words_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
        
        // 使用WordPress设置的关键词
        add_settings_field(
            'use_wp_keywords',
            '使用WordPress讨论设置的关键词',
            array($this, 'use_wp_keywords_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
        
        // 错误消息设置
        add_settings_field(
            'error_messages',
            '错误消息设置',
            array($this, 'error_messages_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
        
        // 错误标题设置
        add_settings_field(
            'error_titles',
            '错误标题设置',
            array($this, 'error_titles_callback'),
            'baby-wp-comment-filter',
            'baby_wp_comment_filter_main'
        );
    }
    
    /**
     * 设置部分回调
     */
    public function settings_section_callback() {
        echo '<p>配置评论过滤规则，让评论更加规范和安全。</p>';
    }
    
    /**
     * 最少字数回调
     */
    public function min_length_callback() {
        $options = get_option('baby_wp_comment_filter_options');
        $value = isset($options['min_length']) ? $options['min_length'] : 0;
        echo '<input type="number" name="baby_wp_comment_filter_options[min_length]" value="' . esc_attr($value) . '" min="0" />';
        echo '<p class="description">设置评论的最少字数，0表示不限制</p>';
    }
    
    /**
     * 最多字数回调
     */
    public function max_length_callback() {
        $options = get_option('baby_wp_comment_filter_options');
        $value = isset($options['max_length']) ? $options['max_length'] : 1800;
        echo '<input type="number" name="baby_wp_comment_filter_options[max_length]" value="' . esc_attr($value) . '" min="1" />';
        echo '<p class="description">设置评论的最多字数</p>';
    }
    
    /**
     * 要求中文回调
     */
    public function require_chinese_callback() {
        $options = get_option('baby_wp_comment_filter_options');
        $value = isset($options['require_chinese']) ? $options['require_chinese'] : 1;
        echo '<input type="checkbox" name="baby_wp_comment_filter_options[require_chinese]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<p class="description">勾选后，评论必须包含中文字符</p>';
    }
    
    /**
     * 自定义禁止关键词回调
     */
    public function custom_banned_words_callback() {
        $options = get_option('baby_wp_comment_filter_options');
        $value = isset($options['custom_banned_words']) ? $options['custom_banned_words'] : '';
        echo '<textarea name="baby_wp_comment_filter_options[custom_banned_words]" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">每行一个关键词，支持正则表达式</p>';
    }
    
    /**
     * 使用WordPress关键词回调
     */
    public function use_wp_keywords_callback() {
        $options = get_option('baby_wp_comment_filter_options');
        $value = isset($options['use_wp_keywords']) ? $options['use_wp_keywords'] : 1;
        echo '<input type="checkbox" name="baby_wp_comment_filter_options[use_wp_keywords]" value="1" ' . checked(1, $value, false) . ' />';
        echo '<p class="description">勾选后，将使用WordPress后台"讨论设置"中的禁止关键词</p>';
        
        // 显示当前WordPress设置的关键词
        $wp_keywords = baby_wp_get_disallowed_comment_keys();
        if (!empty($wp_keywords)) {
            echo '<p><strong>当前WordPress禁止关键词：</strong></p>';
            echo '<ul>';
            foreach ($wp_keywords as $keyword) {
                echo '<li>' . esc_html($keyword) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p><em>当前没有设置WordPress禁止关键词</em></p>';
        }
    }
    
    /**
     * 错误消息回调
     */
    public function error_messages_callback() {
        // 使用辅助函数获取消息
        $messages = baby_wp_get_error_messages();
        $default_messages = baby_wp_get_default_error_messages();
        
        echo '<div class="error-messages-settings">';
        echo '<p class="description">自定义各种错误提示消息，让用户体验更加友好。可以使用占位符 <code>{min_length}</code> 和 <code>{max_length}</code>。</p>';
        
        echo '<table class="form-table widefat">';
        
        // 字数过多消息
        echo '<tr>';
        echo '<th scope="row"><label for="error_too_long">字数过多提示</label></th>';
        echo '<td>';
        echo '<textarea name="baby_wp_comment_filter_options[error_messages][too_long]" id="error_too_long" rows="2" cols="80" class="large-text">' . esc_textarea($messages['too_long']) . '</textarea>';
        echo '<p class="description">当评论超过最大字数限制时显示的消息</p>';
        echo '</td>';
        echo '</tr>';
        
        // 字数过少消息
        echo '<tr>';
        echo '<th scope="row"><label for="error_too_short">字数过少提示</label></th>';
        echo '<td>';
        echo '<textarea name="baby_wp_comment_filter_options[error_messages][too_short]" id="error_too_short" rows="2" cols="80" class="large-text">' . esc_textarea($messages['too_short']) . '</textarea>';
        echo '<p class="description">当评论少于最少字数要求时显示的消息</p>';
        echo '</td>';
        echo '</tr>';
        
        // 无中文字符消息
        echo '<tr>';
        echo '<th scope="row"><label for="error_no_chinese">无中文字符提示</label></th>';
        echo '<td>';
        echo '<textarea name="baby_wp_comment_filter_options[error_messages][no_chinese]" id="error_no_chinese" rows="2" cols="80" class="large-text">' . esc_textarea($messages['no_chinese']) . '</textarea>';
        echo '<p class="description">当评论不包含中文字符时显示的消息</p>';
        echo '</td>';
        echo '</tr>';
        
        // 包含禁止词消息
        echo '<tr>';
        echo '<th scope="row"><label for="error_banned_word">包含禁止词提示</label></th>';
        echo '<td>';
        echo '<textarea name="baby_wp_comment_filter_options[error_messages][banned_word]" id="error_banned_word" rows="2" cols="80" class="large-text">' . esc_textarea($messages['banned_word']) . '</textarea>';
        echo '<p class="description">当评论包含禁止关键词时显示的消息</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        // 添加重置按钮
        echo '<div style="margin-top: 15px;">';
        echo '<button type="button" id="reset-error-messages" class="button button-secondary">恢复默认消息</button>';
        echo '<p class="description">点击此按钮可以恢复所有错误消息为默认内容</p>';
        echo '</div>';
        
        // 添加JavaScript来处理重置功能
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#reset-error-messages").click(function() {
                if (confirm("确定要恢复所有错误消息为默认内容吗？这将覆盖当前的设置。")) {
                    var defaultMessages = ' . json_encode($default_messages) . ';
                    $.each(defaultMessages, function(key, value) {
                        $("#error_" + key).val(value);
                    });
                }
            });
            
            // 添加实时预览功能
            $("textarea[id^=\'error_\']").on("input", function() {
                var key = $(this).attr("id").replace("error_", "");
                var message = $(this).val();
                var preview = message.replace(/{min_length}/g, "10").replace(/{max_length}/g, "500");
                
                // 显示预览（可选）
                if (preview !== message) {
                    $(this).attr("title", "预览: " + preview);
                } else {
                    $(this).removeAttr("title");
                }
            });
        });
        </script>';
        
        echo '</div>';
    }
    
    /**
     * 错误标题回调
     */
    public function error_titles_callback() {
        // 使用辅助函数获取标题
        $titles = baby_wp_get_error_titles();
        $default_titles = baby_wp_get_default_error_titles();
        
        echo '<div class="error-titles-settings">';
        echo '<p class="description">自定义错误页面的标题，让错误提示更加个性化。</p>';
        
        echo '<table class="form-table widefat">';
        
        // 字数过多标题
        echo '<tr>';
        echo '<th scope="row"><label for="title_too_long">字数过多标题</label></th>';
        echo '<td>';
        echo '<input type="text" name="baby_wp_comment_filter_options[error_titles][too_long]" id="title_too_long" value="' . esc_attr($titles['too_long']) . '" class="regular-text" />';
        echo '<p class="description">当评论超过最大字数限制时显示的错误页面标题</p>';
        echo '</td>';
        echo '</tr>';
        
        // 字数过少标题
        echo '<tr>';
        echo '<th scope="row"><label for="title_too_short">字数过少标题</label></th>';
        echo '<td>';
        echo '<input type="text" name="baby_wp_comment_filter_options[error_titles][too_short]" id="title_too_short" value="' . esc_attr($titles['too_short']) . '" class="regular-text" />';
        echo '<p class="description">当评论少于最少字数要求时显示的错误页面标题</p>';
        echo '</td>';
        echo '</tr>';
        
        // 无中文字符标题
        echo '<tr>';
        echo '<th scope="row"><label for="title_no_chinese">无中文字符标题</label></th>';
        echo '<td>';
        echo '<input type="text" name="baby_wp_comment_filter_options[error_titles][no_chinese]" id="title_no_chinese" value="' . esc_attr($titles['no_chinese']) . '" class="regular-text" />';
        echo '<p class="description">当评论不包含中文字符时显示的错误页面标题</p>';
        echo '</td>';
        echo '</tr>';
        
        // 包含禁止词标题
        echo '<tr>';
        echo '<th scope="row"><label for="title_banned_word">包含禁止词标题</label></th>';
        echo '<td>';
        echo '<input type="text" name="baby_wp_comment_filter_options[error_titles][banned_word]" id="title_banned_word" value="' . esc_attr($titles['banned_word']) . '" class="regular-text" />';
        echo '<p class="description">当评论包含禁止关键词时显示的错误页面标题</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</table>';
        
        // 添加重置按钮
        echo '<div style="margin-top: 15px;">';
        echo '<button type="button" id="reset-error-titles" class="button button-secondary">恢复默认标题</button>';
        echo '<p class="description">点击此按钮可以恢复所有错误标题为默认内容</p>';
        echo '</div>';
        
        // 添加JavaScript来处理重置功能
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#reset-error-titles").click(function() {
                if (confirm("确定要恢复所有错误标题为默认内容吗？这将覆盖当前的设置。")) {
                    var defaultTitles = ' . json_encode($default_titles) . ';
                    $.each(defaultTitles, function(key, value) {
                        $("#title_" + key).val(value);
                    });
                }
            });
        });
        </script>';
        
        echo '</div>';
    }
    
    /**
     * 管理页面
     */
    public function admin_page() {
        ?>
        <style>
        .error-messages-settings,
        .error-titles-settings {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin: 10px 0;
        }
        .error-messages-settings .form-table th,
        .error-titles-settings .form-table th {
            width: 200px;
            vertical-align: top;
            padding-top: 15px;
        }
        .error-messages-settings .form-table td,
        .error-titles-settings .form-table td {
            padding: 10px 0;
        }
        .error-messages-settings textarea {
            font-family: monospace;
            font-size: 13px;
        }
        .error-titles-settings input[type="text"] {
            font-family: inherit;
            font-size: 14px;
        }
        .baby-wp-help-box {
            background: #e7f3ff;
            border-left: 4px solid #0073aa;
            padding: 15px;
            margin: 20px 0;
        }
        .baby-wp-help-box h3 {
            margin-top: 0;
            color: #0073aa;
        }
        .baby-wp-stats {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        </style>
        
        <div class="wrap">
            <h1>Baby WP 评论强化拦截插件设置</h1>
            <p>作者：<a href="https://h4ck.org.cn" target="_blank">obaby</a> | 版本：<?php echo BABY_WP_COMMENT_FILTER_VERSION; ?></p>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('baby_wp_comment_filter_settings');
                do_settings_sections('baby-wp-comment-filter');
                submit_button('保存设置', 'primary', 'submit', true, array('id' => 'save-settings'));
                ?>
            </form>
            
            <div class="baby-wp-help-box">
                <h3>💡 使用说明</h3>
                <ul>
                    <li><strong>最少字数：</strong>设置评论的最少字数要求，0表示不限制</li>
                    <li><strong>最多字数：</strong>设置评论的最多字数限制，防止过长评论</li>
                    <li><strong>要求包含中文：</strong>勾选后评论必须包含中文字符，过滤纯英文或数字评论</li>
                    <li><strong>自定义禁止关键词：</strong>每行一个关键词，支持正则表达式（如：<code>/spam/i</code>）</li>
                    <li><strong>使用WordPress关键词：</strong>同时使用WordPress后台"讨论设置"中的禁止关键词</li>
                    <li><strong>错误消息设置：</strong>自定义各种错误提示消息，可以使用占位符 <code>{min_length}</code> 和 <code>{max_length}</code></li>
                    <li><strong>错误标题设置：</strong>自定义错误页面的标题，让错误提示更加个性化</li>
                </ul>
            </div>
            
            <div class="baby-wp-stats">
                <h3>📊 插件统计信息</h3>
                <?php
                $stats = baby_wp_get_plugin_stats();
                echo '<p><strong>总过滤次数：</strong>' . intval($stats['total_filtered']) . '</p>';
                echo '<p><strong>字数限制过滤：</strong>' . intval($stats['filtered_by_length']) . '</p>';
                echo '<p><strong>中文检测过滤：</strong>' . intval($stats['filtered_by_chinese']) . '</p>';
                echo '<p><strong>关键词过滤：</strong>' . intval($stats['filtered_by_keywords']) . '</p>';
                echo '<p><strong>最后重置时间：</strong>' . date('Y-m-d H:i:s', $stats['last_reset']) . '</p>';
                ?>
                <p>
                    <a href="<?php echo admin_url('options-general.php?page=baby-wp-test'); ?>" class="button">运行功能测试</a>
                    <a href="<?php echo admin_url('options-general.php?page=baby-wp-install-check'); ?>" class="button">环境检查</a>
                </p>
            </div>
            
            <div class="card">
                <h3>🔧 高级功能</h3>
                <h4>正则表达式示例：</h4>
                <ul>
                    <li><code>/^\d+$/</code> - 匹配纯数字评论</li>
                    <li><code>/(.)\1{3,}/</code> - 匹配重复字符（如：aaaa）</li>
                    <li><code>/https?:\/\/[^\s]+/</code> - 匹配HTTP链接</li>
                    <li><code>/binance\.(com|info)/i</code> - 匹配特定域名（不区分大小写）</li>
                </ul>
                
                <h4>占位符使用：</h4>
                <ul>
                    <li><code>{min_length}</code> - 自动替换为设置的最少字数</li>
                    <li><code>{max_length}</code> - 自动替换为设置的最多字数</li>
                </ul>
                
                <h4>错误消息示例：</h4>
                <ul>
                    <li><strong>友好型：</strong>"评论内容需要{min_length}到{max_length}个字哦~"</li>
                    <li><strong>正式型：</strong>"评论长度不符合要求，请控制在{min_length}-{max_length}字之间。"</li>
                    <li><strong>幽默型：</strong>"额，你评论的内容太多啦，最多可以输入{max_length}个字，不要再评论区写论文啊！"</li>
                </ul>
                
                <h4>错误标题示例：</h4>
                <ul>
                    <li><strong>友好型：</strong>"评论出错了 - 网站名称"</li>
                    <li><strong>正式型：</strong>"评论提交失败 - 网站名称"</li>
                    <li><strong>幽默型：</strong>"姐姐我不开心啦！ - obaby@mars"</li>
                    <li><strong>简洁型：</strong>"评论错误"</li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * 评论过滤主函数
     */
    public function filter_comment($commentdata) {
        $options = get_option('baby_wp_comment_filter_options', array());
        
        // 如果是管理员，跳过检查
        if (is_admin()) {
            return $commentdata;
        }
        
        $comment_content = $commentdata['comment_content'];
        $comment_length = mb_strlen($comment_content);
        
        // 获取错误消息和标题
        $messages = baby_wp_get_error_messages();
        $titles = baby_wp_get_error_titles();
        
        // 检查最少字数
        $min_length = isset($options['min_length']) ? intval($options['min_length']) : 0;
        if ($min_length > 0 && $comment_length < $min_length) {
            $message = baby_wp_format_error_message($messages['too_short'], array('min_length' => $min_length));
            wp_die($message, $titles['too_short'], array('back_link' => true));
        }
        
        // 检查最多字数
        $max_length = isset($options['max_length']) ? intval($options['max_length']) : 1800;
        if ($comment_length > $max_length) {
            $message = baby_wp_format_error_message($messages['too_long'], array('max_length' => $max_length));
            wp_die($message, $titles['too_long'], array('back_link' => true));
        }
        
        // 检查是否要求中文
        $require_chinese = isset($options['require_chinese']) ? $options['require_chinese'] : 1;
        if ($require_chinese && preg_match('/[\x{4e00}-\x{9fa5}]/u', $comment_content) === 0) {
            wp_die($messages['no_chinese'], $titles['no_chinese'], array('back_link' => true));
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
        
        // 检查是否包含禁止关键词
        if (!empty($banned_words) && baby_wp_has_banned_word($comment_content, $banned_words)) {
            wp_die($messages['banned_word'], $titles['banned_word'], array('back_link' => true));
        }
        
        return $commentdata;
    }
    
}

// 注册插件激活和停用钩子（必须在类实例化之前）
register_activation_hook(__FILE__, function() {
    // 设置默认选项
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
});

register_deactivation_hook(__FILE__, function() {
    // 清理工作（如果需要）
    // 注意：通常不删除用户设置，让用户重新激活时保持配置
});

// 初始化插件
Baby_WP_Comment_Filter::get_instance();
