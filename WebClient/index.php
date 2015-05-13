<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>(☝ ՞ਊ ՞)☝ウイーン</title>
		<script type="text/javascript" src="jquery.js"></script>
		<script type="text/javascript" src="createjs.js"></script>
		<script type="text/javascript" src="socket.io.js"></script>
		<script type="text/javascript">
			$(function() { 
				/* 描画するcanvasの指定 */
				var canvas = document.getElementById("mainCanvas");
				/* canvasをステージに関連付ける */
				var stage = new createjs.Stage( canvas );
				/* 動かすShapeの宣言 */
				var tray = new createjs.Shape(); 
				var trayPanel = new createjs.Shape(); 
				var leadLamp = new createjs.Shape(); 
				// var movingText = new createjs.Text("(☝ ՞ਊ ՞)☝ウイーン","36px sans", "black");
				var clickText = new createjs.Text("CLICK HERE!!","20px Impact", "black");
				var winImage = new createjs.Bitmap("wi-n.png"); 
				// var waitText なるものが合ったはず
				var waitImage = new createjs.Bitmap("matetea.png"); 
				var loadingImage = new createjs.Bitmap("loading.png"); 
				var rotateArc = new createjs.Bitmap("loading_arc.png"); 
				var rotateFlare = new createjs.Bitmap("loading_flare.png"); 
				var rotateAkirame = new createjs.Bitmap("loading_akirame.png"); 
				var canvas_background = new createjs.Shape();
				var flashBackground = new createjs.Shape();
				/* トレイの開閉フラグ 最初の描画が行われればtrueに */
				var drow_canvasFlag = false; 
				/* trueならtryEjectの送信が可能 Receive,Activeの受信で切り替え */
				var tryFlag = false; 
				
				/*現在のトレイの状態を記録*/
				var status;
				
				/* socketの設定 */
				var socket = io.connect('http://example.com:3000');
				
				$(document).ready(function() {
					// canvasの初期描画
					init_canvas(); 
					// Statusの取得命令
					socket.emit("getStatus", {value:"getStatus"});
				})
				
				$("#content").click(function() {
					/* 命令送信可能であれば */
					if (tryFlag) {
						//alert("tryEject");
						/* canvasが押されたら */
						if (drow_canvasFlag) {
							socket.emit("tryEject", {value:"tryEject"});
						}
						
						stage.addChild(flashBackground);
						/* クリックを押したら光らせる */
						flashBackground.alpha = 0.75; 
						createjs.Tween.get(flashBackground).to({alpha:0}, 500);
						createjs.Ticker.setFPS(30);
						createjs.Ticker.addEventListener("tick", stage);
						stage.update();
					}
				})
				
				function init_canvas() {
					/* アクセス時の初期ロード画面 */
					/* 背景色を描画 */
					canvas_background.graphics.beginFill("black");
					canvas_background.graphics.drawRect(0, 0, 600, 550);
					stage.addChild(canvas_background);
					/* クリック用の背景色 */
					flashBackground.graphics.beginFill("white");
					flashBackground.alpha = 0; 
					flashBackground.graphics.drawRect(0, 0, 600, 550);
					stage.addChild(flashBackground);
					
					/* アクセス時の描画テキスト */
					stage.addChild(loadingImage); 
					
					/* 右下で回転してる奴 */
					rotateArc.x = 500; 
					rotateArc.y = 460; 
					stage.addChild(rotateArc); 
					rotateFlare.x = 525; 
					rotateFlare.y = 485; 
					rotateFlare.regX = 25; 
					rotateFlare.regY = 25; 
					stage.addChild(rotateFlare); 
					createjs.Tween.get(rotateFlare).to({rotation:3780}, 10000);
					rotateAkirame.x = 500; 
					rotateAkirame.y = 460; 
					rotateAkirame.alpha = 0; 
					stage.addChild(rotateAkirame); 
					createjs.Tween.get(rotateAkirame).wait(10000).set({alpha:1});
					
					createjs.Ticker.setFPS(30);
					createjs.Ticker.addEventListener("tick", stage);
					stage.update();
				}
				
				function drow_canvas() {
					/* statusが返ってきたら描画 */
					/* 背景色(四角)を描画 */
					canvas_background.graphics.beginFill("#E0FFF7");
					canvas_background.graphics.drawRect(0, 0, 600, 550);
					stage.addChild(canvas_background);
					
					/* 天井パネルの描画 逆三角形+逆三角形 */
					var topPanelTri = new createjs.Shape(); 
					topPanelTri.graphics.beginFill("#DCDCDC"); 
					// x座標 y座標 半径(1辺の長さではない) 角の数 絞り 角度
					/*
						! メモ !
						半径50pxの三角形とは、三角形の重心から頂点までの距離が50pxの三角形
						重心は三角形の2:1の位置にある(オイラーの定理より 半径50pxの場合、三角形の高さは75pxになる)
						ここで求められた高さと、θ=60度という情報から
						http://keisan.casio.jp/exec/system/1260247765
						を使って斜辺または底辺を求めて1辺の長さを求める
						己の数学のできなさがよくわかる
					*/
					topPanelTri.graphics.drawPolyStar(150, 50, 50, 3, 0, 30);
					topPanelTri.graphics.drawPolyStar(450, 50, 50, 3, 0, 30);
					// 縦にN倍拡大
					topPanelTri.scaleY = 4; 
					stage.addChild(topPanelTri); 
					/* 天井パネルの描画 三角形の間の四角 */
					var topPanelSqu = new createjs.Shape(); 
					topPanelSqu.graphics.beginFill("#DCDCDC"); 
					topPanelSqu.graphics.drawRect(150, 0, 300, 300); 
					stage.addChild(topPanelSqu); 
					
					/* 手前のパネルの描画 */
					var frontPanel = new createjs.Shape(); 
					frontPanel.graphics.beginFill("#CCCCCC"); 
					frontPanel.graphics.drawRect(107, 300, 386, 75); 
					stage.addChild(frontPanel); 
					
					/* ランプ(消灯の描画) */
					var sleepLamp = new createjs.Shape(); 
					sleepLamp.graphics.beginFill("#CAC991"); 
					sleepLamp.graphics.drawRect(120, 360, 30, 10); 
					stage.addChild(sleepLamp); 
					
					/* ランプ(点灯の描画 初期では非表示) */
					leadLamp.graphics.beginFill("#F8F560"); 
					leadLamp.graphics.drawRect(120, 360, 30, 10); 
					leadLamp.alpha = 0; 
					stage.addChild(leadLamp); 
					
					/* トレイが格納される穴の描画 */
					var frontHole = new createjs.Shape(); 
					frontHole.graphics.beginFill("#868686"); 
					frontHole.graphics.drawRect(150, 320, 300, 30); 
					stage.addChild(frontHole); 
					
					/* トレイ(close)の描画 */
					tray.graphics.beginFill("#9C9C9C"); 
					tray.graphics.drawRect(160, 335, 280, 15); 
					stage.addChild(tray); 
					
					/* トレイのパネル(手前の板)の描画 */
					trayPanel.graphics.beginFill("#E7E7E7"); 
					trayPanel.graphics.drawRect(140, 310, 320, 40); 
					stage.addChild(trayPanel); 
					
					/* CLICK HERE!!の描画 */
					stage.addChild(clickText); 
					
					/* うぃーんの描画 */
					stage.addChild(winImage);
					
					/* waitの描画 */
					waitImage.alpha = 0; 
					stage.addChild(waitImage);
					
					/* ステージを更新 */
					stage.update();
				}
				
				socket.on("Status", function(data){
					/* Statusが返ってきた時 */
					if (!drow_canvasFlag) {
						/* topPanel等の初期描画 */
						drow_canvas(); 
						drow_canvasFlag = true; 
					}
					ejectAction(data.code); 
				});
				
				socket.on("Active", function(data) {
					/* Active(コマンド受付可能)になった時 */
					//alert(data.code);
					if(data.code == 1){	
						tryFlag = true;
						createjs.Tween.get(waitImage).set({alpha:0}); 
					}else{
						tryFlag = false;
						createjs.Tween.get(waitImage).set({alpha:1}); 
					}
					
					createjs.Ticker.setFPS(30);
					createjs.Ticker.addEventListener("tick", stage);
					//stage.addChild(waitText);
					stage.update();
				}); 
				
				function ejectAction(ejectStatus) {
					var interval = 1000;
					/* Statusの内容からアニメーションを操作 */
					if (ejectStatus >= 0) {
						if(status != ejectStatus){
							status = ejectStatus;
							if (ejectStatus == 1) {
								/* トレイが閉じている時 */
								trayPanel.y = 200; 
								createjs.Tween.get(trayPanel).to({y:0}, interval);
								tray.scaleY = 13; 
								tray.y = -4355 + 200 + 135; 
								createjs.Tween.get(tray).to({scaleY:1, y:0}, interval);
								// ランプの点灯・消灯
								createjs.Tween.get(leadLamp).wait(2000).set({alpha:1}); 
								createjs.Tween.get(leadLamp).wait(3500).set({alpha:0}); 
							}
							else if (ejectStatus == 0) {
								/* トレイが開いている時 */
								trayPanel.y = 0; 
								createjs.Tween.get(trayPanel).to({y:200}, interval);
								tray.scaleY = 1; 
								tray.y = 0; 
								createjs.Tween.get(tray).to({scaleY:13, y:-4355 + 200 + 135}, interval);
							}
							/* うぃーんを流す */
							winImage.x = 450; 
							createjs.Tween.get(winImage).to({x:-450}, interval * 1.5);
							createjs.Ticker.setFPS(30);
							createjs.Ticker.addEventListener("tick", stage);
							stage.update();
						}
					}
					else {
						/* エラーが発生した場合 */
						canvas_background.graphics.beginFill("red");
						canvas_background.graphics.drawRect(0, 0, 600, 550);
						stage.addChild(canvas_background);
						stage.update();
						alert("エラーが発生しました。\nお近くの変態までお知らせください。\n"); 
					}
				}
			})
		</script>
		<link rel="stylesheet" type="text/css" href="pagestyle.css" />
	</head>
	
	<body>
		<div id="content">
			<canvas id="mainCanvas" width="600px" height="550px"></canvas>
		</div><!-- content -->
		<div id="howto">
			<div class="subtitle">[EjectCarのあそびかた！]</div>
			ページを開くとEjectCarに接続するまで黒い画面が表示されます。<br />
			しばらく変化が起きない時はサーバやEjectCarが止まっている可能性があるので、しばらく待って再接続を行うか諦めてください。<br />
			接続ができると光学ドライブを模したなにかが表示されます。<br />
			canvas内をクリックすると光学ドライブに開閉命令が送信され、実際にEjectCarで動作が行われるとwebページ上でも開閉をしているようなアニメーションが再生されます。<br />
			また、開閉命令はN秒に1回だけ受理されます。(ソースのバージョン次第)<br />
			命令が受理できない(Ejectが不可能)の時はメッセージを表示します。<br />
			連打を行っても処理に影響はしないと考えられますが、なるべく控えてください。<br />
			<div class="subtitle">[EjectCarとは！]</div>
			EjectCarとは光学ドライブの開閉動力を用いてwebページ上から自走操作を行える<a href="http://eject.kokuda.org/" target="_blank">Eject工作</a>です。<br />
			こちらは2015年5月のOpenSourceConference(OSC)名古屋にて実演されます。<br />
			またEjectCarに関する技術的なことはたぶん<a href="tech/">こちら</a>に記載しています。<br />
		</div><!-- howto -->
		<address style="text-align:center;">
			Copyright&copy;Progress:is:Useless All Rights Reserved.<br />
			<a href="https://twitter.com/kamaboko123" target="_blank">Kamaboko</a> &amp; <a href="https://twitter.com/rettar5" target="_blank">Rettar</a><br />
		</address>
	</body>
</html>
