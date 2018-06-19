# 打更新包

# 仓库代码拉取

chmod +x {{ wrapper_file }}
export GIT_SSH={{ wrapper_file }}

if [ ! -d {{ mirror_path }} ]; then
    git clone --mirror {{ repository }} {{ mirror_path }}
fi

cd {{ mirror_path }}

git fetch --all --prune


# 更新包逻辑开始

old_hash={{ from_tag }}

new_hash={{ to_tag }}

repo_path=`pwd`


# 注意，这里的$0需要用反斜杠转义，否则会被替换成文件名，下同
git diff --name-status ${old_hash} ${new_hash} | awk '/^[^DR]/{print "\""substr(\$0, 3)"\""}'

# 由外部保证这个目录存在，并且是空的
archive_path={{ archive_path }}

# git生成代码压缩包 # 排除掉删除、重命名文件列表
if [ -f $archive_path/C_R_FILES.txt ]
  then
    # 删除文件以确保不要有多余的文件
    rm -f $archive_path/C_R_FILES.txt
fi
git diff --name-status $old_hash $new_hash | awk '/^[^DR]/{print "\""substr(\$0, 3)"\""}' >> $archive_path/C_R_FILES.txt
git diff --name-status $old_hash $new_hash | awk '/^[R]/{print "\""\$3"\""}' >> $archive_path/C_R_FILES.txt
cat $archive_path/C_R_FILES.txt | xargs git archive -o $archive_path/__git_archive.tar $new_hash
git diff $old_hash $new_hash --name-status | awk -F"\t" '/^[D]/{print \$2}' >> $archive_path/文件删除.txt
echo >> $archive_path/文件删除.txt #输出空行作为删除文件和重命名文件的分隔
git diff $old_hash $new_hash --name-status | awk -F"\t" '/^[R]/{print \$2}' >> $archive_path/文件删除.txt


cd $archive_path

# 清除旧目录
if [ -d __git_archive ]
  then
    rm -rf __git_archive
fi
# 创建新目录
mkdir __git_archive
tar xf __git_archive.tar -C __git_archive

cd __git_archive
mv $archive_path/文件删除.txt 文件删除.txt
mv $archive_path/C_R_FILES.txt 文件更新.txt
echo "更新日期："`date` > 更新说明.txt
echo "版本号：$old_hash => $new_hash" >> 更新说明.txt

cd ..
rm -f __git_archive.tar

mv __git_archive update
tar czf update.tar.gz update

save_path={{ save_path }}
mv update.tar.gz $save_path
