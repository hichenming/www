var LOAD_ALMFILELIST = '正在加载星历文件列表...'; //IE不支持const
var LOAD_SATLIST = '正在加载卫星数据......';

var ALMLIST_URL = 'coreHander.php?code=1'; //星历文件列表URL
var SATDATA_URL = 'coreHander.php?code=2&name='; //星历数据URL
var FLASH_DATA_URL = location.href.substring(0,location.href.lastIndexOf('/'))+'/coreHander.php?code=3&';
var NUM_DATA_URL = 'coreHander.php?code=4';
var ELE_DATA_URL = 'coreHander.php?code=5';
var DOP_DATA_URL = 'coreHander.php?code=6';
var curUrl = location.href;
if(curUrl.indexOf(":81") != -1) {  //81端口时绑定ASP.NET后台
	ALMLIST_URL = 'orbitPoints.ashx?code=3'; //星历文件列表URL
	SATDATA_URL = 'orbitPoints.ashx?code=4&name='; //星历数据URL
	FLASH_DATA_URL = location.href.substring(0,location.href.lastIndexOf('/'))+'/orbitPoints.ashx?code=5&';
	NUM_DATA_URL = 'orbitPoints.ashx?code=6';
	ELE_DATA_URL = 'orbitPoints.ashx?code=7';
	DOP_DATA_URL = 'orbitPoints.ashx?code=8'
}


//object类型转字符串
function obj2str(o) {
	var r = [];
	if(typeof o =="string")
		return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";
	if(typeof o == "object") {
		if(!o.sort) {
			for(var i in o)
				r.push(i+":"+obj2str(o[i]));
			if(!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)) {
				r.push("toString:"+o.toString.toString());
			}
			r="{"+r.join()+"}"
		} else {
			for(var i =0;i<o.length;i++)
				r.push(obj2str(o[i]))
			r="["+r.join()+"]"
		}
		return r;
	}
	return o.toString();
}

//json字符串转object
function str2obj(json) {
	return eval("(" + json + ")");
}
//获取flash Object
function thisMovie(movieName) {
     if (navigator.appName.indexOf("Microsoft") != -1) {
         return window[movieName]
     }
     else {
         return document[movieName]
     }
}