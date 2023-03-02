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

### 1.5 Configure SSH Client

#### 1.5.1 Configure DHCP IP Reservations

Example using my subnet settings

| Device | IP Address |
| --- | --- |
| NFS Server | 192.168.86.10 |
| k3s main | 192.168.86.100 |
| k3s node1 | 192.168.86.110 |
| k3s node2 | 192.168.86.111 |

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
sudo mkdir /media/nfsmnts
sudo mkdir /media/nfsmnts/usbpioneer1

# Mount NTFS
# sudo mount -t ntfs-3g /dev/sda1 /media/nfsmnts/usbpioneer1


# Unmount
# sudo umount /media/nfsmnts/usbpioneer1

# Automounting an external drive using autofs at runtime (when exploring directories)
## Install autofs
sudo apt install autofs

sudo udevadm test --action="add" /devices/platform/scb/fd500000.pcie/pci0000:00/0000:00:00.0/0000:01:00.0/usb2
sudo udevadm control --reload
sudo systemctl restart udev
ls -lah /dev | grep usb

## Configure autofs
sudo cp /etc/auto.master /etc/auto.master.orig
sudo nano /etc/auto.master # End of line - /media/nfsmnts/   /etc/auto.ext-usb --timeout=10,defaults,user,exec,uid=1000
sudo nano /etc/auto.ext-usb

sudo systemctl restart autofs

sudo systemctl stop autofs
```

#### 2.1.2 Install and Configure NFS Server
```shell
# Prepare root directories to mount onto
sudo mkdir /exports
cd /exports
sudo ln -s /media/ext1/nfsroot ./nfsroot

# NFS Server install
sudo apt install nfs-kernel-server
systemctl status nfs-kernet-server

# Configure nfs server
cat /etc/exports
sudo mv /etc/exports /etc/exports.original
sudo echo "/exports/nfsroot 192.168.86.0/255.255.255.0(rw,no_subtree_check)" > /etc/exports

# Restart server
sudo systemctl restart nfs-kernel-server
systemctl status nfs-kernet-server

# Create test files
sudo echo "hello world" > /exports/documents/test1.txt
```

### NFS Client Setup
```shell
# Install client
sudo apt install nfs-common

# Test connectivity
showmount --exports 92.168.86.10

# Prepare mount point
sudo mkdir /mnt/nfs
sudo mkdir /mnt/nfs/documents

# Mount
sudo mount 92.168.86.10:/exports/documents /mnt/nfs/documents
df -h
ls -l /mnt/nfs/documents
cat /mnt/nfs/documents/test1.txt

# Unmount
sudo umount /mnt/nfs/backup
df -h

# Automount with Autofs(Autofs handles creation of directories for mounting)
sudo rm -r /mnt/nfs/documents
sudo apt install autofs
systemctl status autofs

# Configure Autofs
sudo cp /etc/auto.master /etc/auto.master.orig

## Add this to end of file (ghost creates the mount point dirs, 
## and timeout ensures there is no continuous attempt when nfs server is down
sudo echo "/mnt/nfs /etc/auto.nfs --ghost --timeout=60" >> /etc/auto.master
sudo echo "documents -fstype=nfs4,rw 2.168.86.10:/exports/documents" > /etc/auto.nfs
tail -n 1 /etc/auto.master
cat /etc/auto.nfs

# Restart autofs
## Ensure nothing is mounted
df -h
sudo systemctl restart autofs
sudo systemctl status autofs

## mounting is done on demand, so checking mounts before accessing the shared directories will show nothing 
df -h
mount | grep nfs

ls -l /mnt/nfs
df -h

## In about 60 seconds, it gets unmounted
watch "mount | grep nfs"
```

