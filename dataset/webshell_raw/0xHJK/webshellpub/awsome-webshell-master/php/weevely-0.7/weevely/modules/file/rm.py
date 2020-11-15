'''
Created on 22/ago/2011

@author: norby
'''

from core.module import Module, ModuleException
from core.vector import VectorList, Vector
from core.parameters import ParametersList, Parameter as P

classname = 'Rm'
    
class Rm(Module):

    
    vectors = VectorList([
        Vector('shell.php', 'php_rmdir', """
function rmfile($dir) {
if (is_dir("$dir")) rmdir("$dir");
else { unlink("$dir"); }
}
function exists($path) {
return (file_exists("$path") || is_link("$path"));
}
function rrmdir($recurs,$dir) { 
    if($recurs=="1") {
        if (is_dir("$dir")) { 
            $objects = scandir("$dir"); 
            foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
            if (filetype($dir."/".$object) == "dir") rrmdir($recurs, $dir."/".$object); else unlink($dir."/".$object); 
            } 
            } 
            reset($objects); 
            rmdir("$dir"); 
        }
        else rmfile("$dir");
    }
    else rmfile("$dir");
}      
$recurs="%s"; $path="%s"; 
if(exists("$path")) {
rrmdir("$recurs", "$path");
if(!exists("$path")) 
    echo("OK");  
}"""),
              Vector('shell.sh', 'rm', "rm %s %s && echo OK")
        ])


    
    params = ParametersList('Remove remote file and directory', vectors,
                P(arg='rpath', help='Remote path', required=True, pos=0),
                P(arg='recursive', help='Recursion', default=False, type=bool, pos=1),
                )

    def __init__(self, modhandler, url, password):
        
        Module.__init__(self, modhandler, url, password)
        
    def run_module(self, rpath, recursive):
            
        vectors = self._get_default_vector2()
        if not vectors:
            vectors  = self.vectors.get_vectors_by_interpreters(self.modhandler.loaded_shells)
        
        for vector in vectors:
            
            response = self.__execute_payload(vector, [rpath, recursive])
            if 'OK' in response:
                self.params.set_and_check_parameters({'vector' : vector.name})
                return True
                
        recursive_output = ''
        if not recursive:
            recursive_output = ' and use \'recursive\' with unempty folders' 
        raise ModuleException(self.name,  "Delete fail, check existance and permissions%s." % (recursive_output))


    def __execute_payload(self, vector, parameters):
        
        payload = self.__prepare_payload(vector, parameters)
    
        try:    
            response = self.modhandler.load(vector.interpreter).run({0 : payload})
        except ModuleException:
            response = None
        else:
            return response

    def __prepare_payload(self, vector, parameters):
        
        rpath = parameters[0]
        recursive = parameters[1]
        
            
        if vector.interpreter == 'shell.sh' and recursive:
            recursive = '-rf'
        elif vector.interpreter == 'shell.php' and recursive:
            recursive = '1'
        else:
            recursive = ''    
        
        return vector.payloads[0] % (recursive, rpath) 
                    
    