set :stage, :production

set :branch, 'master'
set :deploy_to, "/var/www/browl/production"
set :keep_releases, 5

if ENV['branch']
     set :branch, ENV['branch']
end