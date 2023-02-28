# Raspberry Pi: Kubernets Cluster with NFS for persistent volumes

## Creating an image for nfs server using raspberrypi imager
> Install raspberry pi imager from https://www.raspberrypi.com/software/

> Creating an image using - Raspberry Pi OS Other > Raspberry Pi OS Lite (64 bit No desktop)

> Click on gear icon, and setup keypair only using username of the public key

![imager.png](imager.png)

## Imager TODO
- ssh-keygen -t rsa -f ~/.ssh/raspi_id_rsa3 -C pi
- Gear hostname nfs
- Allow public key auth only
- While creating image use pi for for the keypair user pi

## Creating an image for k8s main node usng rasbperypi imager

## Creating an image for k8s worker1 node usng rasbperypi imager

## Creating an image for k8s worder2 node usng rasbperypi imager
