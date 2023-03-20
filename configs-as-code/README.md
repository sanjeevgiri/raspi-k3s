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

# Copy bootstrap keys from bastion into control node for setup
scp id_rsa nfs@nfs:~/.ssh
scp id_rsa.pub nfs@nfs:~/.ssh
ssh nfs@nfs
chmod 400 id_rsa
chmod 400 id_rsa.pub
git clone git@github.com:sanjeevgiri/raspi-k3s.git

# Ping
ansible all -m ping -i ./inventory/hosts.ini
```


# Scratch Pad
```shell
ansible-playbook foo.yml --check

```
