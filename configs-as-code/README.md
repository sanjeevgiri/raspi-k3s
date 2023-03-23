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
# Check playbook
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "global_state=present"
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "global_state=present" -e "amn_state=absent"
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "global_state=absent"
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "global_state=absent" -e "acn_state=present"


# Apply playbook
ansible-playbook site.yml -i ./inventory/hosts.ini -e "global_state=present"
ansible-playbook site.yml -i ./inventory/hosts.ini -e "global_state=present" -e "amn_state=absent"
ansible-playbook site.yml -i ./inventory/hosts.ini -e "global_state=absent"
ansible-playbook site.yml -i ./inventory/hosts.ini -e "global_state=absent" -e "acn_state=present"

# Check control node
sudo cat /etc/passwd | grep ansible
sudo cat /etc/group | grep ansible
ls -la /home/ansible/.ssh

# Pass variables during playbook execution
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "@values.yml"
```

# What next
- Specify UID and GID while creating ansible users and groups
- Create open ssh keypair in control plane
- Specify authorized key for ansible managed nodes
