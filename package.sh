#!/bin/bash

# Baby WP 评论强化拦截插件 - 打包脚本
# 用于创建插件的发布包

PLUGIN_NAME="baby-wp-comment-filter"
VERSION="1.0.0"
PACKAGE_NAME="${PLUGIN_NAME}-${VERSION}"

echo "正在创建 Baby WP 评论强化拦截插件发布包..."
echo "插件名称: ${PLUGIN_NAME}"
echo "版本: ${VERSION}"
echo "包名: ${PACKAGE_NAME}"

# 创建临时目录
TEMP_DIR="/tmp/${PACKAGE_NAME}"
mkdir -p "${TEMP_DIR}"

# 复制插件文件
echo "复制插件文件..."
cp -r . "${TEMP_DIR}/"

# 删除不需要的文件
echo "清理不需要的文件..."
cd "${TEMP_DIR}"
rm -f package.sh
rm -f .DS_Store
rm -rf .git
rm -rf node_modules

# 创建zip包
echo "创建zip包..."
cd /tmp
zip -r "${PACKAGE_NAME}.zip" "${PACKAGE_NAME}/"

# 移动zip包到当前目录
mv "${PACKAGE_NAME}.zip" "$(dirname "$0")/"

# 清理临时目录
rm -rf "${TEMP_DIR}"

echo "✅ 打包完成！"
echo "发布包: ${PACKAGE_NAME}.zip"
echo "文件大小: $(du -h "${PACKAGE_NAME}.zip" | cut -f1)"
