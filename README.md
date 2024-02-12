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
- Follow steps outlined in section 1.1, with one minor modification > `hostname ayuraya`
- Plug into main node, let it fully boot up
- Plug out the boot ssd out of the pi and hook it up back into pc
- Edit following files
cmdline.txt
```text
cgroup=1 cgroup_enable=memory ip=192.168.86.100::192.168.1.1:255:255.255.0:ayuraya:eth0:off
```
- Create an empty ssh file
```shell
touch ssh
```
- Hook the drive back into raspberry pi and let it boot up
### 1.3 Creating an image for k8s worker1 node usng rasbperypi imager
Follow steps outlined in section 1.2, with one minor modification > `hostname node1`

### 1.4 Creating an image for k8s worder2 node usng rasbperypi imager
Follow steps outlined in section 1.2, with one minor modification > `hostname node2`

### 1.5 Configure SSH Client

#### 1.5.1 Configure DHCP IP Reservations

Example using my subnet settings

| Device                  | IP Address       |
|-------------------------|------------------|
| NFS Server              | 192.168.86.10    |
| k3s vip control node ip | 192.168.86.20    |
| k3s vip alb ip          | 192.168.86.30-39 |
| k3s main                | 192.168.86.100   |
| k3s node1               | 192.168.86.110   |
| k3s node2               | 192.168.86.111   |


#### 1.5.2 SSH Configurations

Sample ~/.ssh/config file

```shell
Host *
	StrictHostKeyChecking no
Host main.ayuraya.com ayuraya
	IdentityFile ~/.ssh/ayuraya/id_rsa
Host node1.ayuraya.com node1
	IdentityFile ~/.ssh/ayuraya/id_rsa
Host node2.ayuraya.com node2
	IdentityFile ~/.ssh/ayuraya/id_rsa	
Host nfs.ayuraya.com nfs
	IdentityFile ~/.ssh/ayuraya/id_rsa

```

#### 1.5.3 SSH Key pair
Ensure that the public/private key pair for the key used for image generation exists in the appropriate location.
Example - IdentityFile `~/.ssh/ayuraya/id_rsa`

#### 1.5.4 Ssh into servers
```shell
ssh nfs@nfs
ssh ayuraya@ayuraya
ssh node1@node1
ssh node2@node2
```

## 2 Network File System (NFS) Setup

### 2.1 NFS Server Setup
#### 2.1.1 Configure External USB Drive Automount
```shell
# Get device information
lsblk 
sudo fdisk -l

# Create a mount point
sudo mkdir /mnt/usbs

# Mount NTFS
# sudo mount -t ntfs-3g /dev/sda1 /exports/usbmounts/pioneer1

# Unmount
# sudo umount /exports/usbmounts/pioneer1

# Automounting an external drive using autofs at runtime (when exploring directories)
## Install autofs
sudo apt install autofs

## Configure autofs
sudo cp /etc/auto.master /etc/auto.master.orig
echo '/mnt/usbs   /etc/auto.ext-usb --ghost --timeout=10,defaults,user,exec,uid=1000' | sudo tee -a /etc/auto.master
echo 'pioneer1        -fstype=auto    :/dev/sda1' | sudo tee /etc/auto.ext-usb
sudo nano /etc/auto.ext-usb

sudo systemctl restart autofs

# Stop autofs
# sudo systemctl stop autofs

systemctl status autofs
```
```diff
☝️ 
- You can change directory to the autofs configured mounts: cd /exports/usbmounts/pioneer1
- Howver if you perform df -h after the time out interval, it will not show up as mounted
- After step 2.1.2 however, it will always show up as mounted when we expose it with nfsserver
```

#### 2.1.2 Install and Configure NFS Server
```shell
# Install NFS server
sudo apt install nfs-kernel-server
sudo systemctl status nfs-kernel-server

# Configure nfs server
cat /etc/exports
sudo mv /etc/exports /etc/exports.original
echo '/mnt/usbs/pioneer1 192.168.86.0/255.255.255.0(rw,no_subtree_check)' | sudo tee /etc/exports

# Restart server
sudo systemctl restart nfs-kernel-server
systemctl status nfs-kernel-server
```

## 3 K3S Main Node(s)
### 3.1 NFS Client Setup
```shell
# Install client
sudo apt install nfs-common

# Test connectivity
showmount -e 192.168.86.10

# Prepare mount point
sudo mkdir /mnt/nfs
sudo mkdir /mnt/nfs/pioneer1

# Mount
sudo mount -t nfs 192.168.86.10:/mnt/usbs/pioneer1 /mnt/nfs/pioneer1
df -h

# Unmount
sudo umount /mnt/nfs/pioneer1
```

### 3.2 Install K3S
[Reference](https://www.phillipsj.net/posts/k3s-enable-nfs-storage/)
```shell

# Install linux extra modules for raspi
sudo apt install linux-modules-extra-raspi

# Install
curl -sfL https://get.k3s.io | sudo sh -

# Uninstall
# sudo sh /usr/local/bin/k3s-uninstall.sh

# Check nodes
sudo k3s kubectl get node
```

### 3.3 Deploy NFS Provisioner

#### /var/lib/rancher/k3s/server/manifests/nfs.yaml
```yaml
apiVersion: helm.cattle.io/v1
kind: HelmChart
metadata:
  name: nfs
  namespace: default
spec:
  chart: nfs-subdir-external-provisioner
  repo: https://kubernetes-sigs.github.io/nfs-subdir-external-provisioner
  targetNamespace: default
  set:
    nfs.server: 192.168.86.10
    nfs.path: /mnt/nfs/pioneer1
    storageClass.reclaimPolicy: Retain
    storageClass.name: nfs
```

```shell
echo 'apiVersion: helm.cattle.io/v1
kind: HelmChart
metadata:
  name: nfs
  namespace: default
spec:
  chart: nfs-subdir-external-provisioner
  repo: https://kubernetes-sigs.github.io/nfs-subdir-external-provisioner
  targetNamespace: default
  set:
    nfs.server: 192.168.86.10
    nfs.path: /mnt/nfs/pioneer1
    storageClass.reclaimPolicy: Retain
    storageClass.name: nfs' | sudo tee /var/lib/rancher/k3s/server/manifests/nfs.yaml
    
sudo cat /var/lib/rancher/k3s/server/manifests/nfs.yaml 

sudo k3s kubectl get storageclass
```

## 4 K3S Worker Nodes
### 4.1 NFS Client Setup
Follow same steps in section [NFS Client Setup](### 3.1 NFS Client Setup)

### 4.2 Cluster Token
```shell
sudo cat /var/lib/rancher/k3s/server/node-token
```

### 4.3 Install k3s
```shell
# Install linux extra modules for raspi
sudo apt install linux-modules-extra-raspi

# Install k3s
sudo su -
curl -sfL https://get.k3s.io | K3S_TOKEN="cluster-token-value" K3S_URL="https://192.168.86.100:6443" K3S_NODE_NAME="node1" sh -
```

## References
- https://github.com/techno-tim/k3s-ansible
- https://github.com/mrlesmithjr/ansible-nfs-server
- https://github.com/Oefenweb/ansible-nfs-server

