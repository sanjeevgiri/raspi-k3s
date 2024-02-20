# Manual steps for generating video image previews
## Create arm64 docker image with ffmpeg
With the default official image, video file image preview is not available. In order to manually create image previews a docker image needs to be created with libreoffice and other packages.

```shell
docker buildx build --platform linux/arm64 -t yourdockerhubusername/nextcloud-arm64-ffmpeg:28.0.2 --push .
```

## Deploy custom docker image
Update repository and image tag values in ~/raspi-k3s/config_as_code/roles/k3s/apps/nextcloud/vars/main.yml

```shell
cd ~/raspi-k3s/config_as_code
ansible-playbook site_k3s_nextcloud.yml -i ./inventory/hosts.ini
```

## Update config.php
This step can be performed on the nfs server.

```shell
cd /path/to/nfs/sever/pvc-id/config

# backup existing config.php
cp -a config.php config.php.bk

# Update config.php based on contents defined in templates/config.php file
```

## Manually generate video previews
```shell
# SSH into one of kubernetes control node(s)
kubectl exec -it pod-id -n nextcloud bash

php /var/www/html/occ preview:pre-generate -vvv
```

## References
https://www.allerstorfer.at/nextcloud-install-preview-generator/

