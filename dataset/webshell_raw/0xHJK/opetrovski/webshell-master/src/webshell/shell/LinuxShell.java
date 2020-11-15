package webshell.shell;

import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.io.Writer;
import java.net.URLEncoder;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

/**
 * Implements RedHat specific operations.
 * 
 */
class LinuxShell implements IShell {

	private static final Log log = LogFactory.getLog(LinuxShell.class);

	PrintWriter pw = null;
	Process process = null;
	boolean canExecuteShellCommands = true;

	public LinuxShell(final Writer writer){
		
		if( writer == null ){
			canExecuteShellCommands = false;
			return;
		}

        String[] command = { "/bin/sh", "-i"};
        ProcessBuilder pb = new ProcessBuilder(command);

        try {
            process = pb.start();
        } catch (IOException ex) {
            System.err.println(ex);
			System.exit(1);
        }
        OutputStream os = process.getOutputStream();
        pw = new PrintWriter(new BufferedWriter(new OutputStreamWriter(os)));

        final InputStream is = process.getInputStream();
        new Thread(new Runnable() {
            public void run() {
                try {
					BufferedReader br = new BufferedReader(new InputStreamReader(is));
					String line;
					while ((line = br.readLine()) != null) {
						writer.write(line);
					}
                } catch (java.io.IOException e) {
                }
            }
        }).start();

       final InputStream es = process.getErrorStream();
        new Thread(new Runnable() {
            public void run() {
                try {
					BufferedReader br = new BufferedReader(new InputStreamReader(es));
					String line;
					while ((line = br.readLine()) != null) {
						writer.write(line + System.lineSeparator());
					}
                } catch (java.io.IOException e) {
                }
            }
        }).start();
	}
	
	
    public void execute(String command) throws Exception{
        log.debug("RedHat.execute() " + command);
        
        if( canExecuteShellCommands ){        
			pw.println(command);
			pw.flush();
			
        }
    }
    
    public void terminate(){
    	if( canExecuteShellCommands ){  
	        pw.close();
	        try {
	            int returnCode = process.waitFor();
	        } catch (InterruptedException ex) {
	        }
    	}
    }


	@Override
	public String execute(String command, String filename, String cwd,
			String filecontent) throws Exception {
		log.debug("RedHat.executeOnFilesystem() " + command + "  " + cwd + File.separator + filename);
		StringBuilder result = new StringBuilder();
		
		if( "chdir".equals(command) ){
			File f = new File(cwd + File.separator + filename);
			String dirPath = f.getAbsolutePath();
			
			String newcwd = dirPath;
			while( newcwd.endsWith(File.separator + "." )){
				newcwd = newcwd.substring(0, newcwd.lastIndexOf(File.separator + "."));
			}
			
			if( "..".equals(filename) ){
				newcwd = f.getParentFile().getParentFile().getAbsolutePath();
			}
			
			result.append("[");
			result.append("{\"name\":\"");
			result.append(URLEncoder.encode(newcwd, "UTF-8"));
			result.append("\",\"type\":\"folder\"},");
			result.append("{\"name\":\"");
			result.append("..");
			result.append("\",\"type\":\"folder\"}");
			
			ArrayList<File> files = new ArrayList<File>(Arrays.asList(f.listFiles()));
			for (File file : files) {
				result.append(","); 
				result.append("{\"name\":\"");
				result.append(file.getName());
				if(file.isDirectory()){
					result.append("\",\"type\":\"folder\"}");
				}
				else{
					result.append("\",\"type\":\"file\"}");
				}
			}
			result.append("]");
		}
		else if( "open".equals(command)){
	        BufferedReader br = new BufferedReader(new InputStreamReader(new FileInputStream(cwd + File.separator + filename), "UTF-8"));
		    try {
		        String line = br.readLine();
		        while (line != null) {
		        	result.append(line);//URLEncoder.encode(line));
		        	result.append("\r\n");
		            line = br.readLine();
		        }
		    } finally {
		        br.close();
		    }
		}
		else if( "save".equals(command)){
			PrintWriter writer = null;
			try {
				writer = new PrintWriter(cwd + File.separator + filename, "UTF-8");
				writer.print(filecontent);
		    } finally {
				if( writer != null ) writer.close();
		    }
		}
		else if( "info".equals(command)){
			File f = new File(cwd + File.separator + filename);
//			f.lastModified()
//			f.getTotalSpace()
//			f.getFreeSpace()
//			f.getUsableSpace()
			
			String size = "";
			if( f.length() < 1024 ){
				size = f.length() + " Byte";
			}
			else if( f.length() < 1024000 ){
				long s = f.length() / 1024;
				size = s + " KB";
			}
			else{
				long s = f.length() / 1048576; // (1024 * 1024)
				size = s + " MB";
			}
			
			SimpleDateFormat sdf = new SimpleDateFormat("MM/dd/yyyy HH:mm:ss");
			
			result.append("{\"name\":\""+ f.getName() +"\",");
//			result.append("\"cwd\":\""+ URLEncoder.encode(cwd, "UTF-8") +"\",");
			result.append("\"dir\":\""+ f.isDirectory() +"\",");
			result.append("\"hidden\":\""+ f.isHidden() +"\",");
			result.append("\"exec\":\""+ f.canExecute() +"\",");
			result.append("\"read\":\""+ f.canRead() +"\",");
			result.append("\"write\":\""+ f.canWrite() +"\",");
			result.append("\"modified\":\""+ sdf.format(f.lastModified()) +"\",");
			result.append("\"size\":\""+ size +"\"}");
		}
		else if( "purge".equals(command)){
			PrintWriter writer = null;
			try {
				writer = new PrintWriter(cwd + File.separator + filename, "UTF-8");
				writer.print("");
				writer.flush();
		    } finally {
				if( writer != null ) writer.close();
		    }
		}
		else if( "delete".equals(command)){
			File f = new File(cwd + File.separator + filename);
			f.delete();
		}
		else{
			throw new Exception("Unsupported functionality.");
		}
		
		return result.toString();
	}


}
