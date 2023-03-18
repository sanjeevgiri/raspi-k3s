# Configuration as code CASC

## Control node setup
I will be using the host where we plan on deploying the NFS server as the ansible control node. 

### Bootstrapping control node
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

# Hop back in
ssh nfs@nfs

#
```
