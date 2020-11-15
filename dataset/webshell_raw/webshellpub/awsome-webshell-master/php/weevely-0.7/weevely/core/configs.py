import os

dirpath = '~/.weevely/'
rcfilepath = 'weevely.rc'
historyfilepath = 'weevely_history'

class Configs:
    
    def __init__(self):
        
        self.dirpath = os.path.expanduser( dirpath )
        
        if not os.path.exists(self.dirpath):
            os.mkdir(self.dirpath)
            
        self.historyfile = self.__historyfile()
            
    def read_rc(self, rcpath):
            
        try:
            rcfile = open(rcpath, 'r')
        except Exception, e:
            print "[!] Error opening '%s' file." % rcpath
        else:
            return [c for c in rcfile.read().split('\n') if c and c[0] != '#']
        
        return [] 

    def __historyfile(self):
        return self.dirpath + historyfilepath
            