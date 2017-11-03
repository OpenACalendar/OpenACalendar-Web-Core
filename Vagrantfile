# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

	# It seems to need this here or "destroy" errors.
	config.vm.box = "boxcutter/ubuntu1604"


	config.vm.define "app" do |normal|

		config.vm.box = "boxcutter/ubuntu1604"
		config.vm.box_version = "2.0.18"

		config.vm.network "forwarded_port", guest: 8080, host: 8080
		config.vm.network "forwarded_port", guest: 8081, host: 8081
		config.vm.network "forwarded_port", guest: 8082, host: 8082

		config.vm.synced_folder ".", "/vagrant",  :owner=> 'vagrant', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

		config.vm.provider "virtualbox" do |vb|
			# Display the VirtualBox GUI when booting the machine
			vb.gui = false

			# Customize the amount of memory on the VM:
			vb.memory = "512"
		end

		config.vm.provision :shell, path: "vagrant/app/bootstrap.sh"

	end

	config.vm.define "tests" do |normal|

		config.vm.box = "ubuntu/xenial64"
	    config.ssh.username = "ubuntu"

		config.vm.synced_folder ".", "/vagrant",  :owner=> 'ubuntu', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

		config.vm.provider "virtualbox" do |vb|
			# Display the VirtualBox GUI when booting the machine
			vb.gui = false

			# Customize the amount of memory on the VM:
			vb.memory = "2048"
		end

		config.vm.provision :shell, path: "vagrant/tests/bootstrap.sh"

	end

	config.vm.define "frontendtests" do |normal|

		config.vm.box = "boxcutter/ubuntu1604-desktop"
		config.vm.box_version = "2.0.18"

		config.vm.synced_folder ".", "/vagrant",  :owner=> 'vagrant', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

		config.vm.provider "virtualbox" do |vb|
			vb.gui = true
			vb.memory = "2048"
		end

		config.vm.provision :shell, path: "vagrant/frontendtests/bootstrap.sh"

	end

end
