# Configuration as code CASC
Configure the following:
- Ansible nodes
- nfs server
- k3s nodes

# Prequisites
- All servers are configured for UTC timezone
- All servers are based on raspberry pi arm 64
- All servers OS are ubuntu server 22.04.2 LTS Server +
- All servers have the boot file updated to enable cgroup
  cgroup=1 cgroup_enable=memory

# Ansible Control Node
In this implementation, the host where we plan on deploying the NFS server as the ansible control node. 

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
sudo apt install --no-install-recommends python3-netaddr

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
ansible-playbook site_ansible.yml --check -i ./inventory/hosts.ini
ansible-playbook site_ansible.yml --check -i ./inventory/hosts.ini -e "amn_state=absent" -e "acn_state=absent"

# Execute playbook to create ansible group, user, and setup ssh keys and cleanup
ansible-playbook site_ansible.yml -i ./inventory/hosts.ini
ansible-playbook site_ansible.yml -i ./inventory/hosts.ini -e "amn_state=absent" -e "acn_state=absent"

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

# Performing package and security updates/upgrades
```shell
ansible-playbook site_update.yml --check -i ./inventory/hosts.ini
ansible-playbook site_update.yml -i ./inventory/hosts.ini

# if update fails due to certs, - sudo apt install ca-certificates
# TODO code the cert install if it works
sudo apt update
sudo apt upgrade
sudo apt autoremove
```

# Configure NFS Sever
- Install and configure autofs
- Install and configure nfs server

```shell
# NFS Checks
ansible-playbook site_nfs_server.yml --check -i ./inventory/hosts.ini
ansible-playbook site_nfs_server.yml --check -i ./inventory/hosts.ini -e "nfs_server_state=absent"

# NFS Apply
ansible-playbook site_nfs_server.yml -i ./inventory/hosts.ini

# NFS Undo
ansible-playbook site_nfs_server.yml -i ./inventory/hosts.ini -e "nfs_server_state=absent"
```

# Configure NFS Clients
```shell
# NFS Checks
ansible-playbook site_nfs_clients.yml --check -i ./inventory/hosts.ini
ansible-playbook site_nfs_clients.yml --check -i ./inventory/hosts.ini -e "nfs_client_state=absent"

# NFS Apply
ansible-playbook site_nfs_clients.yml -i ./inventory/hosts.ini

# NFS Undo
ansible-playbook site_nfs_clients.yml -i ./inventory/hosts.ini -e "nfs_client_state=absent"

# Manually mount and unmount for testing at this point
# At later stages, we will use NFS provisioner for creating k3s storage class and managing mounts
sudo mount -t nfs 192.168.86.10:/mnt/usbs/pioneer1 /mnt/nfs/pioneer1
df -h

umount /mnt/nfs/pioneer1
```

# Configure K3s Cluster Nodes (Control and Data)
- Install k3s
  - https://github.com/k3s-io/k3s/releases/download/{{ k3s_version }}/k3s
  - k3s_version current - v1.27.2+k3s1 vs v1.25.9+k3s1
- Install raspi extra modules
- Setup UTC timezone
- Enable ipv4 and ipv6 port forwarding
- Enable ipv6 router advertisements
- Variables
  - systemd_dir
  - system_timezone

```shell
# Checks
ansible-playbook site_k3s_cluster.yml --check -i ./inventory/hosts.ini
ansible-playbook site_k3s_cluster.yml --check -i ./inventory/hosts.ini -e "k3s_cluster_state=present"

# Apply
ansible-playbook site_k3s_cluster.yml -i ./inventory/hosts.ini

# Undo
ansible-playbook site_k3s_cluster.yml -i ./inventory/hosts.ini -e "k3s_cluster_state=absent"
```

# Configure K3s Control Nodes
- Cleanup prior initialization transient services
- Deploy vip (VIP allocates virtual ip to control nodes and can be thought of as a LB for control nodes)
- Deploy metallb (replace traefik with metallb, lb for applications), attempt to use VIP for applications as well
- Initialization with systemd-run (transient service) and parameters - `extra_server_args, server_init_args`
- Verify all control nodes have joined the cluster and save k3s init logs
- Copy and enable k3s service
- Manage node token (wait for availability, access, read from control, store, restore access)
- Configure kubectl cluster
- Create kubectl and crictl symlinks
- Remove manifests and folders that are only needed for bootstrapping cluster so k3s does not auto apply on start

## Vip deployment (LB for control plane and services)
- Create a manifest directory in the first control node (ensure naming consistency here)
- Download vip rback 
  - kube_vip_tag_version used 0.5.7, current 0.6.0
  - https://github.com/kube-vip/kube-vip/blob/v0.6.0/docs/manifests/rbac.yaml
- Copy vip rback to first control node

```shell
# Checks
ansible-playbook site_k3s_control_nodes.yml --check -i ./inventory/hosts.ini
ansible-playbook site_k3s_control_nodes.yml --check -i ./inventory/hosts.ini -e "k3s_cn_state=absent"

# Apply
ansible-playbook site_k3s_control_nodes.yml -i ./inventory/hosts.ini

# Undo
ansible-playbook site_k3s_control_nodes.yml -i ./inventory/hosts.ini -e "k3s_cn_state=absent"

```


## K3 Control Nodes Variables:
K3s Server
- apiserver_endpoint:
  - VIP based virtual ip address to use for api_server
- server_init_args:
  - Used for bootstrapping k2s service
  - https://github.com/k3s-io/k3s/discussions/3429
  - https://docs.k3s.io/cli/server
- extra_server_args
  - extra args
  - Exclude workloads from being scheduled in control nodes with node taints - --node-taint node-role.kubernetes.io/master=true:NoSchedule
  - tls-san api sever ip
  - disable servicelb and traefik
- extra_args
  - Flannel interface
  - Node ip
- log_destination
  - Set this variable to store initialization logs
- systemd_dir
  - This is where services will be installed and configured
- retry_count
  - Used to verify all control nodes have joined the cluster

Metallb
- metal_lb_type:
  - native - for ipv4 frr for ipv6 support
- metal_lb_mode:
  - layer2 - post k3s tasks
- https://github.com/metallb/metallb
- https://metallb.universe.tf/installation/

# Configure K3s Data Plane
- Create service for k3s-node
- Ensure apiserver_endpoint is configured at all level

```shell
# Checks
ansible-playbook site_k3s_data_nodes.yml --check -i ./inventory/hosts.ini
ansible-playbook site_k3s_data_nodes.yml --check -i ./inventory/hosts.ini -e "k3s_dn_state=absent"

# Apply
ansible-playbook site_k3s_data_nodes.yml -i ./inventory/hosts.ini -e "k3s_dn_token=contentsOf(/var/lib/rancher/k3s/server/node-token

# Undo
ansible-playbook site_k3s_data_nodes.yml -i ./inventory/hosts.ini -e "k3s_dn_state=absent"

```

# Storage class / Dynamic PV Provisioner
Prequisite: Setup KUBECONFIG environment variable
- Executing the k3s control nodes module will fetch the k3s token file on the ansible control node
- Change the permission to 700 on the ./kubeconfig file
- Include `export KUBECONFIG=path/to/the/repo/raspi-k3s/config_as_code/kubeconfig` in control node default and root user's ~/.bashrc file

```shell
# Apply
ansible-playbook site_k3s_nfs_storage_class.yml -i ./inventory/hosts.ini
# Undo
ansible-playbook site_k3s_nfs_storage_class.yml -i ./inventory/hosts.ini -e "nfs_sc_state=absent"
```


# Nextcloud
```shell
# Apply
ansible-playbook site_k3s_nextcloud.yml -i ./inventory/hosts.ini
# Undo
ansible-playbook site_k3s_nextcloud.yml -i ./inventory/hosts.ini -e "nextcloud_state=absent"

## Preview generator
https://www.allerstorfer.at/nextcloud-install-preview-generator/