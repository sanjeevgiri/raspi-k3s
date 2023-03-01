# Raspberry Pi: Kubernets Cluster with NFS for persistent volumes

## 1 Imaging
### 1.1 Creating an image for nfs server using raspberrypi imager
> Install raspberry pi imager from https://www.raspberrypi.com/software/

> Create an image using following parameters

| Parameter | Value |
| --- | --- |
| Operating System | Ubuntu Server 22.04.2 LTS (64 Bit) |
| Hostname | nfs |
| Enable SSH | ✔️ |
| SSH > Allow public key authentication only | public-key-value |
| Username | nfs |
| Password | \*\*\*\*\*\*\* |

![](imager.png)

### Generate keypair
`ssh-keygen -t rsa -f ~/.ssh/ayuraya_id_rsa -C ayuraya`

### 1.2 Creating an image for k3s main node usng rasbperypi imager
Follow steps outlined in section 1.1, with one minor modification > `hostname ayuraya` 

### 1.3 Creating an image for k8s worker1 node usng rasbperypi imager
Follow steps outlined in section 1.1, with one minor modification > `hostname node1`

### 1.4 Creating an image for k8s worder2 node usng rasbperypi imager
Follow steps outlined in section 1.1, with one minor modification > `hostname node2` 


