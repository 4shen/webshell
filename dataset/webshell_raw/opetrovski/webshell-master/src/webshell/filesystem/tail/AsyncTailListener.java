package webshell.filesystem.tail;

import java.io.IOException;
import java.io.PrintWriter;

import javax.servlet.AsyncEvent;
import javax.servlet.AsyncListener;
import javax.servlet.ServletResponse;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

public class AsyncTailListener implements AsyncListener {

	private static final Log log = LogFactory.getLog(AsyncTailListener.class);
	
	@Override
	public void onComplete(AsyncEvent asyncEvent) throws IOException {
		log.debug("AppAsyncListener onComplete");
	}

	@Override
	public void onError(AsyncEvent asyncEvent) throws IOException {
		log.debug("AppAsyncListener onError");
	}

	@Override
	public void onStartAsync(AsyncEvent asyncEvent) throws IOException {
		log.debug("AppAsyncListener onStartAsync");
		//we can log the event here
	}

	@Override
	public void onTimeout(AsyncEvent asyncEvent) throws IOException {
		log.debug("AppAsyncListener onTimeout");
		//we can send appropriate response to client
		ServletResponse response = asyncEvent.getAsyncContext().getResponse();
		PrintWriter out = response.getWriter();
		out.write("TimeOut");
	}

}