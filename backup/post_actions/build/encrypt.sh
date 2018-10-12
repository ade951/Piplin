# 脚本名称：源码加密打包
# 用途：加密源码并打包
# 配置路径：项目主页 > 构建计划 > 构建结果 > 后置任务
# 适用项目：所有项目
# 依赖：swoole-compiler 1.8.0

echo 'swoole-compiler {{ php_version }} {{ build_path }} {{ build_path }}/app_encrypted.tar.gz 0 0 0 {{ domain_restriction }} linux'
#因为直接运行此命令时，exit code不是0，会导致构建任务失败，所以这里用反引号去执行
echo `swoole-compiler {{ php_version }} {{ build_path }} {{ build_path }}/app_encrypted.tar.gz 0 0 0 {{ domain_restriction }} linux`
if [ -f {{ build_path }}/app_encrypted.tar.gz ]
then
  echo 'file generated, exit 0'
  exit 0
fi
