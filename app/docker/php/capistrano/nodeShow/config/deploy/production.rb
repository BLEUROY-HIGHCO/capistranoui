set :stage, :production
set :branch, 'master'
set :deploy_to, "/var/www/nodeShow/production"

if ENV['branch']
     set :branch, ENV['branch']
end
