# Configuration as code CASC

## Control node setup
I will be using the host where we plan on deploying the NFS server as the ansible control node. 

### Install Ansible, Git, and Configure Setup SSH Keys
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

## Configure Ansible Control and Managed Nodes
- Ansible group and user
- Ansible user ssh key in control node
- Ansible user authorized keys in managed nodes
```shell
# Ping
ansible all -m ping -i ./inventory/hosts.ini

# Check playbook to create ansible group, user, and setup ssh keys
ansible-playbook site.yml --check -i ./inventory/ansible-hosts.ini
ansible-playbook site.yml --check -i ./inventory/ansible-hosts.ini -e "amn_state=absent" -e "acn_state=absent"

# Execute playbook to create ansible group, user, and setup ssh keys and cleanup
ansible-playbook site.yml -i ./inventory/ansible-hosts.ini
ansible-playbook site.yml -i ./inventory/ansible-hosts.ini -e "amn_state=absent" -e "acn_state=absent"

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



# Scratch Pad
```shell
# Pass variables during playbook execution
ansible-playbook site.yml --check -i ./inventory/hosts.ini -e "@values.yml"
```

# What next
- NFS server and autofs
