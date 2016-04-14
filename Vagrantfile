# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

	# It seems to need this here or "destroy" errors.
	config.vm.box = "boxcutter/debian82"

	config.vm.define "tests" do |normal|

		config.vm.box = "boxcutter/debian82"

		config.vm.synced_folder ".", "/vagrant",  :owner=> 'vagrant', :group=>'users', :mount_options => ['dmode=777', 'fmode=777']

		config.vm.provider "virtualbox" do |vb|
			# Display the VirtualBox GUI when booting the machine
			vb.gui = false

			# Customize the amount of memory on the VM:
			vb.memory = "1024"
		end

		config.vm.provision :shell, path: "vagrant/tests/bootstrap.sh"

	end

end
