# 插件激活钩子修复说明

## 问题描述

在原始代码中，插件激活钩子 `register_activation_hook` 是在类的 `init_hooks()` 方法中注册的，但是这个方法是在构造函数中调用的，而构造函数是在 `get_instance()` 方法中调用的。但是 `get_instance()` 方法是在文件末尾才被调用的，这时候 `register_activation_hook` 已经太晚了。

## 问题原因

WordPress 的 `register_activation_hook` 必须在插件文件被加载时立即注册，不能在类实例化之后才注册。这是因为：

1. 插件激活时，WordPress 会直接调用注册的激活函数
2. 如果激活钩子在类实例化之后才注册，那么激活时钩子还没有注册
3. 这会导致插件激活时不会执行激活函数，默认设置不会被创建

## 修复方案

### 1. 移除类中的激活钩子注册

从 `init_hooks()` 方法中移除：
```php
// 插件激活和停用钩子
register_activation_hook(__FILE__, array($this, 'activate'));
register_deactivation_hook(__FILE__, array($this, 'deactivate'));
```

### 2. 在文件末尾注册激活钩子

在类定义之后，插件初始化之前注册激活钩子：
```php
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
```

### 3. 删除类中的激活方法

删除类中的 `activate()` 和 `deactivate()` 方法，因为激活逻辑已经移到全局函数中。

## 修复后的执行流程

1. **插件文件加载** → 立即注册激活钩子
2. **用户激活插件** → WordPress 调用注册的激活函数
3. **激活函数执行** → 创建默认选项
4. **插件初始化** → 类实例化，注册其他钩子

## 测试方法

### 1. 使用激活测试页面

访问 WordPress 后台：**设置** → **Baby WP 激活测试**

### 2. 手动测试

1. 停用插件
2. 删除插件选项：`delete_option('baby_wp_comment_filter_options')`
3. 重新激活插件
4. 检查选项是否被创建

### 3. 检查数据库

在 WordPress 数据库中检查 `wp_options` 表，确认 `baby_wp_comment_filter_options` 选项是否存在。

## 注意事项

1. **激活钩子必须在文件加载时注册**，不能在类实例化之后
2. **使用匿名函数**而不是类方法，避免类实例化问题
3. **保持用户设置**，停用时通常不删除用户配置
4. **测试激活功能**，确保默认设置正确创建

## 相关文件

- `baby-wp-comment-filter.php` - 主插件文件（已修复）
- `test-activation.php` - 激活测试文件
- `ACTIVATION-FIX.md` - 本说明文档

## 总结

通过将激活钩子注册移到文件加载时，确保插件激活时能正确执行激活函数，创建默认设置。这是 WordPress 插件开发的最佳实践。
