//======画背景图形
function drawBackground(isInit) { //isInit是否为页面第一次加载第一次画
	canvas.width = canvas.width; //清空画布
	var img = new Image();
	img.addEventListener('load', function () {
		var x = outWidth, y = outWidth;
		context.drawImage(this, outWidth, outWidth, worldWidth, worldHeight);
		var imgd = context.getImageData(x, y, worldWidth, worldHeight);  //下面做滤镜处理
		var pix = imgd.data;
		for (var i = 0, n = pix.length; i < n; i += 4) {
			if(pix[i] == 0) {
				pix[i  ] = 43; // red
				pix[i+1] = 14; // green
				pix[i+2] = 177; // blue
			} else {
				pix[i  ] = 6; // red
				pix[i+1] = 43; // green
				pix[i+2] = 210; // blue
			}
		}
		context.putImageData(imgd, x, y);
		//drawCopyright();
		drawGrid();
		canvasData = context.getImageData(0,0,canvas.width,canvas.height);
	}, false);
	img.src = 'images/World-Standard.bmp';
}

//在已有的背景图下画orbit及sats
function drawOnBackground() {
	context.putImageData(canvasData,0,0);
	drawOrbit();
	drawStars();
}

//==============画一颗卫星
function drawSat(x, y, name) {
	satImg = new Image();
	satImg.src = "images/Sat-Ball(small).bmp";

	context.drawImage(satImg, x-3, y-2, 6,6);
	context.fillStyle = "#fff";
	context.font = "normal 12px 'Microsoft Yahei',uming,sans-serif";
	context.fillText(name, x+5, y+4);
}

function drawCopyright() {
	context.fillStyle = "#fff";
	context.font = "normal 12px 'Microsoft Yahei',uming,sans-serif";
	context.fillText('@2011 武汉大学测绘学院 陈明 毕业设计作品', canvas.width-250, canvas.height-10);
}

//===========画坐标格网
function drawGrid() {
	context.fillStyle = "#fff";
	context.font = 'normal 12px "Microsoft Yahei",uming,sans-serif';
	var offset = 3;
	var offset2 = 5;
	context.fillText("0", 0, worldHeight/2+outWidth + offset);
	context.fillText("30", 0, worldHeight/3+outWidth + offset);
	context.fillText("30", 0, worldHeight/3*2+outWidth + offset);
	context.fillText("60", 0, worldHeight/6+outWidth + offset);
	context.fillText("60", 0, worldHeight/6*5+outWidth + offset);
	context.fillText("0", worldWidth/2+outWidth - offset, outWidth);
	context.fillText("30", worldWidth/12*5+outWidth - offset2, outWidth);
	context.fillText("30", worldWidth/12*7+outWidth - offset2, outWidth);
	context.fillText("60", worldWidth/12*4+outWidth - offset2, outWidth);
	context.fillText("60", worldWidth/12*8+outWidth - offset2, outWidth);
	context.fillText("90", worldWidth/12*3+outWidth - offset2, outWidth);
	context.fillText("90", worldWidth/12*9+outWidth - offset2, outWidth);
	context.fillText("120", worldWidth/12*2+outWidth - 7, outWidth);
	context.fillText("120", worldWidth/12*10+outWidth - 7, outWidth);
	context.fillText("150", worldWidth/12+outWidth - 7, outWidth);
	context.fillText("150", worldWidth/12*11+outWidth - 7, outWidth);
	context.fillStyle = "rgb(144,144,144)";
	context.fillText("N", 6, worldHeight/12 + outWidth);
	context.fillText("N", 6, worldHeight/12*5 + outWidth);
	context.fillText("S", 6, worldHeight/12*7 + outWidth);
	context.fillText("S", 6, worldHeight/12*11 + outWidth);
	context.fillText("W", worldWidth/24 + outWidth, outWidth);
	context.fillText("W", worldWidth/24*11 + outWidth, outWidth);
	context.fillText("E", worldWidth/24*13 + outWidth, outWidth);
	context.fillText("E", worldWidth/24*23 + outWidth, outWidth);

	context.strokeStyle = "rgb(69,158,231)";
	context.lineWidth = 0.5;
	context.beginPath();
	for(i=1; i<=6; i++) {
		context.moveTo(outWidth, worldHeight/6*i+outWidth);
		context.lineTo(canvas.width, worldHeight/6*i+outWidth);
	}
	for(i=1; i<=12; i++) {
		context.moveTo(worldWidth/12*i+outWidth, outWidth);
		context.lineTo(worldWidth/12*i+outWidth, canvas.width);
	}
	context.stroke();
}

//=============画卫星轨道，经度[-180,180]，纬度[-90,90]
function drawOrbit() {
	context.strokeStyle = 'rgb(255,255,304)';
	for(i=0; i<orbitJsonData.points.length; i++) {
		if(i>0) {
			var x1,x2,y1,y2;
			if(orbitJsonData.points[i].l<180) {
				x1 = canvas.width / 360.0 * (orbitJsonData.points[i].l -1+ 181) + outWidth;
				x2 = canvas.width / 360.0 * (orbitJsonData.points[i-1].l -1+ 181) + outWidth;
			} else {
				x1 = canvas.width / 360.0 * (orbitJsonData.points[i].l - 180) + outWidth;
				x2 = canvas.width / 360.0 * (orbitJsonData.points[i-1].l - 180) + outWidth;
			}
			y1 = canvas.height / 180.0 * (-orbitJsonData.points[i].b -1+ 91) + outWidth;
			y2 = canvas.height / 180.0 * (-orbitJsonData.points[i-1].b -1+ 91) + outWidth;
			if( Math.abs(x1-x2)<=250 && Math.abs(x1-x2)<=250 && x1>outWidth && y1>outWidth && x2>outWidth && y2>outWidth) {
				context.beginPath();
				context.moveTo(x1, y1);
				context.lineTo(x2, y2);
				context.stroke();
			}
		}
	}
}

//===========画选中的卫星
function drawStars() {
	$('#satList input:checkbox[checked=true]').each( function() {
		var data = str2obj($(this).val());
		var x,y;
		if(data.l<180) {
			x = canvas.width / 360.0 * (data.l -1+ 181) + outWidth;
		} else {
			x = canvas.width / 360.0 * (data.l - 180) + outWidth;
		}
		y = canvas.height / 180.0 * (-data.b-1+91) + outWidth;
		drawSat(x, y, data.name);
	});
}

//=========画FLASH轨道
function drawFlashOrbit () {
	var strname = '';
	var i = 1;
	$('#satList input:checkbox[checked=true]').each( function() {
		if (i>1) strname += ',';
		i++;
		var data = str2obj($(this).val());
		strname += data.name;
	});
	var url = FLASH_DATA_URL+'file='+curTleFile+'&name='+strname;
	thisMovie('flashwrapper').drawOrbit(url);
}