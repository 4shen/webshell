import os
from module import ModuleException
from vector import VectorList, Vector 
from helper import Helper


class ModHandler(Helper):
    
    interpreters_priorities = [ 'shell.sh', 'shell.php' ]
    
    
    def __init__(self, url = None, password = None, path_modules = 'modules'):
        
        self.url = url
        self.password = password
        
        if not os.path.exists(path_modules):
            raise Exception( "No module directory %s found." % path_modules )
        
        self.path_modules = path_modules

        self.loaded_shells = []    
        self.modules_classes = {}
        self.modules = {}

        Helper.__init__(self)
        
        self._first_load(self.path_modules)
    
        self.verbosity=[ 3 ]
        
        self.interpreter = None
            
            
    def _first_load(self, startpath, recursive = True):
        
        for f in os.listdir(startpath):
            
            f = startpath + os.sep + f
            
            if os.path.isdir(f) and recursive:
                self._first_load(f, False)
            if os.path.isfile(f) and f.endswith('.py') and not f.endswith('__init__.py'):
                f = f[len(self.path_modules)+1:-3].replace('/','.')
                mod = __import__('%s.%s' % (self.path_modules, f), fromlist = ["*"])
                modclass = getattr(mod, mod.classname)
                self.modules_classes[f] = modclass

                parts = f.split('.')
                if parts[0] not in self.modules_names_by_group:
                    self.modules_names_by_group[parts[0]] = []
                self.modules_names_by_group[parts[0]].append(f)

            
        self.ordered_groups = self.modules_names_by_group.keys()
        self.ordered_groups.sort()                
        
    def load(self, module_name, disable_interpreter_probe=False):
        
        if not module_name in self.modules:
            if module_name not in self.modules_classes.keys():
                raise ModuleException(module_name, "Not found in path '%s'." % (self.path_modules) )
            
            self.modules[module_name]=self.modules_classes[module_name](self, self.url, self.password)
        
            if module_name.startswith('shell.'):
                self.loaded_shells.append(module_name)
        
        return self.modules[module_name]
         
                    
    def set_verbosity(self, v = None):
        
        if not v:
            if self.verbosity:
                self.verbosity.pop()
            else:
                self.verbosity = [ 3 ]
        else:
            self.verbosity.append(v)        
                
                
    def load_interpreters(self):
        
        for interpr in self.interpreters_priorities:
            
            try:
                self.load(interpr)
            except ModuleException, e:
                print '[!] [%s] %s' % (e.module, e.error)   
            else:
                self.interpreter = interpr
                return self.interpreter
            
        
        raise ModuleException('[!]', 'No remote backdoor found. Check URL and password.') 
#   
                
                