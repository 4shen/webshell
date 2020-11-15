package webshell.filesystem.tail;

import java.io.IOException;
import java.util.HashMap;
import java.util.concurrent.ThreadPoolExecutor;

import javax.servlet.AsyncContext;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

@WebServlet(urlPatterns = "/tail", asyncSupported = true)
public class AsyncLongRunningServlet extends HttpServlet {
	private static final long serialVersionUID = 1L;
	private static final Log log = LogFactory.getLog(AsyncLongRunningServlet.class);
	private static HashMap<String,AsyncRequestProcessor> processMap = new HashMap<String,AsyncRequestProcessor>();
	
	protected void doGet(HttpServletRequest request,
			HttpServletResponse response) throws ServletException, IOException {
		log.debug(request.getParameter("cmd"));
		String cmd = request.getParameter("cmd");
		String tabid = request.getParameter("tabid");

		if( "start".equals(cmd)){
			request.setAttribute("org.apache.catalina.ASYNC_SUPPORTED", true);
	
			AsyncContext asyncCtx = request.startAsync();
			asyncCtx.addListener(new AsyncTailListener());
			asyncCtx.setTimeout(Long.MAX_VALUE);
			
			ThreadPoolExecutor executor = (ThreadPoolExecutor) request
					.getServletContext().getAttribute("executor");
	
			AsyncRequestProcessor processor = new AsyncRequestProcessor(asyncCtx);
			processMap.put(tabid,processor);
			executor.execute(processor);
		}
		else if( "stop".equals(cmd)){
			AsyncRequestProcessor processor = processMap.get(tabid);
			processor.terminate();
			processMap.remove(tabid);
		}
		else if( "pause".equals(cmd)){
			AsyncRequestProcessor processor = processMap.get(tabid);
			processor.pause();
		}
		else if( "resume".equals(cmd)){
			AsyncRequestProcessor processor = processMap.get(tabid);
			processor.resume();
		}
	}

}