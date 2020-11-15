require 'pty'

spawn 'bash' do |r, w, pid|
    w.printf "dig zdns.cn\n"
    r.each do |line|
        p line
    end
end
