using System;
using System.Data;
using System.Configuration;
using System.Linq;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.HtmlControls;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Xml.Linq;
using System.IO;
using Zeptomoby.OrbitTools;
using System.Collections;
using Matrice;

/// <summary>
///TleOrbit 的摘要说明
/// </summary>
public class TleOrbit
{
	public TleOrbit()
	{
		//
		//TODO: 在此处添加构造函数逻辑
		//
	}
    /// <summary>
    /// 计算DOP值，返回可见卫星数
    /// </summary>
    /// <param name="tle"></param>
    /// <param name="time">要计算的时间</param>
    /// <param name="B">测站的，单位：度</param>
    /// <param name="L"></param>
    /// <param name="H">单位：米</param>
    /// <param name="cor_limit">截止高度角，度</param>
    /// <param name="GDOP"></param>
    /// <param name="PDOP"></param>
    /// <param name="HDOP"></param>
    /// <param name="VDOP"></param>
    /// <param name="TDOP"></param>
    /// <returns></returns>
    public static int CalcDops(Tle[] tles, DateTime time, double B, double L, double H, double cor_limit, ref double GDOP, ref double PDOP, ref double HDOP, ref double VDOP, ref double TDOP)
    {
        double x2 = 0; double y2 = 0; double z2 = 0; //测站坐标
        BLToXYZ(B / 180 * Math.PI, L / 180 * Math.PI, H, ref x2, ref y2, ref z2);
        ArrayList temp = new ArrayList();
        Site siteEquator2 = new Site(B, L, H);
        Tle tle;
        Orbit orbit;
        Eci eci;
        CoordGeo cg;
        int sum = 0;
        for (int i = 0; i < tles.Length; i++)
        {
            tle = tles[i];
            orbit = new Orbit(tle);
            eci = orbit.GetPosition(time);
            double x = eci.Position.X * 1000;
            double y = eci.Position.Y * 1000;
            double z = eci.Position.Z * 1000; //化成米
            cg = eci.ToGeo();
            CoordTopo topoLook = siteEquator2.GetLookAngle(eci);
            topoLook.Elevation = Globals.Rad2Deg(topoLook.Elevation);
            topoLook.Azimuth = Globals.Rad2Deg(topoLook.Azimuth);  //化成度
            if (topoLook.Elevation > cor_limit)
            {
                sum++;
                double d_x = x - x2;
                double d_y = y - y2;
                double d_z = z - z2;
                double r2 = Math.Sqrt(d_x * d_x + d_y * d_y + d_z * d_z);
                temp.Add(d_x / r2);
                temp.Add(d_y / r2);
                temp.Add(d_z / r2);
            }
        }
        NNMatrix Q = new NNMatrix(sum, 4);
        NNMatrix Q_x = new NNMatrix(4, 4);
        for (int j = 0; j < temp.Count; j++)
        {
            if ((j + 1) % 3 == 1)
            {
                Q.Matrix[j / 3, 0] = (double)temp[j];
            }
            else if ((j + 1) % 3 == 2)
            {
                Q.Matrix[j / 3, 1] = (double)temp[j];
            }
            else if ((j + 1) % 3 == 0)
            {
                Q.Matrix[j / 3, 2] = (double)temp[j];
            }
            Q.Matrix[j / 3, 3] = 1;
        }
        Q_x = NNMatrix.Invers(NNMatrix.Transpos(Q) * Q);
        GDOP = Math.Sqrt(Q_x.Matrix[0, 0] + Q_x.Matrix[1, 1] + Q_x.Matrix[2, 2] + Q_x.Matrix[3, 3]);
        PDOP = Math.Sqrt(Q_x.Matrix[0, 0] + Q_x.Matrix[1, 1] + Q_x.Matrix[2, 2]);
        HDOP = Math.Sqrt(Q_x.Matrix[0, 0] + Q_x.Matrix[1, 1]);
        VDOP = Math.Sqrt(Q_x.Matrix[2, 2]);
        TDOP = Math.Sqrt(Q_x.Matrix[3, 3]);
        return sum;
    }

    public static Tle[] TlesFromTxt(string filename)
    {
        ArrayList list1 = new ArrayList();
        StreamReader sr = new StreamReader(filename);
        int i = 1;
        string line1 = "", line2 = "", line3 = "";
        while (sr.Peek() >= 0)
        {
            switch (i)
            {
                case 1:
                    line1 = sr.ReadLine();
                    i++;
                    break;
                case 2:
                    line2 = sr.ReadLine();
                    i++;
                    break;
                case 3:
                    line3 = sr.ReadLine();
                    Tle tle0 = new Tle(line1, line2, line3);
                    list1.Add(tle0);
                    i = 1;
                    break;
            }
        }
        Tle[] tles = (Tle[])(list1).ToArray(typeof(Tle));
        return tles;
    }

    /// <summary>
    /// 将大地坐标转换成地心坐标
    /// </summary>
    /// <param name="B">大地坐标，单位为弧度</param>
    /// <param name="L"></param>
    /// <param name="H"></param>
    /// <param name="X">地心坐标，单位为米</param>
    /// <param name="Y"></param>
    /// <param name="Z"></param>
    public static void BLToXYZ(double B, double L, double H, ref double X, ref double Y, ref double Z)
    {
        double a2 = 6378245.0;
        double e2 = 0.00669342162297;
        double N = a2 / (Math.Sqrt(1 - e2 * Math.Pow(Math.Sin(B), 2)));
        X = (N + H) * Math.Cos(B) * Math.Cos(L);
        Y = (N + H) * Math.Cos(B) * Math.Sin(L);
        Z = (N * (1 - e2) + H) * Math.Sin(B);
    }
}
