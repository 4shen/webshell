package org.javaweb.server;

import java.io.File;
import java.util.Properties;

public class ServerInfo {

	private int port;
	private int maxSize;
	private String serverPath;

	public ServerInfo() {
		super();
		SysConfig s = new SysConfig();
		setServerPath(new File("").getAbsolutePath());
		Properties p = s.getProperties();
		setPort(Integer.parseInt(p.getProperty("server.port")));
		setMaxSize(Integer.parseInt(p.getProperty("request.maxsize")));
	}

	public int getPort() {
		return port;
	}

	public void setPort(int port) {
		this.port = port;
	}

	public int getMaxSize() {
		return maxSize;
	}

	public void setMaxSize(int maxSize) {
		this.maxSize = maxSize;
	}

	public String getServerPath() {
		return serverPath;
	}

	public void setServerPath(String serverPath) {
		this.serverPath = serverPath;
	}

}
