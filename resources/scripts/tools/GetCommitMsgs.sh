# Get commit details

cd {{ mirror_path }}

git log {{ git_reference }} -n{{ line }} --pretty=format:"%H %h | %s | %an | %cd"
