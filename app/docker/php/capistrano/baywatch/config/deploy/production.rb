set :stage, :production
set :ssh_user, 'bleuroy'
set :branch, 'master'
set :symfony_env,  'prod'
set :deploy_to, '/var/www/vhosts/baywatch'
set :application_url, 'http://baywatch.bleuroy.fr/'
server "test.dev", user: fetch(:ssh_user), roles: %w{app db web}

if ENV['branch']
     set :branch, ENV['branch']
end
