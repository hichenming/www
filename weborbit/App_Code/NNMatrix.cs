using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.IO;


namespace Matrice
{
    public struct NNMatrix
    {
        public int row, col;
        public double[,] Matrix;

        public NNMatrix(int Mrow, int Mcol)  //指定行列数创建矩阵，初始值为0矩阵
        {
            row = Mrow;
            col = Mcol;
            Matrix = new double[row, col];
            for (int i = 0; i < row; i++)
                for (int j = 0; j < col; j++)
                    Matrix[i, j] = 0;
        }

        public static NNMatrix operator +(NNMatrix m1, NNMatrix m2)   //矩阵加法
        {
            NNMatrix temp = new NNMatrix(m1.row, m1.col);
            if (m1.row == m2.row && m1.col == m2.col)
            {
                for (int i = 0; i < m1.row; i++)
                    for (int j = 0; j < m2.col; j++)
                         temp.Matrix[i,j]=m1.Matrix[i, j] + m2.Matrix[i, j];
            }
            return (temp);
        }

        public static NNMatrix operator +(NNMatrix m1, double m2)    //矩阵加常量
        {
            NNMatrix temp = new NNMatrix(m1.row, m1.col);
            for (int i = 0; i < m1.row; i++)
                for (int j = 0; j < m1.col; j++)
                    temp.Matrix[i,j]=m1.Matrix[i, j] + m2;
            return (temp);
        }

        public static NNMatrix operator -(NNMatrix m1, NNMatrix m2)  //矩阵减法
        {
            NNMatrix temp = new NNMatrix(m1.row, m1.col);
            if (m1.row == m2.row && m1.col == m2.col)
            {
                for (int i = 0; i < m1.row; i++)
                    for (int j = 0; j < m2.col; j++)
                        temp.Matrix[i,j]=m1.Matrix[i, j] - m2.Matrix[i, j];
            }
            return (temp);
        }

        public static NNMatrix operator *(NNMatrix m1, NNMatrix m2) //矩阵乘法
        {
            int m3r = m1.row;
            int m3c = m2.col;
            NNMatrix m3 = new NNMatrix(m3r, m3c);

            if (m1.col == m2.row)
            {
                double value = 0.0;
                for (int i = 0; i < m3r; i++)
                    for (int j = 0; j < m3c; j++)
                    {
                        for (int ii = 0; ii < m1.col; ii++)
                            value += m1.Matrix[i, ii] * m2.Matrix[ii, j];
                        m3.Matrix[i, j] = value;
                        value = 0.0;
                    }
            }
            else
                throw new Exception("矩阵的行/列数不匹配。");
            return m3;
        }

        public static NNMatrix operator *(NNMatrix m1, double m2) //矩阵乘以常量
        {
            for (int i = 0; i < m1.row; i++)
                for (int j = 0; j < m1.col; j++)
                    m1.Matrix[i, j] *= m2;
            return (m1);
        }

        public static NNMatrix Transpos(NNMatrix srcm)  //矩阵转秩
        {
            NNMatrix tmpm = new NNMatrix(srcm.col, srcm.row);
            for (int i = 0; i < srcm.row; i++)
                for (int j = 0; j < srcm.col; j++)
                {
                    if (i != j)
                    {
                        tmpm.Matrix[j, i] = srcm.Matrix[i, j];
                    }
                    else
                        tmpm.Matrix[i, j] = srcm.Matrix[i, j];
                }
            return tmpm;
        }

        private static void swaper(double m1, double m2) //交换
        {
            double sw;
            sw = m1; m1 = m2; m2 = sw;
        }

        /* 实矩阵求逆的全选主元高斯－约当法 */
        public static NNMatrix Invers(NNMatrix srcm)           //矩阵求逆
        {
            NNMatrix temp = new NNMatrix(srcm.row, srcm.col);
            for (int i = 0; i < srcm.row; i++)
                for (int j = 0; j < srcm.col; j++)
                    temp.Matrix[i, j] = srcm.Matrix[i, j];
            int rhc = temp.row;
            if (temp.row == temp.col)
            {
                int[] iss = new int[rhc];
                int[] jss = new int[rhc];
                double fdet = 1;
                double f = 1;
                //消元
                for (int k = 0; k < rhc; k++)
                {
                    double fmax = 0;
                    for (int i = k; i < rhc; i++)
                    {
                        for (int j = k; j < rhc; j++)
                        {
                            f = Math.Abs(temp.Matrix[i, j]);
                            if (f > fmax)
                            {
                                fmax = f;
                                iss[k] = i;
                                jss[k] = j;
                            }
                        }
                    }
                    if (iss[k] != k)
                    {
                        f = -f;
                        for (int ii = 0; ii < rhc; ii++)
                        {
                            swaper(temp.Matrix[k, ii], temp.Matrix[iss[k], ii]);
                        }
                    }
                    if (jss[k] != k)
                    {
                        f = -f;
                        for (int ii = 0; ii < rhc; ii++)
                        {
                            swaper(temp.Matrix[k, ii], temp.Matrix[jss[k], ii]);
                        }
                    }
                    fdet *= temp.Matrix[k, k];
                    temp.Matrix[k, k] = 1.0 / temp.Matrix[k, k];
                    for (int j = 0; j < rhc; j++)
                        if (j != k)
                            temp.Matrix[k, j] *= temp.Matrix[k, k];
                    for (int i = 0; i < rhc; i++)
                        if (i != k)
                            for (int j = 0; j < rhc; j++)
                                if (j != k)
                                    temp.Matrix[i, j] = temp.Matrix[i, j] - temp.Matrix[i, k] * temp.Matrix[k, j];
                    for (int i = 0; i < rhc; i++)
                        if (i != k)
                            temp.Matrix[i, k] *= -temp.Matrix[k, k];
                }
                // 调整恢复行列次序
                for (int k = rhc - 1; k >= 0; k--)
                {
                    if (jss[k] != k)
                        for (int ii = 0; ii < rhc; ii++)
                            swaper(temp.Matrix[k, ii], temp.Matrix[jss[k], ii]);
                    if (iss[k] != k)
                        for (int ii = 0; ii < rhc; ii++)
                            swaper(temp.Matrix[k, ii], temp.Matrix[iss[k], ii]);
                }
            }
            return temp;
        }

        /*求行列式值*/
        public static double ComputeDet(NNMatrix m)
        {
            int i, j, k, nis = 0, js = 0;
            double f, det, q, d;
            // 初值
            f = 1.0;
            det = 1.0;
            // 消元
            for (k = 0; k <= m.col - 2; k++)
            {
                q = 0.0;
                for (i = k; i <= m.col - 1; i++)
                {
                    for (j = k; j <= m.col - 1; j++)
                    {
                        d = Math.Abs(m.Matrix[j, i]);
                        if (d > q)
                        {
                            q = d;
                            nis = i;
                            js = j;
                        }
                    }
                }
                if (q == 0.0)
                {
                    det = 0.0;
                    return (det);
                }
                if (nis != k)
                {
                    f = -f;
                    for (j = k; j <= m.col - 1; j++)
                    {
                        d = m.Matrix[j, k];
                        m.Matrix[j, k] = m.Matrix[j, nis];
                        m.Matrix[j,nis] = d;
                    }
                }
                if (js != k)
                {
                    f = -f;
                    for (i = k; i <= m.col - 1; i++)
                    {
                        d = m.Matrix[js,i];
                        m.Matrix[js,i] = m.Matrix[k,i];
                        m.Matrix[k,i] = d;
                    }
                }
                det = det * m.Matrix[k, k];
                for (i = k + 1; i <= m.col - 1; i++)
                {
                    d = m.Matrix[k, i] / m.Matrix[k, k];
                    for (j = k + 1; j <= m.col - 1; j++)
                    {
                        m.Matrix[j, i] = m.Matrix[j, i] - d * m.Matrix[j, k];
                    }
                }
            }
            det = f * det * m.Matrix[m.row-1, m.col-1];
            return (det);
        }

        /*从文本文件中读取矩阵*/
        public static NNMatrix FromText(string filename)
        {
            StreamReader reader = new StreamReader(filename);
            string text = "";
            int rows = 0; int cols = 0;
            while ((text = reader.ReadLine()) != null)
            {
                if (text.Trim() != "")
                {
                    text = text.Trim();
                    rows++;
                    Regex reg = new Regex(@"\s{1,}");
                    string[] list = reg.Split(text);
                    cols = list.Length;
                }
            }
            reader.Close();
            NNMatrix temp = new NNMatrix(rows, cols);
            reader = new StreamReader(filename);
            int n = 0;
            while ((text = reader.ReadLine()) != null)
            {
                text = text.Trim();
                if (text != "")
                {
                    Regex reg = new Regex(@"\s{1,}");
                    string[] list = reg.Split(text);
                    for (int i = 0; i < list.Length; i++)
                    {
                        temp.Matrix[n, i] = Convert.ToDouble(list[i]);                         
                    }
                    n++;
                }
            }
            return temp;

        }

        public string MatrixPrint()   //矩阵输出
        {
            string tmprst;
            tmprst = "\n";
            for (int i = 0; i < row; i++)
            {
                for (int j = 0; j < col; j++)
                {
                    tmprst += Matrix[i, j].ToString() + "\t";
                }
                tmprst += "\n";
            }
            return tmprst;
        }
    }

}
