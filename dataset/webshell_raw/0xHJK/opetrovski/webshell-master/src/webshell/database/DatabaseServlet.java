package webshell.database;

import java.io.IOException;
import java.io.PrintWriter;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

@WebServlet(urlPatterns = "/database")
public class DatabaseServlet extends HttpServlet {
	private static final long serialVersionUID = 1543765435654L;
	private static final Log log = LogFactory.getLog(DatabaseServlet.class);

	protected void doGet(HttpServletRequest request,
			HttpServletResponse response) throws ServletException, IOException {
		String cmd = request.getParameter("cmd");
		String tabid = request.getParameter("tabid");
		response.setContentType("application/json");
		response.setStatus(HttpServletResponse.SC_OK);
		
		try {
			if ("connect".equals(cmd)) {
				String hostname = request.getParameter("hostname");
				String port = request.getParameter("port");
				String driver = request.getParameter("driver");
				String database = request.getParameter("database");
				String userid = request.getParameter("userid");
				String password = request.getParameter("password");
				Database.getInstance().establishConnection(hostname, port,
						driver, database, userid, password);
				String json = Database.getInstance().listTables();
				PrintWriter out = response.getWriter();
				out.print(json);
				out.flush();
			} else if ("list_tables".equals(cmd)) {
				if( !Database.getInstance().isConnected() ){
					response.setStatus(HttpServletResponse.SC_PRECONDITION_FAILED);
					log.error("Failed to list tables because database is not connected");
				}
				else{
					String json = Database.getInstance().listTables();
					PrintWriter out = response.getWriter();
					out.print(json);
					out.flush();
				}				
			} else if ("open_table".equals(cmd)) {
				String tablename = request.getParameter("name");
				String json = Database.getInstance().getTableContent(tablename);
				PrintWriter out = response.getWriter();
				out.print(json);
				out.flush();
			} else if ("save_value".equals(cmd)) {
			}
		} catch (Exception e) {
			log.error("Failed to establish connection", e);
		}
	}
}