'''
Created on 20/set/2011

@author: norby
'''


from core.module import Module, ModuleException
from core.vector import VectorList, Vector as V
from core.parameters import ParametersList, Parameter as P

from external.ipaddr import IPNetwork, IPAddress, summarize_address_range
from random import choice
from base64 import b64encode
from socket import gethostbyname

classname = 'Scan'
    
    
class RequestList(dict):
    
    
    def __init__(self, modhandler, port_list_path):
        
        self.modhandler = modhandler
        
        self.port_list = []
        self.ifaces = {}
        
        self.nmap_ports = []
        self.nmap_services = {}
        
        if port_list_path:
            try:
                nmap_file = open(port_list_path, 'r')
            except:
                raise ModuleException(self.name, 'Error opening \'%s\' port file' % port_list_path)
            
            for line in nmap_file.readlines():
                name, port = line[:-1].split()
                self.nmap_services[int(port)] = name
                self.nmap_ports.append(int(port))
        
        dict.__init__(self)
        
        
    def get_requests(self, howmany):
        
        to_return = {}
        requests = 0
        
        # Filling request 
        
        for ip in self:
            while self[ip]:
                if requests >= howmany:
                    break
                
                if ip not in to_return:
                    to_return[ip] = []
                
                to_return[ip].append(self[ip].pop(0))
                
                requests+=1
            
            if requests >= howmany:
                break
            
        
        # Removing empty ips
        for ip, ports in self.items():
            if not ports:
                del self[ip]
            
        return to_return        
                
        
    def add(self, net, port):
        """ First add port to duplicate for every inserted host """


        if ',' in port:
            port_ranges = port.split(',')
        else:
            port_ranges = [ port ]    
        
        for ports in port_ranges:
            self.__set_port_ranges(ports)
        
        
        # If there are available ports
        if self.port_list:
            
            if ',' in net:
                addresses = net.split(',')
            else:
                addresses = [ net ]    
            
            for addr in addresses:
                self.__set_networks(addr)
        
    def __set_port_ranges(self, given_range):

            start_port = None
            end_port = None
            

            if given_range.count('-') == 1:
                try:
                    splitted_ports = [ int(strport) for strport in given_range.split('-') if (int(strport) > 0 and int(strport) <= 65535)]
                except ValueError:
                    return None
                else:
                    if len(splitted_ports) == 2:
                        start_port = splitted_ports[0]
                        end_port = splitted_ports[1]
                        
            else:
                try:
                    int_port = int(given_range)
                except ValueError:
                    return None   
                else:
                    start_port = int_port
                    end_port = int_port
                    
            if start_port and end_port:
                self.port_list += [ p for p in range(start_port, end_port+1) if p in self.nmap_ports]
            else:
                raise ModuleException('net.scan', 'Error parsing port numbers \'%s\'' % given_range)
                    
                    

    def __get_network_from_ifaces(self, iface):
        
        if not self.ifaces:
            
            self.modhandler.set_verbosity(6)
            self.modhandler.load('net.ifaces').run()
            self.modhandler.set_verbosity()
            
            self.ifaces = self.modhandler.load('net.ifaces').ifaces
            
        
        if iface in self.ifaces.keys():
             return self.ifaces[iface]
                       
            


    def __set_networks(self, addr):
        
        
        networks = []
        
        try:
            # Parse single IP or networks
            networks.append(IPNetwork(addr))
        except ValueError:
            
            #Parse IP-IP
            if addr.count('-') == 1:
                splitted_addr = addr.split('-')
                # Only address supported
                
                try:
                    start_address = IPAddress(splitted_addr[0])
                    end_address = IPAddress(splitted_addr[1])
                except ValueError:
                    pass
                else:
                    networks += summarize_address_range(start_address, end_address)
            else:
                
                # Parse interface name
                remote_iface = self.__get_network_from_ifaces(addr)
                if remote_iface:
                    networks.append(remote_iface)  
                else:
                    # Try to resolve host
                    try:
                        networks.append(IPNetwork(gethostbyname(addr)))
                    except:
                        pass

        if not networks:       
            print '[net.scan] Warning: \'%s\' is not an IP address, network or detected interface' % ( addr)
            
        else:
            for net in networks:
                for ip in net:
                    self[str(ip)] = self.port_list[:]

    
    
class Scan(Module):

    params = ParametersList('Scan network for open ports', [],
                    P(arg='addr', help='IP address, multiple IPs (IP1,IP2,..), networks (IP/MASK or firstIP-lastIP) or interfaces (ethN)', required=True, pos=0),
                    P(arg='port', help='Port or multiple ports (PORT1,PORT2,.. or firstPORT-lastPORT)', required=True, pos=1),
                    P(arg='onlyknownports', help='Scan only known ports', default=True, type=bool),
                    P(arg='portsperreq', help='Number of scanned ports per request.', default=10, type=int)
                    )

    
    vector_scan = """
$str = base64_decode($_POST["%s"]);
foreach (explode(',', $str) as $s) {
$s2 = explode(' ', $s);
foreach( explode('|', $s2[1]) as $p) {
if($fp = fsockopen("$s2[0]", $p, $n, $e, $timeout=1)) {print("\nOPEN: $s2[0]:$p\n"); fclose($fp);}
else { print("."); }    
}}
"""

    def __init__(self, modhandler, url, password):

        self.rand_post_addr = ''.join([choice('abcdefghijklmnopqrstuvwxyz') for i in xrange(4)])
        self.rand_post_port = ''.join([choice('abcdefghijklmnopqrstuvwxyz') for i in xrange(4)])
        
        
        Module.__init__(self, modhandler, url, password)    

    
    def run_module(self, addr, port, onlyknownports, portsperreq):
                    
        port_list_path = None
        if onlyknownports:
            port_list_path = 'modules/net/external/nmap-services-tcp'
        
        self.reqlist = RequestList(self.modhandler, port_list_path)
        self.reqlist.add(addr, port)
        
        if not self.reqlist:
            raise ModuleException(self.name, 'Invalid scan range, check hosts and ports')
        
        hostnum = len(self.reqlist)
        portnum = len(self.reqlist.port_list)
        reqnum = (portnum*hostnum/portsperreq)+1

            
        self.mprint('[%s] Scanning %i ports of %i hosts using %i requests (%i connections per request)' % (self.name, portnum, hostnum, reqnum, portsperreq))
        if onlyknownports:
            known_ports_string = '[%s] Only known ports scanned.' % self.name
        
        
        while self.reqlist:
            
            reqstringarray = ''
            
            requests = self.reqlist.get_requests(portsperreq)

            for host, ports in requests.items():
                
                reqstringarray += '%s %s,' % (host, '|'.join(map(str, (ports) )))
                
            reqstringarray = '%s' % reqstringarray[:-1]
                    
            payload = self.vector_scan % (self.rand_post_addr)
            self.modhandler.load('shell.php').set_post_data({self.rand_post_addr : b64encode(reqstringarray)})
        
            response = self.modhandler.load('shell.php').run({0 : payload})
            print response


