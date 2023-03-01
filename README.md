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

## 2 NFS Setup

### Network File Share Server Setup

