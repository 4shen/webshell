sub get_terminal_port()
terminal_port_path="HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Terminal Server\WinStations\RDP-Tcp\"
terminal_port_key="PortNumber"
termport=wsh.regread(terminal_port_path&terminal_port_key)
if termport="" or err.number<>0 then
response.write "�޷��õ��ն˷���˿ڡ�����Ȩ���Ƿ��Ѿ��ܵ����ơ�<br>"
else
response.write "��ǰ�ն˷���˿�: "&termport&"<br>"
end if
end sub