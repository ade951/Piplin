#!/usr/bin/env bash
# 复制对应版本的swoole_loader

phpVersion="{{ php_version }}"
phpVersion="${phpVersion:0:1}${phpVersion:2:1}" #这里的php版本默认都是两位数，类似7.2这样的格式
buildPath="{{ build_path }}"
loaderFile="/home/www/swoole-loader/swoole_loader${phpVersion}.so"
if [ -f ${loaderFile} ]
then
    cp ${loaderFile} ${buildPath}/
    echo "swoole_loader copy success"
fi
