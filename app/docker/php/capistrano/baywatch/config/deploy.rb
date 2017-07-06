lock "3.8.2"

set :application, "baywatch"
set :repo_url, "git@github.com:BLEUROY-HIGHCO/baywatch.git"

set :format, :airbrussh
set :deployer, fetch(:deployer, 'astro')

set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --optimize-autoloader'

set :composer_roles, :app
set :composer_working_dir, -> { fetch(:release_path) }
set :composer_dump_autoload_flags, '--optimize'
set :composer_download_url, "https://getcomposer.org/installer"

append :linked_files, "app/config/parameters.yml"

append :linked_dirs, "var/logs", "var/sessions", "var/jwt", "web/img"

set :keep_releases, 3

set :permission_method, :acl
set :file_permissions_users, ["www-data"]
set :file_permissions_paths, ["var/cache", "var/logs", "var/sessions"]

set :yarn_target_path, -> { release_path.join('react') }
set :yarn_flags, '--silent --no-progress'
set :yarn_roles, :all
set :yarn_env_variables, {}  

set :react_target_path, -> { release_path.join('react') }

set :slackistrano, {
    channel: '#redmine',
    webhook: 'https://hooks.slack.com/services/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'
}

namespace :symfony do
    namespace :doctrine do
        task :migrate do
          on roles(:db) do
            symfony_console('doctrine:migrations:migrate', '--no-interaction --allow-no-migration')
          end
        end
    end
    namespace :elasticsearch do
      task :bulk do
        on roles(:db) do
          symfony_console('fos:elastica:populate', '--no-interaction')
        end
      end
    end
end

after 'deploy:starting', 'composer:install_executable'
before 'deploy:updated', 'deploy:set_permissions:acl'
after 'deploy:set_permissions:acl', 'composer:install'
after 'composer:install', 'symfony:build_bootstrap'
after 'symfony:build_bootstrap', 'symfony:doctrine:migrate'
after 'symfony:doctrine:migrate', 'symfony:elasticsearch:bulk'
after 'symfony:elasticsearch:bulk', 'symfony:assets:install'
after 'yarn:install', 'react:build'
