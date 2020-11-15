package javax.servlet;

import java.util.Map;

public interface HttpServletRequest{

	public String getParameter(String name);

	public String getRealPath(String path);

	public String getContentType();

	public String getMethod();

	public String getQueryString();

	public String getRequestURI();

	public String getHost();

	public Map<String, Object> getHeader();

	public Map<String, String> getParameterMap();
	
}