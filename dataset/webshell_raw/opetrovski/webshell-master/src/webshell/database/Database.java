/*
 ******************************************************************************
 *                                Fiducia IT AG                               *
 *                           ALL RIGHTS RESERVED                              *
 ******************************************************************************
 *
 * $Source: /cm/cvsroot/ein/ein/backend/src/impl/de/bapagree/ein/backend/impl/comp/TSDB2Basis.java,v $
 * @version $Revision: 1.3 $
 * @author $Author: xhu1091 $ 
 */
package webshell.database;

import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.SQLException;
import java.sql.SQLWarning;
import java.sql.Statement;
import java.util.Properties;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

import webshell.util.JsonResult;

public class Database {
	private Connection connection;
	private String query;
	private String queryName;
	private ResultSet resultSet;
	private static Database instance = null;
	private static final Log log = LogFactory.getLog(Database.class);

	private Database() {
	}

	public static Database getInstance() {
		if (instance == null) {
			instance = new Database();
		}
		return instance;
	}

	/**
	 * Baut die Verbindung (Connection) zur Datenbank wieder ab, bzw. gibt die
	 * Connection wieder frei. Unreferenziert die Instanz Variablen, bzw. setzt
	 * die Instanz Variablen auf <code>null</code>.
	 * 
	 * @see java.lang.Object#finalize()
	 */
	public void finalize() {
		this.freeConnection();
		this.unref();
	}

	/**
	 * Etabliert eine Verbindung zur Datenbank, kann keine Verbindung zur
	 * Datenbank hergestellt werden, wird eine SQLException geworfen.
	 * 
	 * @exception java.sql.SQLException
	 *                wenn keine Verbindung zur Datenbank hergetsellt werden
	 *                kann.
	 * 
	 * @see java.sql.SQLException
	 */
	public void establishConnection(String hostname, String port,
			String driver, String database, String user, String password)
			throws Exception {
		String url = "jdbc:oracle:thin:@" + "(DESCRIPTION=(ADDRESS=(HOST="
				+ hostname + ")" + "(PROTOCOL=tcp)(PORT=" + port + "))"
				+ "(CONNECT_DATA=(SID=" + database + ")))";

		// jdbc:oracle:thin:@209.194.12.223:1526:orcl

		if (driver.indexOf("mysql") > 0) {
			url = "jdbc:mysql://" + hostname + ":" + port + "/" + database;
		} else if (driver.indexOf("postgresql") > 0) {
			url = "jdbc:postgresql://" + hostname + ":" + port + "/" + database;
		}

		Class.forName(driver);
		Properties props = new Properties();
		props.setProperty("user", user);
		props.setProperty("password", password);
		connection = DriverManager.getConnection(url, props);
		connection.setAutoCommit(true);
	}

	public boolean isConnected() {
		boolean connected = true;

		try {
			connected = (connection != null && !connection.isClosed());
		} catch (SQLException e) {
			log.error("Failed to retrieve connection status", e);
			connected = false;
		}
		return connected;
	}

	public String listTables() throws Exception {
		JsonResult json = new JsonResult();
		json.beginArray();
		DatabaseMetaData md = connection.getMetaData();
		String[] types = {"TABLE"};
		ResultSet rs = md.getTables(null, null, "%", types);
		while (rs.next()) {
			json.addToArray(rs.getString(3));
		}

		json.endArray();
		return json.getJson();
	}

	public String getTableContent(String tablename) throws SQLException {
		log.debug("tablename: " + tablename);
		Statement statement = connection.createStatement();
		ResultSet resultSet = statement.executeQuery("SELECT * FROM " + tablename);
		ResultSetMetaData metadata = resultSet.getMetaData();
		int columnCount = metadata.getColumnCount();

		JsonResult json = new JsonResult();
		json.begin();
		json.beginArray("columnNames");

		for (int i = 0; i < columnCount; i++) {
			json.addToArray(metadata.getColumnName(i + 1).toUpperCase());
		}
		json.endArray();

		json.beginArray("data");
		while (resultSet.next()) {
			json.beginArray();
			for (int i = 0; i < columnCount; i++) {
				String value = resultSet.getString(i + 1);
				json.addToArray(value);
			}
			json.endArray();
		}
		resultSet.close();
		json.endArray();
		json.end();
		String result = json.getJson();
		log.debug(result);
		return result;
	}


	/**
	 * Baut die Verbindung (Connection) zur Datenbank wieder ab, bzw. gibt die
	 * Connection wieder frei.
	 */
	public void freeConnection() {
		try {
			connection.close();
		} catch (SQLException dbEx) {
			log.error("Fehler beim Disconnect", dbEx);
		}
	}

	/**
	 * Protokolliert die SQLWarnings mit dem Loglevel <code>LOG_WARNING</code>.
	 * 
	 * @param warn
	 *            Die SQLWarning, die protokolliert werden soll.
	 * 
	 */
	protected final void logSQLWarning(SQLWarning warn) {
		while (warn != null) {
			StringBuffer buffer = new StringBuffer();

			buffer.append("SQL Warning:\n");
			buffer.append(warn.getMessage() + "\n");
			buffer.append("ANSI-92 SQL State: " + warn.getSQLState() + "\n");
			buffer.append("Vendor Error Code: " + warn.getErrorCode() + "\n");

			log.error(buffer.toString());

			warn = warn.getNextWarning();

		}// end while (warn != null)

	}// end method

	/**
	 * Protokolliert die SQLException mit dem Loglevel
	 * <code>LOG_EXCEPTION</code>.
	 * 
	 * @param sqlEx
	 *            Die SQLException, die protokolliert werden soll.
	 * 
	 */
	protected final void logSQLException(SQLException sqlEx) {
		while (sqlEx != null) {
			StringBuffer buffer = new StringBuffer();

			buffer.append("SQL Exception:\n");
			buffer.append(sqlEx.getMessage() + "\n");
			buffer.append("ANSI-92 SQL State: " + sqlEx.getSQLState() + "\n");
			buffer.append("Vendor Error Code: " + sqlEx.getErrorCode() + "\n");
			buffer.append("Queryname:         " + this.queryName + "\n");
			buffer.append("letzter Query :    " + this.query + "\n");

			// logging
			log.error(buffer.toString(), sqlEx);

			sqlEx = sqlEx.getNextException();

		}// end while

	}// end method

	/**
	 * Dereferenziert die Instanzvariablen, bzw. setzt diese auf null.
	 */
	private void unref() {
		this.connection = null;
		// this.statement = null;
		this.resultSet = null;
		this.query = null;
		this.queryName = null;
	}


	/**
	 * F&uuml;hrt ein SQL INSERT, UPDATE oder DELETE <code>statement</code> aus.
	 * 
	 * @param sql
	 *            eine SQL INSERT-, UPDATE- oder DELETE - Anweisung oder eine
	 *            SQL Anweisung, die nichts zur&uuml;ck liefert.
	 * @param queryName
	 *            eine Bezeichnung f&uuml;r die SQL Anweisung.
	 * @return entweder der Reihen Zaehlimpuls fuer INSERT-, UPDATE- oder
	 *         DELETE-Anweisungen oder 0 fuer SQL Anweisungen, die nichts
	 *         zur&uuml;ckbringen.
	 * @exception SQLException
	 *                wenn eine Datenbankzugriffstoerung auftritt.
	 * 
	 */
	protected final int executeUpdate(final String query, final String queryName)
			throws SQLException {

		String logTxt = "\n" + "QueryName: " + queryName + "\n" + "Query:     "
				+ query + "\n";

		log.debug(logTxt);

		Statement statement = connection.createStatement();
		int rowCount = statement.executeUpdate(query);
		return rowCount;

	} // end method



	protected final boolean isNumber(String num) {
		if (num == null || num.length() == 0)
			return false;

		for (int i = 0; i < num.length(); i++)
			if (!Character.isDigit(num.charAt(i)))
				return false;

		return true;
	}
}
