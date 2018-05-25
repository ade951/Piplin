# 脚本名称：项目签名
# 用途：给项目打上一个唯一签名，可用于盗版系统追踪
# 配置路径：自动发卡 > 构建计划 > 构建结果 > 后置任务
# 适用项目：自动发卡、免签支付
# 依赖：sign.php

echo 'signing';

if [ ! -f /usr/local/sbin/sign.php ] #此为外部依赖，首次使用需要配置
then
    echo 'error: sign.php does not exists!' 1>&2
    exit 1
fi
php /usr/local/sbin/sign.php '{{ build_path }}' '{{ vsign }}' '{{ project_id }}'

echo 'sign success';
