# start by requiring the standard library PTY
require 'pty'

master, slave = PTY.open
read, write = IO.pipe
pid = spawn("bash", :in=>read, :out=>slave)
#read.close     # we dont need the read
#slave.close    # or the slave

write.puts "ls -G / "
# output the response from factor
while s = master.gets do
    p s
    write.puts 'whoami'
end

write.printf "ping zdns.cn\n"
while s = master.gets do
    p s
end


