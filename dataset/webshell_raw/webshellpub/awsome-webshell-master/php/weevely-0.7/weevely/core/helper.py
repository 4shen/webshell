import os


class Helper:    

    def __init__(self):

        self.modules_names_by_group={}
        self.ordered_groups = []
        
    def summary(self, only_group = None, exclude_group = None):

        output = ''
        
        for group in self.ordered_groups:
            if (not only_group and not exclude_group) or (only_group and only_group == group) or (exclude_group and exclude_group != group):
                output += '  [%s] %s\n' % (group, ', '.join(self.modules_names_by_group[group]))
            
        return output

    def summary_nogroup(self):

        return ', '.join(self.modules_classes.keys())


    def help_completion(self, module, only_name = False):
        
        matches = []
        
        for group in self.ordered_groups:
            
            for modname in self.modules_names_by_group[group]:
                    
                if(modname == module):
                    return [ modname ]
                    
                # Considering module name with or without :
                elif (modname.startswith(module[1:])) or not module:
                    
                    usage = ''
                    if not only_name:
                        usage = self.modules_classes[modname].params.summary()
                    matches.append(':%s %s' % (modname, usage))
        
        return matches
                    

    def helps(self, module):
        
        output = ''
        
        for group in self.ordered_groups:
            
            if not module:
                output += '[%s]' % group
            
            for modname in self.modules_names_by_group[group]:
                    
                # Considering module name with or without :
                if not module or (modname.startswith(module)) or (modname.startswith(module[1:])):
                    
                    descr = self.modules_classes[modname].params.module_description
                    usage = self.modules_classes[modname].params.summary()
                    help = ''
                    if module:
                       help = self.modules_classes[modname].params.help()
                    
                    passwd = ''
                    if 'generate' in modname:
                        passwd = '<password> '
                    
                    output += '\n    [%s] %s\n    Usage :%s %s%s\n    %s\n' % (modname, descr, modname, passwd, usage, help)
             
        if module and not output:
            output += '[!] Error, module \'%s\' not found' % (module) 
        
        return output 
            