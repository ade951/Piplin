# Get commit details

cd {{ mirror_path }}

git log {{ git_reference }} -n{{ line }} --pretty=format:"%H%x09%s%x09%h | %s | %an | %cd"
