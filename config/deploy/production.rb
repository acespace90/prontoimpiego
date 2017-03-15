set :deploy_to,         "/var/www/ca.gnvpartners.com"

set :user,              "ubuntu"
set :domain,            "ca.gnvpartners.com"
ssh_options[:keys] = ["./ec2-key.pem"]
set :use_sudo,          false

role :web,   domain
role :app,   domain, :primary => true
