package webshell.shell;

import javax.websocket.server.ServerEndpointConfig.Configurator;
	 
public class ShellEndPointConfigurator extends Configurator {
	 
	 private static ShellEndPoint shellServer = new ShellEndPoint();
	 
	 @Override
	 public <T> T getEndpointInstance(Class<T> endpointClass)
	   throws InstantiationException {
	  return (T)shellServer;
	 }
	}