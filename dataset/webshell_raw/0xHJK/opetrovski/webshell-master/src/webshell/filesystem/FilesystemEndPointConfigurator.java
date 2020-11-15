package webshell.filesystem;

import javax.websocket.server.ServerEndpointConfig.Configurator;
	 
public class FilesystemEndPointConfigurator extends Configurator {
	 
	 private static FilesystemEndPoint fileServer = new FilesystemEndPoint();
	 
	 @Override
	 public <T> T getEndpointInstance(Class<T> endpointClass)
	   throws InstantiationException {
	  return (T)fileServer;
	 }
	}