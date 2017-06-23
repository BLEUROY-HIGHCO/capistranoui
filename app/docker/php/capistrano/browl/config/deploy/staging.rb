set :stage, :staging
set :branch, 'master'
set :deploy_to, "/var/www/browl/staging"
set :keep_releases, 2

if ENV['branch']
     set :branch, ENV['branch']
end
