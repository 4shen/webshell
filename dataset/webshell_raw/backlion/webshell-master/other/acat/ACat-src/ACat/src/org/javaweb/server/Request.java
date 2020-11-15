package org.javaweb.server;

import java.io.File;
import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.net.URLDecoder;
import java.util.LinkedHashMap;
import java.util.Map;

import javax.servlet.HttpServletRequest;

public class Request implements HttpServletRequest {

	private String method;
	private String queryString;
	private String requstURI;
	private String host;
	private Map<String, Object> formContent = new LinkedHashMap<String, Object>();
	private Map<String, Object> header = new LinkedHashMap<String, Object>();
	private Map<String, String> parameterMap = new LinkedHashMap<String, String>();
	
	@Override
	public String getParameter(String name) {
		if(parameterMap.containsKey(name)){
			return parameterMap.get(name);
		}
		return null;
	}
	
	@Override
	public String getRealPath(String path) {
		File f = new File("");
		return new File(f.getAbsoluteFile(),path).toString();
	}
	
	@Override
    public String getContentType() {
		if(header.get("Content-Type")!=null){
			return header.get("Content-Type").toString();
		}else{
			new RuntimeException("Content-Type not allowed null");
		}
		return null;
    }
	
	@Override
	public String getMethod() {
		return method;
	}

	public void setMethod(String method) {
		this.method = method;
	}
	
	@Override
	public String getQueryString() {
		return queryString;
	}

	public void setQueryString(String queryString) {
		this.queryString = queryString;
	}
	
	@Override
	public String getRequestURI() {
		return requstURI;
	}

	public void setRequstURI(String requstURI) {
		this.requstURI = requstURI;
	}

	@Override
	public String getHost() {
		return host;
	}

	public void setHost(String host) {
		this.host = host;
	}

	public Map<String, Object> getFormContent() {
		return formContent;
	}

	public void setFormContent(Map<String, Object> formContent) {
		this.formContent = formContent;
	}

	@Override
	public Map<String, Object> getHeader() {
		return header;
	}

	public void setHeader(Map<String, Object> header) {
		this.header = header;
	}

	@Override
	public Map<String, String> getParameterMap() {
		return parameterMap;
	}

	public void setParameterMap(Map<String, String> parameterMap) {
		this.parameterMap = parameterMap;
	}

	private void parserHeader(String req) {
		String[] str = req.split("\r\n");//用换行符切开请求头
		for (int i = 1; i < str.length; i++) {
			if (str[i].indexOf(" ") != -1) {
				String k = str[i].substring(0, str[i].indexOf(" ") - 1);//请求头的key
				String v = str[i].substring(str[i].indexOf(" ") + 1,str[i].length());//请求头对应的value
				if (k != null) {
					header.put(k, v);//把key和value保存到header
				}
			} else {
				return ;
			}
		}
	}

	private void parserGET() {
		requstURI = queryString.substring(0,queryString.indexOf("?")!=-1?queryString.indexOf("?"):queryString.length());
		if (getQueryString().indexOf("?") != -1) {
			String u = getQueryString().substring(getQueryString().indexOf("?") + 1,getQueryString().length());
			for (String s : u.split("&")) {
				String[] xb = s.split("=");
				if (xb.length > 0) {
					try {
						parameterMap.put(xb[0], xb.length == 2 ? URLDecoder.decode(xb[1],"ISO-8859-1") : null);
					} catch (UnsupportedEncodingException e) {
						e.printStackTrace();
					}
				}
			}
		}
	}
	
	public void getRequestMap(String str) {
		if(str!=null){
			try {
				str = URLDecoder.decode(str,"ISO-8859-1");
			} catch (UnsupportedEncodingException e) {
				e.printStackTrace();
			}
		}
        for (String s : str.split("&")) {
            if (s.indexOf("=") != -1) {
                parameterMap.put(s.substring(0, s.indexOf("=")), s.substring(s.indexOf("=") + 1, s.length()));
            } else {
            	parameterMap.put(s, null);
            }
        }
    }
	
	/**
	 * 解析POST请求
	 * @param req
	 * @throws IOException
	 */
	private void parserPOST(String req) throws IOException {
		String[] str = req.split("\r\n");
		int len = str[str.length-1].length();//当客户端没传Content-Length的时候,默认取POST的最后一行。
		if(header.get("Content-Length")!=null){
			len = Integer.parseInt((""+header.get("Content-Length")).trim());
		}
		getRequestMap(req.substring(req.length()-len,req.length()));
	}

	private void parserHttpRequest(String req) throws Exception{
		parserHeader(req);//解析请求头
		parserGET();//解析RequestURI的参数
		if("POST".equals(this.getMethod())){
			parserPOST(req);
		}
	}
	
	/**
	 * 简单的解析Request请求
	 * @param req
	 * @return
	 */
	public Request parserRequest(String req) {
		try {
			String[] str = req.split("\r\n");
			String[] p = str[0].split("\\s");
			if(p.length==3){
				setMethod(p[0]);
				setQueryString(p[1]);
				parserHttpRequest(req);
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
		return this;
	}
}
