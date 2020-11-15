package org.javaweb.server;

import java.util.Date;
import org.javaweb.server.common.Constants;

public class Response {
	
	public String getResponse(String content){
		return "HTTP/1.1 200 OK\r\n"+
			   "server: "+Constants.SYS_CONFIG_NAME+"\r\n"+
			   "Date: "+new Date()+"\r\n"+
			   "X-Powered-By-yzmm: "+Constants.SYS_CONFIG_VERSION+"\r\n"+
			   "Content-Type: text/html\r\n"+
			   "Content-Length: "+(content!=null?content.length():0)+"\r\n\r\n"+
			   content;
	}
	
	public String response(Request request){
		String content = "";
		if("/api.jsp".equals(request.getRequestURI())){
			content = new Controller().wooyun(request);
		}
		return getResponse(content);
	}

}
