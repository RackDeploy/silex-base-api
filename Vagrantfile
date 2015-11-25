# -*- mode: ruby -*-
# vi: set ft=ruby :

VAGRANTFILE_API_VERSION = "2"
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.define "api1" do |api1|
    api1.vm.provider "docker" do |d|
      d.name = "api1"
      d.build_dir = "./build/api"
      d.create_args = ["-p", "192.168.169.13:80:80","-p", "192.168.169.13:443:443"]
      #d.create_args = ["-p", "192.168.169.13:80:80","-p", "192.168.169.13:443:443", "--add-host","db.test.io:192.168.169.11"]
      d.volumes = [
        "/vagrant_src:/var/www/omni-api/",
        "/vagrant_build/api/sites-enabled:/etc/apache2/sites-enabled/"
        ]
      d.env = {
        APACHE_RUN_USER: "www-data",
        APACHE_RUN_GROUP: "www-data",
        APACHE_LOG_DIR: "/var/log/apache2"
      }
      d.vagrant_vagrantfile = "./build/dockerhost/Vagrantfile"
    end
  end
end
