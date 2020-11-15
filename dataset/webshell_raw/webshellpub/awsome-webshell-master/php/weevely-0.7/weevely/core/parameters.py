import ast, types


class Parameter:
    
    def __init__(self, arg, help='', required = False, pos=-1, default = None, choices = [], type = None, mutual_exclusion=[], passed = True, hidden=False):
        self.arg = arg
        self.help = help
        self.required = required
        self.default = default
        self.choices = choices
        self.type = type
        self.pos = pos
        self.mutual_exclusion = mutual_exclusion
        
        self.passed = passed
        
        self.value = default
        self.hidden = hidden
        
        
    def set_value(self, value):
        value = self.value
    
    
    def __repr__(self):
        
        choices = ''
        if self.choices:
            choices = '(%s)' % (', '.join(self.choices))
            
        type = ''
        if self.type != None:
            type = 'Type: %s' % repr(self.type.__name__)                    
            if 'bool' in str(self.type):
                type += ' (True, False)'
            
            
        value = ''
        if self.value != None:
            value = str(self.value)
        
        exclusions = ''
        if self.mutual_exclusion:
            exclusions = '(Mutual exclusions: \'%s\')' % '\', \''.join(self.mutual_exclusion)
        
            
        tabs = '\t'*(3-((len(self.arg)+len(value) + 4)/8))
            
        return '%s = %s %s %s %s %s %s' % (self.arg, value, tabs, self.help, choices, type, exclusions)
        
class ParametersList:
    
    def __init__(self, module_description, vectors, *parameters):
        
        self.module_description = module_description
        self.parameters = list(parameters)
        self.parameters_names = [par.arg for par in self.parameters]
        
        self.vectors = vectors
        if vectors:
            self.parameters.append(Parameter(arg='vector', help='Specify vector', choices = vectors.get_names_list(), passed = False))
            self.parameters_names.append('vector')
 
    def summary(self, print_value = False, print_all_args = False):
        
        output=''
        
        
        for parameter in self.parameters:
        
            value=''
            
            # Skip vectors and hidden
            if not print_all_args and (parameter.arg == 'vector' or parameter.hidden):
                continue
            
            if print_value and parameter.value:
                value='=%s' % parameter.value
            
            if parameter.required:
                formatarg = '<%s%s>' 
            else:
                formatarg = '[%s%s]' 
                
            output += '%s ' % (formatarg % ( parameter.arg, value))                
        
        return output
    
    def help(self):
        
        output = ''
        for parameter in self.parameters:
            
            if parameter.hidden:
                continue
            output += '\n%s' % (parameter)
        return output

    def __print_namepos(self,s):
        try: 
            int(s)
            return 'at position %s' % s
        except ValueError:
            return 'in \'%s\'' % s
        
        
    def __repr__(self):
        
        return self.summary() + self.help()
        
        
    def set_and_check_parameters(self, arglist_argdict, oneshot = False):
        
        check=True
        
        oneshot_parameters = {}

        if isinstance(arglist_argdict, types.DictionaryType):
            args = arglist_argdict
        elif isinstance(arglist_argdict, types.ListType):
            args = self.format_arglist(arglist_argdict)
        
        
        for namepos in args:
            param = self.__get_parameter(namepos)
        
            if param:
                value = args[namepos]
                
                #print value, namepos, len(value), bool(value)
                
                if value:

                    if param.choices and (value not in param.choices):
                        print '[!] Error, allowed values %s: \'%s\'' % (self.__print_namepos(namepos), '\', \''.join(param.choices))             
                        check=False
                    
                    if param.type:
                        
                        # Check if passed parameter is a string, in case conver
                        if isinstance(value,str):
                            try:
                                value = ast.literal_eval(value)
                            #except ValueError: It may cause various exception types
                            except Exception:
                                print '[!] Error, allowed type %s: %s' % (self.__print_namepos(namepos), repr(param.type))             
                                check=False
                                
                        # Check instance
                        if not isinstance(value, param.type):
                            print '[!] Error, allowed type %s: %s' % (self.__print_namepos(namepos), repr(param.type)) 
                            check=False
                        
                    if param.mutual_exclusion:
                        
                        for excluded in param.mutual_exclusion:
                            if self.get_parameter_value(excluded):
                                print '[!] Error, parameters \'%s\' and \'%s\' are mutually exclusive' % (param.arg, excluded) 
                                check=False
                
                else:
                    # Not value, set value to null or default
                    if param.default:
                        value = param.default
                    else:
                        value = ''
                
            else:
                print '[!] Error, invalid parameter %s' % (self.__print_namepos(namepos))  
                
                check=False
                
            if check:
                
                # Saved only if not oneshot (set with :set) 
                # or if not-passed vector (anyway they will be not saved)
                
                if not oneshot or not param.passed:
                    param.value = value    
        
                oneshot_parameters[param.arg] = value
                
                
        if not check:
            print '[!] Usage: %s' % self
        
        return check, oneshot_parameters
                
                
                
    def __get_parameter(self, namepos):
        
        for par in self.parameters:
            if namepos == par.arg or namepos == par.pos:
                return par
        
        
    def get_parameter_value(self, namepos):
        
        par = self.__get_parameter(namepos)
        if par and par.value:
            return par.value
        return None

    def get_parameter_choices(self, namepos):
        
        par = self.__get_parameter(namepos)
        if par and par.choices:
            return par.choices
        return None

                
    def get_parameters_list(self, argdict):
        
        args_list =  []
        
        error_required = []
        
        for param in self.parameters:
            
            best_value = None
            
            oneshot_value = None
            if param.arg in argdict:
                oneshot_value = argdict[param.arg]
            perm_value = param.value
            
            if oneshot_value != None:
                best_value = oneshot_value
            elif perm_value != None:
                best_value = perm_value
            else:
                if param.required:
                   error_required.append(param.arg)
                   continue               
            
            if param.passed:
                args_list.append(best_value)
            
        if error_required:
            print '[!] Error, required parameters: \'%s\'\n[!] Usage: %s' % ('\', \''.join(error_required), self)
            return False, args_list
        
        return True, args_list
            
    
    def format_arglist(self, module_arglist):
        
        arguments = {}
        pos = 0
        for arg in module_arglist:
            
            if '=' in arg and arg.split('=',1)[0] in self.parameters_names:
                name, value = arg.split('=',1)
            else:
                name = pos
                value = arg
                
            arguments[name] = value
            pos+=1
        
        return arguments