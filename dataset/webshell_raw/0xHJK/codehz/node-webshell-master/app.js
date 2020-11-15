"use strict";
const server = require('websocket').server, http = require('http');
const pty = require('pty.js');

const socket = new server({httpServer: http.createServer().listen(process.env.WEBSHELL_PORT || 1337)});

socket.on('request', req => {
	const conn = req.accept(null, req.origin);
	const term = pty.spawn(process.env.SHELL, [], {
		name: 'xterm-color',
		cwd: process.env.HOME,
		env: process.env
	});

	term.on('data', conn.sendUTF.bind(conn));
	term.on('exit', () => {
		conn.close(1000, 'end');
	});

	conn.on('message', msg => term.write(msg.utf8Data));

	conn.on('close', term.destroy.bind(term));
});
