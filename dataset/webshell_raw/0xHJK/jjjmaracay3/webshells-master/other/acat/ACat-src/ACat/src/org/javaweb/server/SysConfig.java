package org.javaweb.server;

import java.io.InputStream;
import java.util.Properties;
import java.util.logging.Logger;

public class SysConfig {

	private static final Logger logger = Logger.getLogger("info");
	
	public Properties getProperties() {
		Properties p = new Properties();
        try {
        	InputStream is = this.getClass().getResourceAsStream("/server.properties");
            p.load(is);
		} catch (Exception e) {
			logger.info(e.toString());
		}
        return p;
    }
}
