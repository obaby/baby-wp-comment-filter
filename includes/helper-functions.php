<?php
/**
 * Baby WP 评论强化拦截插件 - 辅助函数
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
 * 获取WordPress禁止使用的评论关键字列表
 * 
 * @return array 禁止关键字的数组
 */
function baby_wp_get_disallowed_comment_keys() {
    // WordPress 5.5+ 使用 moderation_keys，旧版本使用 disallowed_keys
    $disallowed_keys = get_option('moderation_keys', get_option('disallowed_keys', ''));
    
    if (empty($disallowed_keys)) {
        return [];
    }
    
    // 将字符串按换行符分割并清理
    $keywords_array = explode("\n", $disallowed_keys);
    $keywords_array = array_filter(array_map('trim', $keywords_array));
    
    return $keywords_array;
}

/**
 * 检查评论内容是否包含禁止关键词
 * 
 * @param string $comment_content 评论内容
 * @param array $banned_words 禁止关键词数组
 * @return bool 是否包含禁止关键词
 */
function baby_wp_has_banned_word($comment_content, $banned_words) {
    if (empty($banned_words) || empty($comment_content)) {
        return false;
    }
    
    $comment_lower = strtolower($comment_content);
    
    foreach ($banned_words as $word) {
        $word = trim($word);
        if (empty($word)) {
            continue;
        }
        
        // 检查是否为正则表达式（以/开头和结尾）
        if (preg_match('/^\/.*\/[imsxADSUXu]*$/', $word)) {
            // 使用正则表达式匹配
            if (preg_match($word, $comment_content)) {
                return true;
            }
        } else {
            // 普通字符串匹配（不区分大小写）
            if (strpos($comment_lower, strtolower($word)) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * 检查评论内容是否包含中文字符
 * 
 * @param string $content 评论内容
 * @return bool 是否包含中文字符
 */
function baby_wp_has_chinese($content) {
    return preg_match('/[\x{4e00}-\x{9fa5}]/u', $content) > 0;
}

/**
 * 获取评论字数（支持多字节字符）
 * 
 * @param string $content 评论内容
 * @return int 字数
 */
function baby_wp_get_comment_length($content) {
    return mb_strlen($content, 'UTF-8');
}

/**
 * 格式化错误消息，替换占位符
 * 
 * @param string $message 原始消息
 * @param array $placeholders 占位符数组
 * @return string 格式化后的消息
 */
function baby_wp_format_error_message($message, $placeholders = array()) {
    foreach ($placeholders as $key => $value) {
        $message = str_replace('{' . $key . '}', $value, $message);
    }
    return $message;
}

/**
 * 记录评论过滤日志
 * 
 * @param string $comment_content 评论内容
 * @param string $reason 过滤原因
 * @param string $keyword 触发的关键词（如果有）
 */
function baby_wp_log_comment_filter($comment_content, $reason, $keyword = '') {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $log_message = sprintf(
        '[Baby WP Comment Filter] 评论被过滤 - 原因: %s, 内容: %s, 关键词: %s, IP: %s, 时间: %s',
        $reason,
        substr($comment_content, 0, 100) . (strlen($comment_content) > 100 ? '...' : ''),
        $keyword,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        current_time('Y-m-d H:i:s')
    );
    
    error_log($log_message);
}

/**
 * 获取插件设置选项
 * 
 * @param string $key 选项键名
 * @param mixed $default 默认值
 * @return mixed 选项值
 */
function baby_wp_get_option($key, $default = null) {
    $options = get_option('baby_wp_comment_filter_options', array());
    
    if (is_null($key)) {
        return $options;
    }
    
    return isset($options[$key]) ? $options[$key] : $default;
}

/**
 * 更新插件设置选项
 * 
 * @param string $key 选项键名
 * @param mixed $value 选项值
 * @return bool 是否更新成功
 */
function baby_wp_update_option($key, $value) {
    $options = get_option('baby_wp_comment_filter_options', array());
    $options[$key] = $value;
    return update_option('baby_wp_comment_filter_options', $options);
}

/**
 * 检查是否为管理员用户
 * 
 * @return bool 是否为管理员
 */
function baby_wp_is_admin_user() {
    return current_user_can('manage_options') || is_admin();
}

/**
 * 获取当前用户的IP地址
 * 
 * @return string IP地址
 */
function baby_wp_get_user_ip() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * 清理和验证关键词列表
 * 
 * @param string $keywords_string 关键词字符串（换行分隔）
 * @return array 清理后的关键词数组
 */
function baby_wp_clean_keywords($keywords_string) {
    if (empty($keywords_string)) {
        return array();
    }
    
    $keywords = explode("\n", $keywords_string);
    $keywords = array_filter(array_map('trim', $keywords));
    
    // 移除空行和重复项
    $keywords = array_unique($keywords);
    
    return $keywords;
}

/**
 * 验证关键词格式
 * 
 * @param string $keyword 关键词
 * @return bool 是否为有效格式
 */
function baby_wp_validate_keyword($keyword) {
    if (empty($keyword)) {
        return false;
    }
    
    // 检查是否为正则表达式
    if (preg_match('/^\/.*\/[imsxADSUXu]*$/', $keyword)) {
        // 验证正则表达式是否有效
        return @preg_match($keyword, '') !== false;
    }
    
    // 普通关键词，检查长度
    return strlen($keyword) <= 255;
}

/**
 * 获取插件统计信息
 * 
 * @return array 统计信息
 */
function baby_wp_get_plugin_stats() {
    $stats = get_option('baby_wp_comment_filter_stats', array(
        'total_filtered' => 0,
        'filtered_by_length' => 0,
        'filtered_by_chinese' => 0,
        'filtered_by_keywords' => 0,
        'last_reset' => current_time('timestamp')
    ));
    
    return $stats;
}

/**
 * 更新插件统计信息
 * 
 * @param string $type 统计类型
 */
function baby_wp_update_stats($type) {
    $stats = baby_wp_get_plugin_stats();
    
    if (isset($stats[$type])) {
        $stats[$type]++;
        $stats['total_filtered']++;
        update_option('baby_wp_comment_filter_stats', $stats);
    }
}

/**
 * 重置插件统计信息
 */
function baby_wp_reset_stats() {
    $default_stats = array(
        'total_filtered' => 0,
        'filtered_by_length' => 0,
        'filtered_by_chinese' => 0,
        'filtered_by_keywords' => 0,
        'last_reset' => current_time('timestamp')
    );
    
    update_option('baby_wp_comment_filter_stats', $default_stats);
}

/**
 * 获取WordPress讨论设置信息
 * 
 * @return array 讨论设置信息
 */
function baby_wp_get_discussion_info() {
    return array(
        'moderation_keys' => get_option('moderation_keys', ''),
        'disallowed_keys' => get_option('disallowed_keys', ''),
        'comment_moderation' => get_option('comment_moderation', false),
        'comment_whitelist' => get_option('comment_whitelist', false),
        'require_name_email' => get_option('require_name_email', true),
        'comment_registration' => get_option('comment_registration', false)
    );
}

/**
 * 检查插件是否应该跳过当前用户
 * 
 * @return bool 是否跳过
 */
function baby_wp_should_skip_user() {
    // 跳过管理员
    if (baby_wp_is_admin_user()) {
        return true;
    }
    
    // 跳过已登录用户（可选）
    $skip_logged_in = baby_wp_get_option('skip_logged_in_users', false);
    if ($skip_logged_in && is_user_logged_in()) {
        return true;
    }
    
    return false;
}

/**
 * 获取插件版本信息
 * 
 * @return array 版本信息
 */
function baby_wp_get_version_info() {
    return array(
        'version' => BABY_WP_COMMENT_FILTER_VERSION,
        'plugin_file' => BABY_WP_COMMENT_FILTER_PLUGIN_FILE,
        'plugin_dir' => BABY_WP_COMMENT_FILTER_PLUGIN_DIR,
        'plugin_url' => BABY_WP_COMMENT_FILTER_PLUGIN_URL
    );
}

/**
 * 获取默认错误消息
 * 
 * @return array 默认错误消息数组
 */
function baby_wp_get_default_error_messages() {
    return array(
        'too_long' => '额，你评论的内容太多啦，最多可以输入{max_length}个字，不要再评论区写论文啊！',
        'too_short' => '评论内容太短了，至少需要{min_length}个字哦！',
        'no_chinese' => '不要乱发哦，让姐姐我不开心就不好了嘛！(评论禁止纯英文字符、数字内容)',
        'banned_word' => '不要乱发哦，让姐姐我不开心就不好了嘛！(你tmd别发广告了ok？你是傻逼吗？！)'
    );
}

/**
 * 获取默认错误标题
 * 
 * @return array 默认错误标题数组
 */
function baby_wp_get_default_error_titles() {
    return array(
        'too_long' => '宝贝，出错了哦 - obaby@mars',
        'too_short' => '宝贝，出错了哦 - obaby@mars',
        'no_chinese' => '姐姐我不开心啦！ - obaby@mars',
        'banned_word' => '姐姐我不开心啦！ - obaby@mars'
    );
}

/**
 * 重置错误消息为默认值
 * 
 * @return bool 是否重置成功
 */
function baby_wp_reset_error_messages() {
    $options = baby_wp_get_option(null, array());
    $options['error_messages'] = baby_wp_get_default_error_messages();
    return update_option('baby_wp_comment_filter_options', $options);
}

/**
 * 获取当前错误消息设置
 * 
 * @return array 当前错误消息数组
 */
function baby_wp_get_error_messages() {
    $options = baby_wp_get_option(null, array());
    $default_messages = baby_wp_get_default_error_messages();
    
    $messages = array();
    foreach ($default_messages as $key => $default) {
        $messages[$key] = isset($options['error_messages'][$key]) ? $options['error_messages'][$key] : $default;
    }
    
    return $messages;
}

/**
 * 获取当前错误标题设置
 * 
 * @return array 当前错误标题数组
 */
function baby_wp_get_error_titles() {
    $options = baby_wp_get_option(null, array());
    $default_titles = baby_wp_get_default_error_titles();
    
    $titles = array();
    foreach ($default_titles as $key => $default) {
        $titles[$key] = isset($options['error_titles'][$key]) ? $options['error_titles'][$key] : $default;
    }
    
    return $titles;
}

/**
 * 更新错误消息
 * 
 * @param string $key 消息键名
 * @param string $message 消息内容
 * @return bool 是否更新成功
 */
function baby_wp_update_error_message($key, $message) {
    $options = baby_wp_get_option(null, array());
    
    if (!isset($options['error_messages'])) {
        $options['error_messages'] = array();
    }
    
    $options['error_messages'][$key] = $message;
    return update_option('baby_wp_comment_filter_options', $options);
}

/**
 * 更新错误标题
 * 
 * @param string $key 标题键名
 * @param string $title 标题内容
 * @return bool 是否更新成功
 */
function baby_wp_update_error_title($key, $title) {
    $options = baby_wp_get_option(null, array());
    
    if (!isset($options['error_titles'])) {
        $options['error_titles'] = array();
    }
    
    $options['error_titles'][$key] = $title;
    return update_option('baby_wp_comment_filter_options', $options);
}

/**
 * 验证错误消息格式
 * 
 * @param string $message 消息内容
 * @return bool 是否为有效格式
 */
function baby_wp_validate_error_message($message) {
    if (empty($message)) {
        return false;
    }
    
    // 检查长度
    if (strlen($message) > 500) {
        return false;
    }
    
    // 检查是否包含有效的占位符
    $valid_placeholders = array('{min_length}', '{max_length}');
    $has_valid_placeholder = false;
    
    foreach ($valid_placeholders as $placeholder) {
        if (strpos($message, $placeholder) !== false) {
            $has_valid_placeholder = true;
            break;
        }
    }
    
    return true; // 允许没有占位符的消息
}

/**
 * 获取错误消息预览
 * 
 * @param string $key 消息键名
 * @param array $placeholders 占位符数组
 * @return string 预览消息
 */
function baby_wp_preview_error_message($key, $placeholders = array()) {
    $messages = baby_wp_get_error_messages();
    $message = isset($messages[$key]) ? $messages[$key] : '';
    
    // 设置默认占位符
    $default_placeholders = array(
        'min_length' => 10,
        'max_length' => 500
    );
    
    $placeholders = array_merge($default_placeholders, $placeholders);
    
    return baby_wp_format_error_message($message, $placeholders);
}
