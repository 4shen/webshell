import com.googlecode.htmlcompressor.compressor.HtmlCompressor;

import java.io.*;

/**
 * Created by zxmoa on 14-8-25.
 */
public class Tools {


    public static  String loadClassData(String filepath) throws Exception {
        int n =0;
        BufferedInputStream br = new BufferedInputStream(
                new FileInputStream(
                        new File(filepath)));
        ByteArrayOutputStream bos= new ByteArrayOutputStream();
        while((n=br.read())!=-1){
            //  System.out.print(n);
            bos.write(n);
        }
        String hex = byte2hex(bos.toByteArray());
        return  hex;
//        FileOutputStream file_out=new FileOutputStream(filepath+"hex");
//        file_out.write(hex.getBytes() );
//        file_out.close();
//        return bos.toByteArray();
    }




    public static String byte2hex(byte[] b) { //一个字节的数，
// 转成16进制字符串
        String hs = "";
        String tmp = "";
        for (int n = 0; n < b.length; n++) {
//整数转成十六进制表示
            tmp = (Integer.toHexString(b[n] & 0XFF));
            if (tmp.length() == 1) {
                hs = hs + "0" + tmp;
            } else {
                hs = hs + tmp;
            }
        }
        tmp = null;

        return hs.toUpperCase(); //转成大写
    }



    /**
     * 字符串转java字节码
     * @param b
     * @return
     */
    public static byte[] hex2byte(byte[] b) {
        if ((b.length % 2) != 0) {
            throw new IllegalArgumentException("长度不是偶数");
        }
        byte[] b2 = new byte[b.length / 2];
        for (int n = 0; n < b.length; n += 2) {
            String item = new String(b, n, 2);
            // 两位一组，表示一个字节,把这样表示的16进制字符串，还原成一个进制字节

            b2[n / 2] = (byte) Integer.parseInt(item, 16);
        }
        b = null;
        return b2;
    }


    /**
     * 去掉jsp的空行和标签占的空行
     */
    public static void hanldleJsp(File file,String classdate) {
        try {
            System.out.println("compress file:" + file.getAbsolutePath());
            BufferedReader br = new BufferedReader(new InputStreamReader(
                    new FileInputStream(file), "utf-8"));
            String s = null;
            StringBuilder sb = new StringBuilder();
            while ((s = br.readLine()) != null) {
                sb.append(s);
                sb.append("\n");
            }
            br.close();
            BufferedWriter bw = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(file+ "shell.jsp"), "utf-8"));
            // bw.write(compressor().compress( sb.toString().replace("CLASSS",classdate)) );
             bw.write(sb.toString().replace("CLASSS",classdate) );
            bw.close();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }



    public static HtmlCompressor compressor () {

        HtmlCompressor compressor = new HtmlCompressor();
        compressor.setEnabled(true);                   //if false all compression is off (default is true)
        compressor.setRemoveComments(false);            //if false keeps HTML comments (default is true)
        compressor.setRemoveMultiSpaces(true);         //if false keeps multiple whitespace characters (default is true)
        compressor.setRemoveIntertagSpaces(false);      //removes iter-tag whitespace characters
        compressor.setRemoveQuotes(false);              //removes unnecessary tag attribute quotes
        compressor.setSimpleDoctype(false);             //simplify existing doctype
        compressor.setRemoveScriptAttributes(false);    //remove optional attributes from script tags
        compressor.setRemoveStyleAttributes(true);     //remove optional attributes from style tags
        compressor.setRemoveLinkAttributes(false);      //remove optional attributes from link tags
        compressor.setRemoveFormAttributes(false);      //remove optional attributes from form tags
        compressor.setRemoveInputAttributes(true);     //remove optional attributes from input tags
        compressor.setSimpleBooleanAttributes(true);   //remove values from boolean tag attributes
        compressor.setRemoveJavaScriptProtocol(true);  //remove "javascript:" from inline event handlers
        compressor.setRemoveHttpProtocol(true);        //replace "http://" with "//" inside tag attributes
        compressor.setRemoveHttpsProtocol(true);       //replace "https://" with "//" inside tag attributes
        compressor.setPreserveLineBreaks(false);        //preserves original line breaks
//  compressor.setRemoveSurroundingSpaces("br,p"); //remove spaces around provided tags
        compressor.setCompressCss(false);               //compress inline css
        compressor.setCompressJavaScript(false);        //compress inline javascript
        //compressor.setYuiCssLineBreak(80);             //--line-break param for Yahoo YUI Compressor
        //compressor.setYuiJsDisableOptimizations(true); //--disable-optimizations param for Yahoo YUI Compressor
        //compressor.setYuiJsLineBreak(-1);              //--line-break param for Yahoo YUI Compressor
        //compressor.setYuiJsNoMunge(true);              //--nomunge param for Yahoo YUI Compressor
        //compressor.setYuiJsPreserveAllSemiColons(true);//--preserve-semi param for Yahoo YUI Compressor
        return  compressor;
    }

}

