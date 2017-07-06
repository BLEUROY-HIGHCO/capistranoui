namespace :react do
  desc <<-DESC
    Execute react cli command.
      You can override any of these defaults by setting the variables show below.
      	set :react_target_path, nil
        set :react_flags, ''
        set :react_roles, :all
    DESC
  task :build do
    on roles fetch(:react_roles) do
      within fetch(:react_target_path, release_path) do
	execute :npm, 'run build', fetch(:react_flags)
      end
    end 
  end
end

namespace :load do
  task :defaults do
    set :react_flags, ""
    set :react_roles, :all
  end
end
