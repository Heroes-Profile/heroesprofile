FROM mysql:5

# 999 is the default docker user, this avoids write permission issues with mysql
RUN mkdir ./vol_db_hp && chown -R 999:999 vol_db_hp