package webshell.shell;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;

import javax.websocket.RemoteEndpoint.Basic;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;


public class ShellFactory {

	private static final Log log = LogFactory.getLog(ShellFactory.class);

	static boolean isWindows = false;

	private ShellFactory() {
		// cannot be instantiated
	}


	static{
		determineOperatingSystem();
	}
	
	static public synchronized IShell createOS(final Basic basic) {
		if (ShellFactory.isWindows) {
			return new WindowsShell(basic);
		} 
		else{
			return null;//new RedHat(outstream);
		} 
	}

	public static boolean isWindows(){
		return isWindows;
	}
	
	private static void determineOperatingSystem() {
		log.debug("OperatingSystemFactory.determineOperatingSystem()");
		BufferedReader br = null;
		
		try {
	        String[] command = { "cmd.exe", "/C", "ver"};
	        ProcessBuilder pb = new ProcessBuilder(command);
	        Process process = null;

	        try {
	        	process = pb.start();
	        } catch (Exception ex) {
	            //log.error("Failed to execute cmd.exe.",ex);
	        	isWindows = false;
	        	return;
	        }
	        
	        OutputStream os = process.getOutputStream();
	        PrintWriter pw = new PrintWriter(new BufferedWriter(new OutputStreamWriter(os)));
	        InputStream is = process.getInputStream();
			br = new BufferedReader(new InputStreamReader(is));
			String line;
			while ((line = br.readLine()) != null) {
				if( line.indexOf("Microsoft") >= 0){
					isWindows = true;
					break;
				}
			}
	        
	        pw.close();
	        try {
	            int returnCode = process.waitFor();
	        } catch (InterruptedException ex) {
	        }
			
		} catch (Exception e) {
			isWindows = false;
			return;
		} finally {
			if (br != null) {
				try {
					br.close();
				} catch (IOException e) {
					log.error(e);
				}
			}
		}
	}

}
