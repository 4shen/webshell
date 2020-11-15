#require "rubygems"
#require "bundler/setup"
require 'sinatra/base'

class Server < Sinatra::Base
  get '/' do
    @url = "http://XXXXXX"
    erb :index
  end
end
