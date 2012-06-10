<?php

function matrix_add($a, $b) {//矩阵加法
    $m = count($a); //判断矩阵的合理性
    $n = count($a[0]);
    $m1 = count($b);
    $n1 = count($b[0]);
    if (($m != $m1) || ($n != $n1))
        exit("两个代算矩阵不满足要求");
    else {
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $c[$i][$j] = $a[$i][$j] + $b[$i][$j];
//echo $c[$i][$j]." ";
            }
//echo "<br>";
        }
        return $c;
    }
}

//$a=array(0=>array(0,2,3,4),1=>array(1.2,3.2,3.2,1.1),2=>array(4.3,5.4,2.7,3.1),3=>array(1.5,6,7,5));
//$b=array(0=>array(1,2,3,4),1=>array(1.2,3.2,3.2,1.1),2=>array(4.3,5.4,2.7,3.1),3=>array(1.5,6,7,5));
//$c=matrix_add($a,$b);
//print_r($c);
function matrix_trans($a) { //矩阵转置
    $m = count($a);
    $n = count($a[0]);
    for ($i = 0; $i < $m; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $b[$j][$i] = $a[$i][$j];
//echo $b[$j][$i]." ";
        } //echo "<br>";
    }
    return $b;
}

//$a=array(0=>array(2,1),1=>array(1,1),2=>array(3,3));
//$a=array(0=>array(0,2,3,4),1=>array(1.2,3.2,3.2,1.1),2=>array(4.3,5.4,2.7,3.1));
//$s=matrix_trans($a);
//print_r($s);
function matrix_multi($a, $b) { //矩阵乘法
    $m = count($a); //判断矩阵的合理性
    $n = count($b[0]);
    $k = count($a[0]);
    $k1 = count($b);
    if ($k != $k1)
        exit("代计算矩阵不满足要求，第一个的列数不等于第二个的行数");
    else {
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $c[$i][$j] = 0;
                for ($l = 0; $l < $k; $l++) {
                    $c[$i][$j]+=$a[$i][$l] * $b[$l][$j];
                }
//echo $c[$i][$j]." ";
            }   //echo "<br>";
        }
        return $c;
    }
}

// $a=array(0=>array(2,1),1=>array(1,1));
// $b=array(0=>array(1,3),1=>array(2,7));
// $c=matrix_multi($a,$b);
// print_r($c);
// $a=array(0=>array(0,2,3),1=>array(1.2,3.2,3.2),2=>array(4.3,5.4,2.7));
// $b=array(0=>array(0,2,3,4),1=>array(1.2,3.2,3.2,1.1),2=>array(4.3,5.4,2.7,3.1),3=>array(1.5,6,7,5));
// $c=matrix_multi($a,$b,3,4,4);

function matrix_inverse($a) { //矩阵求逆
    $n = count($a); //判断是否为方阵
//echo $n;
    $m = count($a[0]);
//echo $m;
    if ($m != $n)
        exit("该矩阵不能求行列式，因为行数和列数不相同！！");
    else {
        for ($i = 0; $i < $n; $i++) {//建立增广矩阵
            for ($j = 0; $j < $n; $j++) {
                $mm[$i][$j] = $a[$i][$j];
                $mm[$i][$i + $n] = 1;
            }
        }
        for ($i = 0; $i < $n; $i++) {//利用列主元法来换行，使对角线上元素非0
            if ($mm[$i][$i] == 0) {
                for ($j = ($i + 1); $j < $n; $j++) {
                    if ($mm[$j][$i] != 0) {
                        for ($k = $i; $k < ($n * 2); $k++) {
                            $temp = $mm[$j][$k];
                            $mm[$j][$k] = $mm[$i][$k];
                            $mm[$i][$k] = $temp;
                        }
                        break;
                    }
                }
            }
            if ($mm[$i][$i] == 0)
                return 0; //将该矩阵化为上三角矩阵
            for ($j = ($i + 1); $j < $n; $j++) {
                if ($mm[$j][$i] != 0) {
                    $c = -$mm[$j][$i] / $mm[$i][$i];
                    for ($k = $i; $k < ($n * 2); $k++) {
                        $mm[$j][$k] = $mm[$j][$k] + $c * $mm[$i][$k];
                    }
                }
            }
        }
        for ($i = $n - 1; $i >= 0; $i--) {//计算逆矩阵
            $d = $mm[$i][$i];
            for ($k = $i; $k < ($n * 2); $k++) {
                $mm[$i][$k] = $mm[$i][$k] / $d;
            }
            for ($j = ($i - 1); $j >= 0; $j--) {
                $d = -$mm[$j][$i];
                for ($k = $i; $k < ($n * 2); $k++) {
                    $mm[$j][$k] = $mm[$j][$k] + $d * $mm[$i][$k];
                }
            }
        }
        for ($i = 0; $i < $n; $i++) {
            for ($k = $n; $k < ($n * 2); $k++) {
                $b[$i][$k - $n] = $mm[$i][$k];
//echo $b[$i][$k-$n]." ";
            }
//echo "<br>";
        }
        return $b;
    }
}

//$a=array(0=>array(2,1),1=>array(1,1));
//$a=array(0=>array(0,2,3,4),1=>array(1.2,3.2,3.2,1.1),2=>array(4.3,5.4,2.7,3.1),3=>array(1.5,6,7,5));
//$b=matrix_inverse($a);
//print_r($b);
function juzhen_zhi($mm) { //计算方阵的行列式
    $n = count($mm); //判断是否为方阵
//echo $n;
    $m = count($mm[0]);
//echo $m;
    if ($m != $n)
        exit("该矩阵不能求行列式，因为行数和列数不相同！！");
    else {
        for ($i = 0; $i < $n; $i++) {//利用列主元法来换行，使对角线上元素非0
            if ($mm[$i][$i] == 0) {
                for ($j = ($i + 1); $j < $n; $j++) {
                    if ($mm[$j][$i] != 0) {
                        $count = 0;
                        for ($k = $i; $k < $n; $k++) {
                            $temp = $mm[$j][$k];
                            $mm[$j][$k] = $mm[$i][$k];
                            $mm[$i][$k] = $temp;
                            $count++;
//echo $count;
                        }
                        break;
                    }
                }
            }
            if ($mm[$i][$i] == 0)
                return 0; //将该矩阵化为上三角矩阵
            for ($j = ($i + 1); $j < $n; $j++) {
                if ($mm[$j][$i] != 0) {
                    $c = -$mm[$j][$i] / $mm[$i][$i];
                    for ($k = $i; $k < $n; $k++) {
                        $mm[$j][$k] = $mm[$j][$k] + $c * $mm[$i][$k];
                    }
                }
            }
        }
        $q = 1;
        for ($i = 0; $i < $n; $i++) {//判断换行的次数，计算行列式
            $q*=$mm[$i][$i];
        }
        if ($count % 2 == 0) {
            $d = $q;
        }
        else
            $d= - $q;

        return $d;
    }
}

// $a=array(0=>array(1,1,1,2),1=>array(3.5,34.3,34,54),2=>array(2,2,2,567),3=>array(5,6,55,66));
// echo juzhen_zhi($a);
?>