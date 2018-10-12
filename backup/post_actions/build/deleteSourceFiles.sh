#!/usr/bin/env bash
# 删除构建目录里面的所有文件（需要的构建物已经上传回站点可供下载了）

buildPath="{{ build_path }}"
echo ${buildPath}
if [ -d "${buildPath}" ]
then
    if [ ${buildPath:0-21:6} = builds ] #严格检查目录，以免误删造成严重后果
    then
        cd ${buildPath}
        rm -rf *
        echo "delete ok"
    fi
fi

