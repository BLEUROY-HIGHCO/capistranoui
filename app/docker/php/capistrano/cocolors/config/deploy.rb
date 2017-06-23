# config valid only for current version of Capistrano
lock "3.8.2"

set :application, "cocolors"
set :repo_url, "https://github.com/BLEUROY-HIGHCO/emoji-git-finder.git"
set :branch, "master"
set :ssh_user, 'www-data'
server "deploy", user: fetch(:ssh_user), roles: %w{app}, password: 'docker'

set :format, :airbrussh

set :format_options, command_output: true, log_file: "log/capistrano.log", color: :auto, truncate: :auto

set :keep_releases, 5
