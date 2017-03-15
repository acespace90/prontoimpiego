set :stages, %w(production)
set :default_stage, "production"
require 'capistrano/ext/multistage'

set :application,       "coop-alleanza"

set :scm,               :git
set :branch,            fetch(:branch, "master")
set :repository,        "git@bitbucket.org:gnvpartners/coop-alleanza.git"
set :php_bin,           "php"
set :slack,             false
set :slack_channel,     "coop"
set :slack_key,         "RsLr5lZcXxvRrSiwKrhlMuwy"

set :keep_releases,     2
set :normalize_asset_timestamps, false  # Capistrano default behavior is to 'touch' all assets files.


namespace :composer do
  desc "Copy vendors from previous release"
  task :copy_vendors, :except => { :no_release => true } do
    run "if [ -d #{previous_release}/vendor ]; then cp -a #{previous_release}/vendor #{latest_release}/vendor; fi"
  end
  task :install do
    run "sh -c 'cd #{latest_release} && curl -s http://getcomposer.org/installer | #{php_bin}'"
    run "sh -c 'cd #{release_path} && ./composer.phar install'"
  end
end

namespace :slack do
  desc "Slack comunication"
  task :update do
    if fetch(:slack, true)
      set :istr, "curl -X POST --data 'payload={\"channel\": \"#" + slack_channel + "\", \"username\": \"Deployer\", \"text\": \"Abbiamo appena aggiornato il progetto "+application+" su <http://" + domain + ">.\", \"icon_emoji\": \":rocket:\"}' https://gnvpartners.slack.com/services/hooks/incoming-webhook?token=" + slack_key
      #puts istr
      output = run_locally istr
    end
  end
end


after "deploy", "deploy:cleanup", "slack:update"
after "deploy:update_code", "composer:install"
before "composer:install", "composer:copy_vendors"
