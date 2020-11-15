require "rubygems"
require "bundler/setup"

ROOT                = File.expand_path("../", __FILE__)
JRUBY_JAR_LOCATION  = "http://jruby.org.s3.amazonaws.com/downloads/1.6.7/jruby-complete-1.6.7.jar"

require 'fileutils'
require 'net/http'
require 'json'

desc "Download dependencies"
task :init do
  vendor = File.join(ROOT, "vendor")
  mkdir_p(vendor)
  sh("curl -L #{JRUBY_JAR_LOCATION} > #{vendor}/jruby-complete.jar") unless File.exists?("#{vendor}/jruby-complete.jar")

  gems = [
    "swt",
    "sinatra",
    "rack",
    "rack-protection",
    "tilt"
  ]

  gems.each do |gem_name|
    puts "fetching #{gem_name}"
    data = JSON.parse(Net::HTTP.get(URI.parse("http://rubygems.org/api/v1/gems/#{gem_name}.json")))
    gem_file = "#{vendor}/#{gem_name}-#{data["version"]}.gem"
    sh("curl -L #{data["gem_uri"]} > #{gem_file}")
    gem_dir = "#{vendor}/#{gem_name}"
    rm_rf(gem_dir)
    mkdir_p(gem_dir)
    sh("tar xv -C #{gem_dir} -f #{gem_file}")
    rm(gem_file)
    sh("tar xzv -C #{gem_dir} -f #{gem_dir}/data.tar.gz")
    rm("#{gem_dir}/data.tar.gz")
    rm("#{gem_dir}/metadata.gz")
  end
end
