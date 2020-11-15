using System;
using System.Collections.Generic;
using System.Text;
using Microsoft.Win32;
using System.Diagnostics;


namespace MyModel
{
    public class RegControl
    {
        //获取跟
        public RegistryKey GetROOT(string rootname)
        {
            RegistryKey hkml = null;
            switch (rootname)
            {
                case "ClassesRoot":
                    hkml = Registry.ClassesRoot;
                    break;
                case "CurrentConfig":
                    hkml = Registry.CurrentConfig;
                    break;
                case "CurrentUser":
                    hkml = Registry.CurrentUser;
                    break;
                case "DynData":
                    hkml = Registry.DynData;
                    //<=win98
                    break;
                case "LocalMachine":
                    hkml = Registry.LocalMachine;
                    break;
                case "PerformanceData":
                    hkml = Registry.PerformanceData;
                    break;
                case "Users":
                    hkml = Registry.Users;
                    break;
                default:
                    break;
            }
            return hkml;
        }

        public string GetRegistChilds(string rootname,string childName)
        {
            
            string registData;
            RegistryKey hkml = GetROOT(rootname);
            RegistryKey software = hkml.OpenSubKey("SOFTWARE",true);
            RegistryKey aimdir = software.OpenSubKey("XXX", true);
            registData = aimdir.GetValue(rootname).ToString();
            return registData;
        }

        //读取指定名称的注册表的值.以上是读取的注册表中HKEY_LOCAL_MACHINE\SOFTWARE目录下的XXX目录中名称为name的注册表值；
        public string GetRegistData(string name)
        {
            string registData;
            RegistryKey hkml = Registry.LocalMachine;
            RegistryKey software = hkml.OpenSubKey("SOFTWARE", true);
            RegistryKey aimdir = software.OpenSubKey("XXX", true);
            registData = aimdir.GetValue(name).ToString();
            return registData;
        }

        //向注册表中写数据.以上是在注册表中HKEY_LOCAL_MACHINE\SOFTWARE目录下新建XXX目录并在此目录下创建名称为name值为tovalue的注册表项；
        private void WTRegedit(string name, string tovalue)
        {
            RegistryKey hklm = Registry.LocalMachine;
            RegistryKey software = hklm.OpenSubKey("SOFTWARE", true);
            RegistryKey aimdir = software.CreateSubKey("XXX");
            aimdir.SetValue(name, tovalue);
        }

        //删除注册表中指定的注册表项
        //以上是在注册表中HKEY_LOCAL_MACHINE\SOFTWARE目录下XXX目录中删除名称为name注册表项；
        private void DeleteRegist(string name)
        {
            string[] aimnames;
            RegistryKey hkml = Registry.LocalMachine;
            RegistryKey software = hkml.OpenSubKey("SOFTWARE", true);
            RegistryKey aimdir = software.OpenSubKey("XXX", true);
            aimnames = aimdir.GetSubKeyNames();
            foreach (string aimKey in aimnames)
            {
                if (aimKey == name)
                    aimdir.DeleteSubKeyTree(name);
            }
        }


        //判断指定注册表项是否存在
        private bool IsRegeditExit(string name)
        {
            bool _exit = false;
            try
            {
                string[] subkeyNames;
                RegistryKey hkml = Registry.LocalMachine;
                RegistryKey software = hkml.OpenSubKey("SOFTWARE", true);
                RegistryKey aimdir = software.OpenSubKey("Weather", true);
                subkeyNames = aimdir.GetValueNames();
                foreach (string keyName in subkeyNames)
                {
                    if (keyName == name)
                    {
                        _exit = true;
                        return _exit;
                    }
                }
            }
            catch
            { }
            return _exit;
        }
    }
}
