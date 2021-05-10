/var/www/html:
  file.directory:
    - user: www-data
    - group: www-data
    - mode: 755
    - makedirs: True

/var/ilias/data:
  file.directory:
    - user: www-data
    - group: www-data
    - mode: 755
    - makedirs: True

/var/ilias/logs:
  file.directory:
    - user: www-data
    - group: www-data
    - mode: 755
    - makedirs: True

ilias_git_config:
  git.config_set:
    - name: core.fileMode
    - value: false
    - repo: /var/www/html

/var/www/html/:
  file.directory:
    - user: www-data
    - group: www-data
    - recurse:
      - user
      - group

/var/ilias/:
  file.directory:
    - user: www-data
    - group: www-data
    - recurse:
      - user
      - group