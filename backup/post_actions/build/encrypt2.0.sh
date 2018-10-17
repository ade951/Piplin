#!/usr/bin/env bash
# 脚本名称：源码加密打包2.0
# 用途：加密源码并打包
# 配置路径：项目主页 > 构建计划 > 构建结果 > 后置任务
# 适用项目：所有项目
# 依赖：swoole-compiler 2.0.0

# 创建加密配置文件
configFile=/tmp/swoole-compiler-config-"{{ project_id }}".conf
cat > ${configFile} <<EOT
################################################
#          Swoole-Compiler 配置文件示例
#
# 注意：
#     1. 请按照配置格式和提示修改配置文件
#     2. 配置项等号左右不可以有空格
#     3. 注释使用 # 符号
################################################

################## 项目配置 ##################

# 项目名称配置
# 项目名称的格式要求为英文字母的组合
# 如果需要设置自定义信息或者限制代码的运行环境，例如ip地址，mac地址，hostname，代码有效时间等，则需要设置此参数。否则，此参数请留空
product_name=zhiyu

################## 加密代码配置 ##################

# PHP版本
php_version={{ php_version }}

# 需要加密的PHP文件路径
php_files_path={{ build_path }}

# 生成的加密文件打包路径
compiled_archived_path={{ build_path }}/app_encrypted.tar.gz

# 不需要进行加密的PHP文件或者路径
# 请按照示例所示格式添加
## 设置加密文件黑名单的示例
exclude_list=("{{ build_path }}/Runtime" "{{ build_path }}/Application/Common/Conf" "{{ build_path }}/config" "{{ build_path }}/demo" "{{ build_path }}/application/database.php" "{{ build_path }}/application/config.php" "{{ build_path }}/application/office.php")
## 不设置加密文件黑名单的示例
#exclude_list=""

# 是否保留注释
# 此选项留空或者0代表不保留，1代表保留注释，有的框架会用注释做路由配置，这种情况下需要保留注释
save_doc=0

#################### 授权证书配置 ####################

# 生成的证书文件的存放地址
# 此选型留空表示默认存放于当前文件夹
license_file=

# 可运行加密代码的机器的hostname
# 此选项留空或者0代表不限制运行加密代码的机器的域名(取值可以是多个域名 用英文逗号","隔离即可,支持*前缀 例如*.swoole.com代表允许所有swoole.com的二级域名运行加密文件)
hostname={{ domain_restriction }}

# 可运行加密代码的机器的ip地址
# 此选项留空或者0代表不限制运行加密代码的ip地址 (取值可以是多个ip地址 用英文逗号","隔离即可)
ip_address=0

# 加密的文件的最后有效时间(超过此时间后，代码不可以再运行)
# 格式为Unix时间戳
# 此选项留空或者0代表不设置最后的生效时间
expire_at=0

# 可运行加密的代码的机器的mac地址，不区分大小写
# 此选项留空或者0代表不限制运行加密代码的机器(取值可以是多个mac地址 用英文逗号","隔离即可)
# windows下mac地址用-分割，例如:  08-00-27-fe-74-52
mac_address=0

# 可选配置信息
# 以下配置为自定义配置，可以从运行代码中通过`swoole_get_license()`获取配置信息
# 以下为示例可选配置
user_data="{{ vsign }}"
copyright="广州知宇信息科技有限公司"
licensed_to="{{ domain_restriction }}"
EOT

# 生成证书
swoole-compiler -t license -c ${configFile}
chmod 440 license_zhiyu
# 加密打包
swoole-compiler -c ${configFile}

# 删除加密配置文件
if [ -f ${configFile} ]
then
    rm ${configFile}
fi

# 判断加密结果
if [ -f "{{ build_path }}"/app_encrypted.tar.gz ]
then
  mv "{{ build_path }}"/app_encrypted.tar.gz app_encrypted_for_"{{ domain_restriction }}".tar.gz
  echo 'file generated, exit 0'
  exit 0
fi

