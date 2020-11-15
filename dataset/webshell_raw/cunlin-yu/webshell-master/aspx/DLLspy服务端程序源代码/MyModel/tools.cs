using System;
using System.Collections.Generic;
using System.Text;
using System.Text.RegularExpressions;

namespace MyModel
{
    public class tools
    {
        /// <summary>
        /// 是否为数字
        /// </summary>
        /// <param name="sSrc"></param>
        /// <returns></returns>
        public bool SGde(string sSrc)
        {
            Regex reg = new Regex(@"^0|[0-9]*[1-9][0-9]*$");
            if (reg.IsMatch(sSrc))
            {
                return true;
            }
            else
            {
                return false;
            }
        }


        /// <summary>
        /// 显示错误
        /// </summary>
        /// <param name="instr"></param>
        public void xseuB(string instr)
        {
            //DKt.Visible = true;
            //jDKt.InnerText = instr;
        }


        /// <summary>
        /// 使用Base64编码
        /// </summary>
        /// <param name="instr"></param>
        /// <returns></returns>
        public string MVVJ(string instr)
        {
            byte[] tmp = Encoding.Default.GetBytes(instr);
            return Convert.ToBase64String(tmp);
        }

        /// <summary>
        /// T0 base64
        /// </summary>
        /// <param name="str"></param>
        /// <returns></returns>
        public string ToBase64(string str)
        {
            byte[] tmp = Encoding.Default.GetBytes(str);
            return Convert.ToBase64String(tmp);
        }

        /// <summary>
        /// 替换大于小于标签
        /// </summary>
        /// <returns></returns>
        public string ReplaceFunc(string str)
        {
           str = str.Replace("<", "&lt;");
           str = str.Replace(">", "&gt;");
           return str;
        }
    }

}
