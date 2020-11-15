package webshell.filesystem;

import java.io.IOException;
import java.net.URLDecoder;
import java.util.HashMap;
import java.util.Map;

import javax.websocket.OnClose;
import javax.websocket.OnError;
import javax.websocket.OnMessage;
import javax.websocket.OnOpen;
import javax.websocket.Session;
import javax.websocket.server.ServerEndpoint;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

import webshell.shell.IShell;
import webshell.shell.ShellFactory;
import webshell.util.JsonResult;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.JsonMappingException;
import com.fasterxml.jackson.databind.ObjectMapper;


@ServerEndpoint(value="/file_wsocket", configurator=FilesystemEndPointConfigurator.class)
public class FilesystemEndPoint {
    
    private static final Log log = LogFactory.getLog(FilesystemEndPoint.class);
	IShell os = null;
 
    /**
     * Callback hook for Connection open events. This method will be invoked when a
     * client requests for a WebSocket connection.
     * @param userSession the userSession which is opened.
     */
    @OnOpen
    public void onOpen(Session session) {
		log.debug("SessionId: " + session.getId());
		if( os == null ){
			os = ShellFactory.createOS(null);
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
		 if( os != null ) os.terminate();
    }
     
    /**
     * Callback hook for Message Events. This method will be invoked when a client
     * send a message.
     * @param message The text message
     * @param userSession The session of the client
     */
    @OnMessage
    public String onMessage(String message, Session session) {
        log.debug("SessionId: " + session.getId() + " message: "+ message);

        if (os == null) {
            log.error("Cannot establish operating system access.");
            return "Cannot establish operating system access";
        }

		JsonResult jsonResult = new JsonResult();
		jsonResult.begin();
		Map<String, Object> jsonMap = new HashMap<String, Object>();

		try {
			ObjectMapper m = new ObjectMapper();
//			m.configure(JsonGenerator.Feature.ESCAPE_NON_ASCII, true);
			jsonMap = m.readValue(message,
					new TypeReference<HashMap<String, Object>>() {
					});

			String scope = (String)jsonMap.get("scope");
			String command = URLDecoder.decode((String)jsonMap.get("command"));
			log.debug("scope: " + scope + " command: " + command);
			jsonResult.add("scope", scope);
			jsonResult.add("command", command);
			
			try {
				
				if("load_scripts".equals(command) || "load_commands".equals(command)){
					String response = os.execute(command, null, null, null);
					jsonResult.add("response", response);
				}
				else{
					String filename = (String)jsonMap.get("filename");
					String cwd = URLDecoder.decode((String)jsonMap.get("cwd"), "UTF-8");
					String filecontent = URLDecoder.decode((String)jsonMap.get("filecontent"), "UTF-8");
					String response = os.execute(command, filename, cwd, filecontent);
					jsonResult.add("filename", (String)jsonMap.get("filename"));
					String filetype = determineFiletype((String)jsonMap.get("filename"), response);
					jsonResult.add("filetype", filetype);
					jsonResult.add("cwd", (String)jsonMap.get("cwd"));
					jsonResult.add("response", response);
					jsonResult.add("status", "ok");
				}
			} catch (Exception e) {
				log.error("Failed to execute command " + command, e);
				jsonResult.add("scope", "status");
				jsonResult.add("status", e.getMessage());
			}
		} catch (JsonMappingException e) {
			log.error("Failed to parse JSON.", e);
			jsonResult.add("scope", "status");
			jsonResult.add("status", "JSON: " + e.getMessage());
		} catch (IOException e) {
			log.error("Failed to parse JSON.", e);
			jsonResult.add("scope", "status");
			jsonResult.add("status", "JSON: " + e.getMessage());
		}
		
		jsonResult.end();
		return jsonResult.getJson();
    }
    
    @OnError
    public void onError(Throwable t) throws Throwable {
        log.error("", t);
    }

	private String determineFiletype(String filename, String content){
		String filetype = "text";
		filename = filename.toLowerCase();
		
		if( filename.indexOf(".") > 0){
			if( filename.endsWith("xml") ){
				filetype = "xml";
			}
			else if( filename.endsWith("sql") ){
				filetype = "sql";
			}
			else if( filename.endsWith("java") ){
				filetype = "java";
			}
			else if( filename.endsWith("sh") ){
				filetype = "sh";
			}
			else if( filename.endsWith("js") ){
				filetype = "javascript";
			}
			else if( filename.endsWith("bat") || filename.endsWith("cmd")){
				filetype = "batchfile";
			}
			else if( filename.endsWith("css") ){
				filetype = "css";
			}
			else if( filename.endsWith("htm") || filename.endsWith("html") ){
				filetype = "html";
			}
			else if( filename.endsWith("pl") ){
				filetype = "perl";
			}
			else if( filename.endsWith("jsp") ){
				filetype = "jsp";
			}
			else if( filename.endsWith("py") ){
				filetype = "python";
			}
			else if( filename.endsWith("php") ){
				filetype = "php";
			}
			else if( filename.endsWith("conf") || filename.endsWith("properties")){
				filetype = "properties";
			}
		}
		else if( content.indexOf("bin/sh") >= 0 ){
			filetype = "sh";
		}
		else if( content.indexOf("<") >= 0 && content.indexOf(">") >= 0 ){
			filetype = "xml";
		}
		else if( content.indexOf("package") >= 0 || content.indexOf("import") >= 0 || content.indexOf("class") >= 0 ){
			filetype = "java";
		}

		return filetype;
	}

}