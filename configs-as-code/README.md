# Configuration as code CASC
Configure the following:
- Ansible nodes
- nfs server
- k3s nodes

# Ansible Control Node Prerequisites
I will be using the host where we plan on deploying the NFS server as the ansible control node. 

## Install Ansible, Git, and Configure Setup SSH Keys
```shell
# Hop into the control node / nfs server
ssh nfs@nfs

# Install ansible
sudo apt update
sudo apt upgrade
sudo apt install software-properties-common
sudo add-apt-repository --yes --update ppa:ansible/ansible
sudo apt install ansible

# Install git
sudo apt install git-all
sudo reboot

# Copy bootstrap keys (keys specified while creating the images) to control node for initial setup
scp id_rsa nfs@nfs:~/.ssh
scp id_rsa.pub nfs@nfs:~/.ssh
ssh nfs@nfs
chmod 400 id_rsa
chmod 400 id_rsa.pub
git clone git@github.com:sanjeevgiri/raspi-k3s.git
```

# Configure Ansible Control and Managed Nodes
- Ansible group and user
- Ansible user ssh key in control node
- Ansible user authorized keys in managed nodes
```shell
# Ping
ansible all -m ping -i ./inventory/hosts.ini

# Check playbook to create ansible group, user, and setup ssh keys
ansible-playbook site-ansible.yml --check -i ./inventory/hosts.ini
ansible-playbook site-ansible.yml --check -i ./inventory/hosts.ini -e "amn_state=absent" -e "acn_state=absent"

# Execute playbook to create ansible group, user, and setup ssh keys and cleanup
ansible-playbook site-ansible.yml -i ./inventory/hosts.ini
ansible-playbook site-ansible.yml -i ./inventory/hosts.ini -e "amn_state=absent" -e "acn_state=absent"

# Check control node
sudo cat /etc/passwd | grep ansible
sudo cat /etc/group | grep ansible
ls -la /home/ansible/.ssh

# Check managed nodes
sudo ssh -i /home/ansible/.ssh/id_rsa ansible@node1
sudo cat /etc/passwd | grep ansible
sudo cat /etc/group | grep ansible
ls -la /home/ansible/.ssh
```

# Configure NFS Sever
- Install and configure autofs
- Install and configure nfs server

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


# Scratch Pad
```shell
# Pass variables during playbook execution
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "@values.yml"
```

## What next
- NFS server and autofs
