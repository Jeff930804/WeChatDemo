<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<style type="text/css">
	body, html,#allmap {width: 100%;height: 100%;overflow: hidden;margin:0;font-family:"微软雅黑";}
	</style>
	<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=z6XlrnVXYdxbzBgjUdZjW4b1C1KY9EDa"></script>
	<title>地图展示</title>
	<style type="text/css">
		#modalDialog {
			background-color: rgba(10,10,10,0.3);
			z-index: 100;
			position: absolute;
			width: 100%;
			height: 100%;
			display: none;
		}
		#modalDialog > div {
			position: relative;
			top: 100px;
		    width: 250px;
		    margin: 10% auto;
		    padding: 5px 20px 13px 20px;
		    border-radius: 10px;
		    background: #FFF;
		}
		#modalDialog > div > p {
			text-align: center;
		}
		.close {
		    background: #606061;
		    color: #FFFFFF;
		    line-height: 25px;
		    position: relative;
		    left: 262px;
		    text-align: center;
		    top: -10px;
		    width: 24px;
		    text-decoration: none;
		    font-weight: bold;
		    -webkit-border-radius: 12px;
		    -moz-border-radius: 12px;
		    border-radius: 12px;
		    -moz-box-shadow: 1px 1px 3px #000;
		    -webkit-box-shadow: 1px 1px 3px #000;
		    box-shadow: 1px 1px 3px #000;
		}
	</style>
</head>
<body>
	<div id="modalDialog">
	    <div>
	        <p id="text"></p>
	    </div>
	</div>
	<div id="allmap"></div>
	
</body>
</html>
<script type="text/javascript">
	var 
	// 百度地图API功能	
	map = new BMap.Map("allmap");
	var mPoint = new BMap.Point(116.482733,39.861642);
	map.centerAndZoom(mPoint, 16);
	var circle = new BMap.Circle(mPoint,300,{fillColor:"blue", strokeWeight: 1 ,fillOpacity: 0.3, strokeOpacity: 0.3});
    map.addOverlay(circle);
	map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放

	var data_info = [[116.482733,39.861642,"北京市朝阳区十里河大羊坊路37号闽龙广场"]];
	var ren = ['A','B','C','D','E','F','G','H','I','J'];
	for (var i=0,count=ren.length;i<count;i++) {
		var x = 116.48+(Math.random()/100);
		var y = 39.86+(Math.random()/100);
		data_info.push([x,y,'接单员'+ren[i]]);
	}
	var opts = {
				width : 50,     // 信息窗口宽度
				height: 60,     // 信息窗口高度
				title : "仓库" , // 信息窗口标题
				enableMessage:true//设置允许信息窗发送短息
			   };
	for(var i=0;i<data_info.length;i++){
		var marker = new BMap.Marker(new BMap.Point(data_info[i][0],data_info[i][1]));  // 创建标注
		var content = data_info[i][2];
		map.addOverlay(marker);               // 将标注添加到地图中
		if (i==0) {
			addClick(content,marker);
		} else {
			addClickHandler(content,marker);
		}
	}
	function addClickHandler(content,marker){
		marker.addEventListener("click",function(e){
			var pointA = new BMap.Point(e.currentTarget.point.lng,e.currentTarget.point.lat);
			var pointB = new BMap.Point(116.482733,39.861642);  // 创建点坐标B--江北区
			if(map.getDistance(pointA,pointB).toFixed(0) > 300) {
				show('亲，离得太远了哦，看看附近有没有接单员')
			} else {
				show('派件成功')
				map.removeOverlay(this);

			}  //获取两点距离,保留小数点后两位
			}
		);
	}
	function addClick(content,marker){
		marker.addEventListener("click",function(e){
			openInfo(content,e);
		});
	}
	function openInfo(content,e){
		var p = e.target;
		var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
		var infoWindow = new BMap.InfoWindow(content,opts);  // 创建信息窗口对象 
		map.openInfoWindow(infoWindow,point); //开启信息窗口
	}
	function show(str) {
		var mod = document.getElementById('modalDialog');
		var p = document.getElementById('text');
		p.innerHTML = str;
		mod.style.display = 'block';
		setTimeout("timedCount()", 666);
	}

	function timedCount() {
        var mod = document.getElementById('modalDialog');
        mod.style.display = 'none';
    }
</script>
