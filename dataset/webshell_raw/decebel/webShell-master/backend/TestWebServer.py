from BaseHTTPServer import BaseHTTPRequestHandler
import urlparse
import cgi
import time

class GetHandler(BaseHTTPRequestHandler):    

    def do_OPTIONS(self):           
        self.send_response(200, "ok")       
        #self.send_header('Access-Control-Allow-Origin', '*')                
        #self.send_header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept')
        #self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        #self.send_header("Access-Control-Allow-Headers", "X-Requested-With")        

    def do_POST(self):
        # Parse the form data posted
        form = cgi.FieldStorage(
            fp=self.rfile, 
            headers=self.headers,
            environ={'REQUEST_METHOD':'POST',
                     'CONTENT_TYPE':self.headers['Content-Type'],
                     })

        # Begin the response
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept")        
        self.send_response(200)
        self.end_headers()
        self.wfile.write('Client: %s\n' % str(self.client_address))
        self.wfile.write('User-agent: %s\n' % str(self.headers['user-agent']))
        self.wfile.write('Path: %s\n' % self.path)
        self.wfile.write('Form data:\n')
        
        self.wfile.write("bye now")
        
        return
        if not form:
            self.wfile.write("bye now")

        # Echo back information about what was posted in the form
        for field in form.keys():
            field_item = form[field]
            if field_item.filename:
                # The field contains an uploaded file
                file_data = field_item.file.read()
                file_len = len(file_data)
                del file_data
                self.wfile.write('\tUploaded %s as "%s" (%d bytes)\n' % \
                        (field, field_item.filename, file_len))
            else:
                # Regular form value
                self.wfile.write('\t%s=%s\n' % (field, form[field].value))
        return
    
    def do_GET(self):
        parsed_path = urlparse.urlparse(self.path)
        message_parts = [
                'CLIENT VALUES:',
                'client_address=%s (%s)' % (self.client_address,
                                            self.address_string()),
                'command=%s' % self.command,
                'path=%s' % self.path,
                'real path=%s' % parsed_path.path,
                'query=%s' % parsed_path.query,
                'request_version=%s' % self.request_version,
                '',
                'SERVER VALUES:',
                'server_version=%s' % self.server_version,
                'sys_version=%s' % self.sys_version,
                'protocol_version=%s' % self.protocol_version,
                '',
                'HEADERS RECEIVED:',
                ]
        #time.sleep(10)
        for name, value in sorted(self.headers.items()):
            message_parts.append('%s=%s' % (name, value.rstrip()))
        message_parts.append('')
        message = '\r\n'.join(message_parts)
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept")    
        self.end_headers()
        self.wfile.write("successfully executed command")
        return

if __name__ == '__main__':
    from BaseHTTPServer import HTTPServer
    server = HTTPServer(('localhost', 8080), GetHandler)
    print 'Starting server, use <Ctrl-C> to stop'
    server.serve_forever()