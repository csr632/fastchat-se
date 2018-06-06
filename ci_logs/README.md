# 注意
由于docker的限制，挂载在容器内的文件夹的拥有者ID、用户组ID继承自宿主文件夹。
为了codeigniter有写日志的权限（php容器使用www-data用户和www-data用户组），请在宿主机器上将ci_logs文件夹的权限设置为777。(`chmod -R 777 ./ci_logs`)
> https://stackoverflow.com/questions/29245216/write-in-shared-volumes-docker
