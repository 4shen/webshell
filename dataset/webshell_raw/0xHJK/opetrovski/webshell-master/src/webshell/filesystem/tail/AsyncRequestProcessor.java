package webshell.filesystem.tail;

import java.io.File;
import java.io.PrintWriter;
import java.util.List;

import javax.servlet.AsyncContext;

import org.apache.commons.io.input.Tailer;
import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;


public class AsyncRequestProcessor implements Runnable {

	static final Log log = LogFactory.getLog(AsyncRequestProcessor.class);
	AsyncContext asyncContext;
	String tabid = null;
	TailListener listener = null;
	final Tailer tailer;
	boolean stopped = false;
	boolean paused = false;

	public AsyncRequestProcessor(AsyncContext asyncCtx) {
		this.asyncContext = asyncCtx;
		tabid = asyncContext.getRequest().getParameter("tabid");
		String cwd = asyncContext.getRequest().getParameter("cwd");
		String filename = asyncContext.getRequest().getParameter("filename");
        long delay = 1000;
        final File file = new File(cwd, filename);
        listener = new TailListener();
        tailer = new Tailer(file, listener, delay, false);
        final Thread thread = new Thread(tailer);
        thread.start();
	}

	public void terminate(){
		log.debug("AsyncRequestProcessor.terminate()");
		tailer.stop();
		stopped = true;
	}

	public void pause(){
		log.debug("AsyncRequestProcessor.pause()");
		paused = true;
	}

	public void resume(){
		log.debug("AsyncRequestProcessor.resume()");
		paused = false;
	}

	@Override
	public void run() {
		log.debug("AsyncRequestProcessor.run()");
		asyncContext.getResponse().setContentType("text/event-stream; charset=utf-8");
		
		long startmillis = System.currentTimeMillis();
		
		try {
			PrintWriter pw = asyncContext.getResponse().getWriter();
			while ( !stopped ) {
				if(!paused){
					pw.append("id: " + tabid + "\n");
					List<String> lines = listener.getLines();
					for (String line : lines) {
						pw.append("data: " + line + "\n");
					}
					pw.append("\n");
					asyncContext.getResponse().flushBuffer();
					listener.clear();
				}
				
				try {
					Thread.currentThread().sleep(1000);
				} catch (InterruptedException e) {
					// ignore
				}
			}
		} catch (Exception e) {
			log.error("AsyncRequestProcessor.run() Thread terminates. ", e);
		}
		finally{
			asyncContext.complete();
			long secs = (System.currentTimeMillis() - startmillis)/1000;
			log.debug("AsyncRequestProcessor.run() Thread terminates after " + secs + " seconds");
		}
	}
	

}