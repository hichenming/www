/**
 * @author chenming
 */
$( function() {
	//========================绑定事件
	$('#satChoose').click( function() {
		$('#dhtmlgoodies_floating_window0').show('fast');
		$(this).addClass('selected');
	});
	$('#displayConfig').click( function() {
		$('#dhtmlgoodies_floating_window1').show('fast');
		$(this).addClass('selected');
	});
	$('#viewInfo').click( function() {
		$('#dhtmlgoodies_floating_window2').show('fast');
		$(this).addClass('selected');
	});
	//选择卫星重新绘图
	$('#satRedrawBtn').click( function() {
		if(viewMode == '2d') {
			drawOnBackground();
		} else if(viewMode == '3d') {
			drawFlashOrbit();
		}
	});
	//卫星全选
	$('#selectAllSat').click( function() {
		$('#satList input:checkbox[checked=false]').each( function() {
			$(this).attr('checked','true');
		});
	});
	//卫星反选
	$('#deSelectSat').click( function() {
		$('#satList input:checkbox').each( function() {
			$(this).attr('checked',!$(this).attr('checked'))
		});
	});
	//加载星历
	$('#loadSelectAlm').click( function() {
		if($('#chartdiv').css('display') == 'none')
			loadAlmData($('#almList input:radio[name="almRadio"][checked="true"]').val(), false);
		else{ //操作图表
			curTleFile = $('#almList input:radio[name="almRadio"][checked="true"]').val();
			$('#setApplyBtn1').click();
		}
	});
	$('#viewSatOrbit').click(function(){
		$('#flashwrapper').hide();
		$('#canvas1').show();
		$('#chartdiv').hide();
	});
	//查看三维
	$('#view3Dearth').click( function() {
		if(viewMode == '2d') {
			switchToMode('3d');
			$(this).addClass('selected');
			viewMode = '3d';
		} else if(viewMode == '3d') {
			switchToMode('2d');
			$(this).removeClass('selected');
			viewMode = '2d';
		}
	});
	//查看卫星可见数分步图
	$('#viewNumChart').click( function() {
		$('#flashwrapper').hide();
		$('#canvas1').hide();
		$('#chartdiv').show();
		curTleFile = $('#almList input:radio[name="almRadio"][checked="true"]').val();
		zingchart.render({
			'id' : 'chartdiv',
			'width' : canvas.width,
			'height' : canvas.height,
			'dataurl' : NUM_DATA_URL+'&file='+curTleFile+'&lon='+$('#satlon').val()+'&lat='+$('#satlat').val()+'&alt='+$('#satalt').val()+'&date='+$('#calcDate').val()+'&t='+Math.random()
		});
		curChartId = 1;
	});
	//查看高角度分布图
	$('#viewEleChart').click(function(){
		$('#flashwrapper').hide();
		$('#canvas1').hide();
		$('#chartdiv').show();
		curTleFile = $('#almList input:radio[name="almRadio"][checked="true"]').val();
		zingchart.render({
			'id' : 'chartdiv',
			'width' : canvas.width,
			'height' : canvas.height,
			'dataurl' : ELE_DATA_URL+'&file='+curTleFile+'&lon='+$('#satlon').val()+'&lat='+$('#satlat').val()+'&alt='+$('#satalt').val()+'&date='+$('#calcDate').val()+'&t='+Math.random()
		});
		curChartId = 2;
	});
	//查看DOP值分布图
	$('#viewDopChart').click(function(){
		$('#flashwrapper').hide();
		$('#canvas1').hide();
		$('#chartdiv').show();
		curTleFile = $('#almList input:radio[name="almRadio"][checked="true"]').val();
		zingchart.render({
			'id' : 'chartdiv',
			'width' : canvas.width,
			'height' : canvas.height,
			'dataurl' : DOP_DATA_URL+'&file='+curTleFile+'&lon='+$('#satlon').val()+'&lat='+$('#satlat').val()+'&alt='+$('#satalt').val()+'&date='+$('#calcDate').val()+'&t='+Math.random()
		});
		curChartId = 3;
	});
	//设置->应用
	$('#setApplyBtn1').click(function(){
		if($('#chartdiv').css('display')!='none')
			if(curChartId == 1)
				$('#viewNumChart').click();
			else if(curChartId == 2)
				$('#viewEleChart').click();
			else if(curChartId == 3)
				$('#viewDopChart').click();
	});
	
	today=new Date();
	$('#calcDate').val(today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate());
	$('#calcDate').datepicker({
		beforeShow: function () {
			setTimeout( function () {
				$('#ui-datepicker-div').css("z-index", 15000);
			}, 100
			);
		},
		dateFormat: 'yy-mm-dd'
	});
	//==============初始化加载星历与星座，默认加载GPS
	$('#msgtext').html(LOAD_ALMFILELIST);
	$.ajax({
		type:'get',
		url:ALMLIST_URL,
		dataType:'json',
		success: function(data) {
			$almContainer = $('#almList');
			for(i=0; i<data.result.length; i++) {
				$almContainer.append('<input type="radio" id="almRadio" name="almRadio" value="'+data.result[i].name+'" />'+data.result[i].name+'<br />');
			}
			$('#almList input:radio[name="almRadio"][value="gps-ops.txt"]').attr('checked','true');
			$('#msgtext').html('');
			loadAlmData('gps-ops.txt', true);
		}
	});
});
//加载指定星历文件的数据
function loadAlmData(almName, isInit) {
	$('#msgtext').html(LOAD_SATLIST);
	$.ajax({
		type:'get',
		url:SATDATA_URL + almName,
		dataType:'json',
		success: function(data) {
			orbitJsonData = data;
			$('#satList').html('');
			for(i=0; i<orbitJsonData.sats.length; i++) {
				$('#satList').append('<input type="checkbox" value=\''+obj2str(orbitJsonData.sats[i])+'\' />'+orbitJsonData.sats[i].name+
				'<br />');
				if(i<5) { //默认勾选前五个
					$('#satList input:checkbox:last').attr('checked','true');
				}
			}
			curTleFile = almName; //设置当前星历文件变量
			if(viewMode == '3d') {
				drawFlashOrbit();
			} else {
				drawOnBackground();
			}
			$('#msgtext').html('');
		}
	});
}

//刷新功能导航条的样式
function freshNavClass(i) { //第i个窗口关闭，第i个div样式复原
	$('#toolnav div:eq('+i+')').removeClass('selected');
}

var firstLoadFlash = true;
//切换2D、3D视图
function switchToMode(mode) {
	if(mode=='2d') {
		$('#flashwrapper').hide();
		$('#chartdiv').hide();
		$('#canvas1').show();
	} else if(mode=='3d') {
		$('#chartdiv').hide();
		$('#canvas1').hide();
		$('#flashwrapper').show();
		if(firstLoadFlash) { //第一次加载flash
			firstLoadFlash = false;
			var flashvars = {
			};
			var params = {
				menu: "false",
				wmode: "transparent"
			};
			var attributes = {
				id: "flashwrapper",
				name: "flashwrapper",
				allowScriptAccess: "always"
			};
			swfobject.embedSWF("images/FlexProject1.swf","flashwrapper",canvas.width,canvas.height,"9.0.0","expressInstall.swf", flashvars, params, attributes);
		}
	}
}