# Raspberry Pi: Kubernets Cluster with NFS for persistent volumes

## 1 Imaging
### 1.1 Creating an image for nfs server using raspberrypi imager
> Install raspberry pi imager from https://www.raspberrypi.com/software/

> Creating an image using - Raspberry Pi OS Other > Raspberry Pi OS Lite (64 bit No desktop)

> Click on gear icon, and setup keypair only using username of the public key

![imager.png](imager.png)

### Imager TODO
- ssh-keygen -t rsa -f ~/.ssh/raspi_id_rsa3 -C pi
- Gear hostname nfs
- Allow public key auth only
- While creating image use pi for for the keypair user pi

### 1.2 Creating an image for k3s main node usng rasbperypi imager
Follow steps outlined in section 1.1, with one minor modification > `hostname rayaayu` 

### 1.3 Creating an image for k8s worker1 node usng rasbperypi imager
Follow steps outlined in section 1.1, with one minor modification > `hostname node1`

### 1.4 Creating an image for k8s worder2 node usng rasbperypi imager
Follow steps outlined in section 1.1, with one minor modification > `hostname node2` 

