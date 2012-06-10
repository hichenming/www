<%@ WebHandler Language="C#" Class="orbitPoints" %>

using System;
using System.Web;
using System.IO;
using Zeptomoby.OrbitTools;

public class orbitPoints : IHttpHandler {

    public void ProcessRequest(HttpContext context)
    {
        context.Response.ContentType = "text/plain";
        if (context.Request.Params["code"] == null) return;
        int code = int.Parse(context.Request.Params["code"].ToString().Trim());
        Tle tle;
        Tle[] tles;
        Orbit orbit;
        Eci eci;
        CoordGeo cg;
        DateTime curtime = DateTime.Now.ToUniversalTime(); //当前时间
        int periodMin; //卫星周期
        string path;
        int calcStep = 1; //每1分钟计算一次坐标
        int i, j; //专门用于for循环
        string str1 = "GPS BIIA-10 (PRN 32)";
        string str2 = "1 20959U 90103A   11124.63588683 -.00000020  00000-0  10000-3 0  1308";
        string str3 = "2 20959  54.7286 255.2782 0120028 313.6993  45.3088  2.00561133149673";
        switch (code)
        {
            case 1:  //输出xyz坐标
                tle = new Tle(str1, str2, str3);
                orbit = new Orbit(tle);
                context.Response.Write("{\"result\":[");
                for (i = 0; i <= 720; i += 5)
                {
                    eci = orbit.GetPosition(i);
                    if (i != 0) { context.Response.Write(","); }
                    context.Response.Write("{\"x\":" + eci.Position.X * 1000 + ",\"y\":" + eci.Position.Y * 1000 + ",\"z\":" + eci.Position.Z * 1000 + "}");
                }
                context.Response.Write("]}");
                break;
            case 2: //输出lbh坐标
                tle = new Tle(str1, str2, str3);
                orbit = new Orbit(tle);
                context.Response.Write("{\"result\":[");
                for (i = 0; i <= 60*24; i += 1)
                {
                    eci = orbit.GetPosition(i);
                    cg = eci.ToGeo();
                    cg.Latitude = Globals.Rad2Deg(cg.Latitude);
                    cg.Longitude = Globals.Rad2Deg(cg.Longitude);
                    cg.Altitude = cg.Altitude * 1000;
                    if (i != 0) { context.Response.Write(","); }
                    context.Response.Write("{\"b\":" + cg.Latitude.ToString("f2") + ",\"l\":" + cg.Longitude.ToString("f2") + ",\"h\":" + cg.Altitude.ToString("f2") + "}");
                }
                context.Response.Write("]}");
                break;
            case 3: //返回所有星历文件名
                path = HttpContext.Current.Server.MapPath("Tle");
                DirectoryInfo dir = new DirectoryInfo(path);
                FileInfo[] fileinfo = dir.GetFiles();
                context.Response.Write("{\"result\":[");
                for (i = 0; i < fileinfo.Length; i++)
                {
                    if (i != 0) context.Response.Write(",");
                    context.Response.Write("{\"name\":\"" + fileinfo[i].Name + "\"}");
                }
                context.Response.Write("]}");
                break;
            case 4: //输出指定星历文件的轨迹坐标，和所有卫星的名字、坐标
                path = HttpContext.Current.Server.MapPath("Tle") + "/" + context.Request.Params["name"].ToString().Trim();
                tles = TleOrbit.TlesFromTxt(path);                
                
                for (i = 0; i < tles.Length; i++)
                {
                    tle = tles[i];
                    orbit = new Orbit(tle);
                    //卫星运行周期，分钟
                    periodMin = orbit.Period.Days * 60 * 24 + orbit.Period.Hours * 60 + orbit.Period.Minutes + 1;
                    if (i == 0)  //输出第一个卫星的轨道坐标
                    {
                        context.Response.Write("{\"points\":[");
                        for (j = 0; j <= periodMin * 2; j += calcStep)
                        {
                            eci = orbit.GetPosition(curtime.AddMinutes(j));
                            cg = eci.ToGeo();
                            cg.Latitude = Globals.Rad2Deg(cg.Latitude);
                            cg.Longitude = Globals.Rad2Deg(cg.Longitude);
                            cg.Altitude = cg.Altitude * 1000;
                            if (j != 0) { context.Response.Write(","); }
                            context.Response.Write("{\"b\":" + cg.Latitude.ToString("f2") + ",\"l\":" + cg.Longitude.ToString("f2") + ",\"h\":" + cg.Altitude.ToString("f2") + "}");
                        }
                        context.Response.Write("],\"sats\":[");
                    }
                    else context.Response.Write(",");
                    
                    eci = orbit.GetPosition(curtime);
                    cg = eci.ToGeo();
                    cg.Latitude = Globals.Rad2Deg(cg.Latitude);
                    cg.Longitude = Globals.Rad2Deg(cg.Longitude);
                    cg.Altitude = cg.Altitude * 1000;

                    context.Response.Write("{\"name\":\"" + tle.Name.Trim() + "\",\"b\":" + cg.Latitude.ToString("f2") +
                        ",\"l\":" + cg.Longitude.ToString("f2") + ",\"h\":" + cg.Altitude.ToString("f2") + "}"); 
                }
                context.Response.Write("]}");
                break;
            case 5: //输出指定星历文件，指定卫星的xyz坐标
                string filename = context.Request.Params["file"].Trim();
                string name = context.Server.UrlDecode(context.Request.Params["name"].Trim());
                string[] names = name.Split(',');
                tles = TleOrbit.TlesFromTxt(HttpContext.Current.Server.MapPath("Tle") + "/" + filename);
                context.Response.Write("{\"result\":[");
                Boolean isfirst = true;
                for (i = 0; i < tles.Length; i++)
                {
                    tle = tles[i];
                    if (Array.IndexOf(names, tle.Name.Trim()) != -1)
                    {
                        if (isfirst) 
                            isfirst = false; 
                        else context.Response.Write(",");
                        context.Response.Write("{\"name:\":\""+tle.Name.Trim()+"\",\"points\":["); //对于flex，必须使用双引号，值前后不能有空格，SB FLEX
                        orbit = new Orbit(tle);
                        periodMin = orbit.Period.Days * 60 * 24 + orbit.Period.Hours * 60 + orbit.Period.Minutes + 1;
                        for (j = 0; j <= periodMin * 2; j += 3*calcStep)
                        {
                            eci = orbit.GetPosition(curtime.AddMinutes(j));
                            if (j != 0) { context.Response.Write(","); }
                            context.Response.Write("{\"x\":" + (eci.Position.X * 1000).ToString("f2") + ",\"y\":" + (eci.Position.Y * 1000).ToString("f2") + ",\"z\":" + (eci.Position.Z * 1000).ToString("f2") + "}");
                        }
                        context.Response.Write("]}");
                    } 
                }
                context.Response.Write("]}");
                break;
            case 6: //返回卫星可见数
                string[] almfiles = new string[] { "gps-ops.txt", "glo-ops.txt", "galileo.txt" };
                string jsonfile = "numChartNet.json";
                StreamReader sr = new StreamReader(HttpContext.Current.Server.MapPath("json") + "/" + jsonfile);
                string jsonstr = sr.ReadToEnd().Trim();
                sr.Dispose();
                double lon = double.Parse(context.Request.Params["lon"]);
                double lat = double.Parse(context.Request.Params["lat"]);
                double alt = double.Parse(context.Request.Params["alt"]);
                string datestr = context.Request.Params["date"];
                Site siteEquator = new Site(lat, lon, alt);
                curtime = Convert.ToDateTime(datestr);
                for (i = 0; i < almfiles.Length; i++)
                {
                    string numstr = "";
                    tles = TleOrbit.TlesFromTxt(HttpContext.Current.Server.MapPath("Tle") + "/" + almfiles[i]);
                    for (j = 0; j < 24; j++)
                    {
                        int num = 0;
                        for (int k = 0; k < tles.Length; k++)
                        {
                            tle = tles[k];
                            orbit = new Orbit(tle);
                            eci = orbit.GetPosition(curtime.AddMinutes(j * 60));
                            cg = eci.ToGeo();                            
                            CoordTopo topoLook = siteEquator.GetLookAngle(eci);
                            topoLook.Elevation = Globals.Rad2Deg(topoLook.Elevation);
                            topoLook.Azimuth = Globals.Rad2Deg(topoLook.Azimuth);
                            if (topoLook.Elevation > 15)
                                num++;
                        }
                        if (j != 0)
                            numstr += ",";
                        numstr += num.ToString();
                    }
                    jsonstr = jsonstr.Replace("123456chenming" + (i + 1).ToString(), numstr);
                }
                jsonstr = jsonstr.Replace("卫星可见数分布图", "卫星可见数分布图(" + datestr + ")");
                context.Response.Write(jsonstr);
                break;
            case 7:  //输入卫星高度角列表            
                string almfile = "gps-ops.txt";
                if (context.Request.Params["file"] != null)
                    almfile = context.Request.Params["file"].Trim();
                string jsonfile2 = "eleChartNet.json";
                StreamReader sr2 = new StreamReader(HttpContext.Current.Server.MapPath("json") + "/" + jsonfile2);
                string jsonstr2 = sr2.ReadToEnd().Trim();
                sr2.Dispose();
                double lon2 = double.Parse(context.Request.Params["lon"]);
                double lat2 = double.Parse(context.Request.Params["lat"]);
                double alt2 = double.Parse(context.Request.Params["alt"]);
                string datestr2 = context.Request.Params["date"];
                Site siteEquator2 = new Site(lat2, lon2, alt2);
                curtime = Convert.ToDateTime(datestr2);
                tles = TleOrbit.TlesFromTxt(HttpContext.Current.Server.MapPath("Tle") + "/" + almfile);
                string timearr = "";
                string valuearr = "";
                for (int k = 0; k < tles.Length; k++)
                {
                    tle = tles[k];
                    orbit = new Orbit(tle);
                    if (k != 0)
                        valuearr += ",";
                    valuearr += "{\"line-width\": \"2px\",\"values\": [";
                    for (j = 0; j < 24 * 60; j += 20)
                    {
                        if (k == 0)
                        {
                            if (j != 0)
                                timearr += ",";
                            string tmpstr = "\"" + (j / 60).ToString() + ":" + (j % 60).ToString() + "\"";
                            timearr += tmpstr;
                        }
                        eci = orbit.GetPosition(curtime.AddMinutes(j));
                        cg = eci.ToGeo();                        
                        CoordTopo topoLook = siteEquator2.GetLookAngle(eci);
                        topoLook.Elevation = Globals.Rad2Deg(topoLook.Elevation);
                        topoLook.Azimuth = Globals.Rad2Deg(topoLook.Azimuth);
                        if (topoLook.Elevation < 10)
                            topoLook.Elevation = 10;
                        if (j != 0)
                            valuearr += ",";
                        valuearr += topoLook.Elevation.ToString("f2");
                    }
                    string namestr = tle.Name.Trim();
                    namestr = namestr.Substring(0, 1) + namestr.Substring(namestr.Length - 3, 2);
                    valuearr += "],\"text\": \"" + namestr + "\"}";
                }
                jsonstr2 = jsonstr2.Replace("123456chenming1", timearr);
                jsonstr2 = jsonstr2.Replace("123456chenming2", valuearr);
                jsonstr2 = jsonstr2.Replace("高度角分布图", "高度角分布图("+almfile+" " + datestr2 + ")");
                context.Response.Write(jsonstr2);
                break;
            case 8: //输出DOP值
                string almfile2 = "gps-ops.txt";
                if (context.Request.Params["file"] != null)
                    almfile2 = context.Request.Params["file"].Trim();
                string jsonfile3 = "dopChartNet.json";
                StreamReader sr3 = new StreamReader(HttpContext.Current.Server.MapPath("json") + "/" + jsonfile3);
                string jsonstr3 = sr3.ReadToEnd().Trim();
                sr3.Dispose();
                double lon3 = double.Parse(context.Request.Params["lon"]);
                double lat3 = double.Parse(context.Request.Params["lat"]);
                double alt3 = double.Parse(context.Request.Params["alt"]);
                string datestr3 = context.Request.Params["date"];
                double cor_limit = 10;          
                curtime = Convert.ToDateTime(datestr3);
                tles = TleOrbit.TlesFromTxt(HttpContext.Current.Server.MapPath("Tle") + "/" + almfile2);
                double GDOP = 0; double PDOP = 0; double HDOP = 0; double VDOP = 0; double TDOP = 0;
                string GDOPstr = ""; string PDOPstr = "";  string HDOPstr = "";
                string VDOPstr = ""; string TDOPstr = "";
                string timearr2 = "";
                for (j = 0; j < 24 * 60; j += 20)
                {
                    if (j != 0)
                        timearr2 += ",";
                    string tmpstr2 = "\"" + (j / 60).ToString() + ":" + (j % 60).ToString() + "\"";
                    timearr2 += tmpstr2;
                    
                    TleOrbit.CalcDops(tles, curtime.AddMinutes(j), lat3, lon3, alt3, cor_limit, ref GDOP, ref PDOP, ref HDOP, ref VDOP, ref TDOP);
                    if (j != 0)
                    {
                        GDOPstr += ",";
                        PDOPstr += ",";
                        HDOPstr += ",";
                        VDOPstr += ",";
                        TDOPstr += ",";                         
                    }
                    if (double.IsNaN(GDOP) || double.IsInfinity(GDOP) || GDOP>10000) { GDOP = 10000; }
                    if (double.IsNaN(PDOP) || double.IsInfinity(PDOP) || PDOP > 10000) { PDOP = 10000; }
                    if (double.IsNaN(HDOP) || double.IsInfinity(HDOP) || HDOP > 10000) { HDOP = 10000; }
                    if (double.IsNaN(VDOP) || double.IsInfinity(VDOP) || VDOP > 10000) { VDOP = 10000; }
                    if (double.IsNaN(TDOP) || double.IsInfinity(TDOP) || TDOP > 10000) { TDOP = 10000; }
                    GDOPstr += GDOP.ToString("f2");
                    PDOPstr += PDOP.ToString("f2");
                    HDOPstr += HDOP.ToString("f2");
                    VDOPstr += VDOP.ToString("f2");
                    TDOPstr += TDOP.ToString("f2");
                }
                jsonstr3 = jsonstr3.Replace("123456chenming1", timearr2);
                jsonstr3 = jsonstr3.Replace("123456chenming2", GDOPstr);
                jsonstr3 = jsonstr3.Replace("123456chenming3", PDOPstr);
                jsonstr3 = jsonstr3.Replace("123456chenming4", HDOPstr);
                jsonstr3 = jsonstr3.Replace("123456chenming5", VDOPstr);
                jsonstr3 = jsonstr3.Replace("123456chenming6", TDOPstr);
                jsonstr3 = jsonstr3.Replace("DOP值分布图", "DOP值分布图("+almfile2+"，" + datestr3 + ")");
                context.Response.Write(jsonstr3);
                break;
        }
    }

    public bool IsReusable
    {
        get {
            return false;
        }
    }

}