require 'bundler/setup'
Bundler.require :app

class App < Sinatra::Base
    Bundler.require environment
    require 'sinatra/cookies'
    require 'pty'

    configure do
        set :root, File.expand_path('..', __FILE__)
        set :bower_path, root + '/bower_components'
        set :sprockets, Sprockets::Environment.new(root)

        set :assets_prefix, 'assets'
        set :assets_path, -> { File.join(public_folder, assets_prefix) }
        set :assets_manifest_path, -> { File.join(assets_path, 'manifest.json') }
        set :assets_compile, %w(*.png docs.js application.js application.css)

        Sprockets::Helpers.configure do |config|
            config.environment = sprockets
            config.prefix = "/#{assets_prefix}"
            config.digest = false
            config.public_path = public_folder
        end

        %w(javascript stylesheet image font).each do |type|
            sprockets.append_path root + "/assets/#{type}"
        end
        sprockets.append_path root + '/assets'
        Dir[bower_path + '/*'].each do |dir|
            sprockets.append_path dir
        end
        sprockets.append_path bower_path
    end

    configure :development do
        register Sinatra::Reloader

        use BetterErrors::Middleware
        BetterErrors.application_root = root
    end

    helpers do
        include Sprockets::Helpers
    end

    helpers Sinatra::Streaming

    get '/' do
        erb :index
    end

    get '/command' do
        cmd = params[:cmd]
        master, slave = PTY.open
        read, write = IO.pipe
        pid = spawn(cmd, :in=>read, :out=>slave)
        read.close
        slave.close
        stream do |out|
            while line = master.gets do
                out.puts(line)
            end
        end
    end

end


