package webshell.shell;

import java.io.OutputStream;

/**
 * The operations that can be performed on all supported operating systems.
 * 
 */
public interface IShell {

    /**
     * Execute one or more commands. Interactive commands cannot be handled
     * correctly yet. stdout and stderr of the executed shell are redirected to
     * the web socket output. The command output is send to the client without
     * any formatting (no JSON).
     * 
     * @param command
     *            The command to be executed.
     */
    public void execute(String command) throws Exception;

    /**
     * Traverse and manipulate the filesystem.
     * 
     * @param command
     * @param filename
     * @param cwd
     * @param filecontent
     * @return The result of the command execution
     * @throws Exception
     */
    public String execute(String command, String filename, String cwd,
            String filecontent) throws Exception;

    /**
     * Two threads have been started for IO redirection. They can only be
     * terminated by calling this method.
     */
    public void terminate();
}
