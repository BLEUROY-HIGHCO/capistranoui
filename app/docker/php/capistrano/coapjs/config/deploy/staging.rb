set :stage, :staging
set :branch, 'master'
server "deploy", user: fetch(:ssh_user), roles: %w{app}
set :keep_releases, 2

if ENV['branch']
     set :branch, ENV['branch']
end
