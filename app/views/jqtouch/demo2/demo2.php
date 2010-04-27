<?php session_start() ?>
<?php 
    if (isset($_REQUEST['_SESSION'])) die("Get lost Dweeb!");
	$_SESSION['filterStatus']=0; // 0= none; 1=room; 2=artist; 3=technique; 4=style
	$_SESSION['tempArtistFilterId']=0;
	$_SESSION['tempStyleFilterId']=0;
	$_SESSION['tempTypeFilterId']=0;
	$_SESSION['tempRoomFilterId']=0;
	$_SESSION['artistFilterId']=0;
	$_SESSION['styleFilterId']=0;
	$_SESSION['typeFilterId']=0;
	$_SESSION['roomFilterId']=0;
	$_SESSION['galleryId']=0;
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" id="iphone-viewport" content="minimum-scale=1.0, maximum-scale=1.0, width=device-width" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
<!--    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> -->
		<title>Demo</title>
        <style type="text/css" media="screen">@import "jqtouch/jqtouch.css";</style>
        <style type="text/css" media="screen">@import "themes/jqt/theme.css";</style>
        <style type="text/css" media="screen">@import "jqtouch/zflow.css";</style>
		<style type="text/css" media="screen">@import "jqtouch/spinningwheel.css";</style>


   	    <script src="jqtouch/jquery-1.4.2.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="jqtouch/jqtouch.js" type="application/x-javascript" charset="utf-8"></script>
        <script src="jqtouch/zflow.js" type="application/x-javascript" charset="utf-8"></script>
		<script src="extensions/jqt.scrolling.js" type="text/javascript"></script>
		<script type="text/javascript" src="jqtouch/mod_spinningwheel.js?v=1.4"></script>
		<script type="text/javascript" charset="utf-8">
			var galleryInit = 0;
			var galleryCell = 0;
			var hunting = 0;
			var jQT = new $.jQTouch({
				icon: 'images/museum.png',
                addGlossToIcon: true,
                startupScreen: 'museum_startup.png',
				statusBar: 'black',
				cacheGetRequests: false,
				slideSelector:'.slide-right',
				preloadImages: [
                    'images/opacity_4.png',
					'images/back_button.png',
					'images/back_button_clicked.png',
					'images/button_clicked.png',
					'images/grayButton.png',
					'images/whiteButton.png',
					'images/loading.gif',
					'images/icon_user.png',
					'images/icon_users.png',
					'images/palette.png',
					'images/picture-frame.png',
					'images/map.png',
					'images/map_grey.png',
					'images/pic_tech.png',
					'images/icon_case.png',
					'images/house.png',
					'images/gear2.png',
					'images/photos.png',
					'images/clapboard.png',
					'images/tools.png'
					]
            });

			//you will find that jQTouch fails to appropriately identify the initial orientation
			//do to the fact that not all components are loaded before firing $.ready
			$(window).load(function()
			{
				$(document.body).trigger('orientationchange');
			});

			// show image event
			$('#show_image a').tap(function(){
				jQT.goBack();	
				return false;
			});

			// Some Javascript functions:
            $(function(){
				// Orientation callback event
                $('#jqt').bind('turn', function(e, data){
					$div = $(".current").attr("id");
					if (data.orientation == 'landscape'){
						$("#tabbar").hide();
					} else if (($div != 'show_image') && ($div != 'floor_plan') && ($div != 'gallery') && ($div != 'hunt')) {
						if ((hunting==0)) { 
							$("#tabbar").show();
						}
					}
					if ($div == 'gallery') {
						if (data.orientation == 'landscape'){
							jQuery("div.centering").attr("class", "centering landscape");
						} else {
							jQuery("div.centering").attr("class", "centering portrait");
						}
						window.setTimeout( function() { window.scrollTo(0, 0); }, 100 );
					}
                });
                // Page animation callback events
                $('#jqt').bind('pageAnimationStart', function(e, info){
						$("#switching").removeClass("hide_switching").addClass("show_switching");
						var myOrient = jQT.getOrientation();
						if  (e.target.id == 'home'){
							if (myOrient != 'landscape') {$("#tabbar").show();}
							hunting=0;
						}
						if ((e.target.id == 'show_image')||(e.target.id == 'floor_plan')||(e.target.id == 'gallery')||(e.target.id == 'hunt')) 
						{
							if (info.direction =='in')
							{
	                   			$("#tabbar").hide();
							} else if ((myOrient != 'landscape') && (hunting==0)) {
	                   			$("#tabbar").show();
							}
						}
						if  ((e.target.id == 'gallery') && (galleryInit == 0) && (info.direction =='in')){
						    flickr(function (images) {
						        zflow(images, "#tray");
						    });
						} else if  (e.target.id == 'hunt'){
							if (info.direction =='in') {
								if (hunting==0) {
									chooseHunt();
									document.getElementById('sw-done').style.display="inline";
									document.getElementById('hunt_clue').style.display="none";
									document.getElementById('hunt_text').style.display="inline";
									document.getElementById('hunt_image').style.display="none";
									document.getElementById('clue_button').style.display="none";
								}
							} else if (hunting==0) {
								SpinningWheel.destroy();
							}
						}
						if ((e.target.id == 'gallery_list') && (galleryInit == 1) && (info.direction =='in')) {
							// DELETE THE PREVIOUS zflow
							zflowCleanup("#tray");
						}
                    }).
					bind('pageAnimationEnd', function(e, info){
						$("#switching").removeClass("show_switching").addClass("hide_switching");
						if (info.direction =='in') 
						{
							if (e.target.id == 'gallery') {
								var div = document.getElementById('show_image');
								if (div) {
									div.parentNode.removeChild(div);  //take the show_image out so it will reload
								}
							}
						}
					});
    		});

function flickr(callback)
{
    var url = "http://api.flickr.com/services/rest/?method=flickr.interestingness.getList&api_key=60746a125b4a901f2dbb6fc902d9a716&per_page=20&format=json&jsoncallback=?";
    
	jQuery.getJSON(url, function(data) 
	{
        var images = jQuery.map(data.photos.photo, function (item)
        {
            return 'http://farm' + item.farm + '.static.flickr.com/' + item.server + '/' + item.id + '_' + item.secret + '_m.jpg';
        });

        callback(images);
    });
}

		</script>

<style type="text/css" media="screen">
	#jqt .toolbar {
		background: url(images/toolbar.png) repeat-x;
	}

	#jqt #home, #jqt #more, #jqt #facilities, #jqt #artwork_styles, #jqt #artwork_types, #jqt #curators, #jqt #gallery_list {
		background: url(images/background.png) repeat-x;
	}

	#jqt #home ul.rounded , #jqt #more ul.rounded, #jqt #facilities ul, #jqt #artwork_styles ul.rounded,
		#jqt #artwork_types ul.rounded, #jqt #curators ul.rounded, #jqt #gallery_list ul {
		-webkit-background-clip: none;
		background: url(images/charcoal_opacity_8.png);
		background-color: none;
		color: none;
		border-width: 2px ;
	}

	#jqt #gallery_list h2 {
		-webkit-background-clip: none;
		background: url(images/charcoal_opacity_8.png);
		color: White;
	}

	#jqt #home li.arrow, #jqt #more li.arrow, #jqt #facilities li.arrow, #jqt #artwork_styles li.arrow,
		#jqt #artwork_types li.arrow, #jqt #curators li.arrow, #jqt #gallery_list li.arrow {
		-webkit-background-clip: none;
		background-image: url(images/charcoal_opacity_8.png), url(themes/jqt/img/chevron.png);
		background-repeat: repeat, no-repeat;
		background-color: none;
		border-width: 2px ;
	}

	.art_pic {
		max-width:320px;
		max-height:220px;
	}

	#jqt #home li.arrow {
		margin:0;
		padding-top:2px !important;
	}

	#jqt #home li img {
		position: relative;
		top: 5px;
		left: 0px;
		border:0;
		margin:0;
		padding-right: 10px;
	}

	#about {
		padding: 100px 10px 40px;
        text-shadow: rgba(255, 255, 255, 0.3) 0px -1px 0;
        font-size: 13px;
        text-align: center;
        background: #161618;
	}
    #about p {
    	margin-bottom: 8px;
    }
    #about a {
    	color: #fff;
		font-weight: bold;
        text-decoration: none;
    }
			.profile #show_image img 
			{
				max-width:320px !important;
				max-height:460px !important;
			}
			.landscape #show_image img
			{
				max-width:480px !important;
				max-height:300px !important;
			}
			.profile #floor_plan .vertical-scroll
			{
				height:410px;
				overflow:hidden;
			}
 			.profile .vertical-scroll, .vertical-slide
			{
				height:350px;
				overflow:hidden;
			}
			.landscape .vertical-scroll, .landscape .vertical-slide
			{
				height:250px;
				overflow:hidden;
			}
			#hunt p
			{
				text-align:center;
				font-size: 18px;
			}


		</style>

<!------------------------------------------------------------------------------------------------------------
 !   SpinningWheel: Styles 
 !------------------------------------------------------------------------------------------------------------>
	<style type="text/css" media="screen">
			
		#result { margin:10px;
			background:#aaa;
			-webkit-border-radius:8px;
			padding:8px;
			font-size:18px;
		}

	</style>
<!------------------------------------------------------------------------------------------------------------
 !   SpinningWheel: Script 
 !------------------------------------------------------------------------------------------------------------>
	<script type="text/javascript">
	function chooseHunt() {	
		var sw_facilities = {};
		var sw_type = {};
		var sw_style = {};
		$.getJSON("get_hunt_arrays.php?item=facility", function(sw_facilities){
			$.getJSON("get_hunt_arrays.php?item=type", function(sw_type){
				$.getJSON("get_hunt_arrays.php?item=style", function(sw_style){
					SpinningWheel.addSlot(sw_facilities, 'shrink');
					SpinningWheel.addSlot(sw_style, 'shrink');
					SpinningWheel.addSlot(sw_type, 'shrink');			
					SpinningWheel.setDoneAction(spin_done);		
					SpinningWheel.open();
					return false;
				}); //End sw_style
			}); //End sw_type
		}); //End sw_facilities
	}
		
	function spin_done() {		
		var results = SpinningWheel.getSelectedValues();
		$.getJSON("get_random_hunt.php", function(data){
			if (data.id == 0) {
				hunting = 0;
				alert("No items found that match your criteria");
			} else {
				hunting = 1;
				document.getElementById('sw-done').style.display="none";
				//set the clue link to the correct art id
//				document.getElementById('hunt_clue').href="art.php?id=" + data.id+"&type=hunt";
				document.getElementById('hunt_clue').style.display="inline";
				document.getElementById('hunt_text').style.display="none";
				//set the current page image to the art id
				document.getElementById('hunt_image').href="show_image.php?fName="+ data.picture;
				document.getElementById('hunt_image').getElementsByTagName('img')[0].src= data.picture;
				document.getElementById('hunt_image').style.display="inline";
				document.getElementById('clue_button').style.display="inline";
//				document.getElementById('clue_button_a').href="room.php?id=" + data.room;
				SpinningWheel.destroy();
			}

		});
	}


	</script>

<!------------------------------------------------------------------------------------------------------------
 !   Tabbar Control: Script 
 !------------------------------------------------------------------------------------------------------------>
	<script type="text/javascript" charset="utf-8">
		
		function tab_to (tab, page, transition)
		{
			$tab = 0;	
			if (tab.className == "tab_sel") return;
			if (tab.id == 'tab_1') {
				$tab=0;
			} else if (tab.id == 'tab_2') {
				$tab=1;
			} else if (tab.id == 'tab_3') {
				$tab=2;
			} else if (tab.id == 'tab_4') {
				$tab=3;
			} else if (tab.id == 'tab_5') {
				$tab=4;
			}

			$.getJSON("setfilter.php?filter="+$tab, function(data){
					if (data.result != 0 || $tab==0) {
						var $old_selected = $(".tab_sel");
						$temp = $('img', $old_selected).attr("src");
						$temp = $temp.replace("_selected", "");
						$('img', $old_selected).attr("src",$temp); //reset the current icon
						$old_selected.addClass("tab");
						$old_selected.removeClass("tab_sel");
						tab.className = "tab_sel";
						$("#switching").removeClass("hide_switching").addClass("show_switching");
						setTimeout("tab_to_2('" + page + "','"+ transition + "')", 100);
						if ($tab == 0) {
							document.getElementById('art_filter').innerHTML = "";
						} else {
							document.getElementById('art_filter').innerHTML = data.filter;
						}
						if (tab.id == 'tab_1') {
							tab.getElementsByTagName('img')[0].src="images/open_padlock_selected.png";
						} else if (tab.id == 'tab_2') {
							tab.getElementsByTagName('img')[0].src="images/house_selected.png";
						} else if (tab.id == 'tab_3') {
							tab.getElementsByTagName('img')[0].src="images/icon_users_selected.png";
						} else if (tab.id == 'tab_4') {
							tab.getElementsByTagName('img')[0].src="images/picture-frame_selected.png";
						} else if (tab.id == 'tab_5') {
							tab.getElementsByTagName('img')[0].src="images/palette_selected.png";
						}
					} else {
						alert("You must select a piece of artwork first!");
						document.getElementById('art_filter').innerHTML = "";
					}
  				});

		}

		function tab_to_2 (page, transition)
		{ 
			jQT.goTo(page, transition);
			$("#switching").removeClass("show_switching").addClass("hide_switching");
		}


	</script >

<!------------------------------------------------------------------------------------------------------------
 !   Tabbar Control: Styles
 !------------------------------------------------------------------------------------------------------------>
		<style type="text/css" media="screen">
	        div.tabbar {
				background: url(images/tabbar.png) #000000 repeat-x;
				margin:0px 0px 0px 0px;
				padding:0px 0px 0px 0px;            
				text-align:center; 
				position:absolute;
				bottom:0px;
				width:100%;
				height:64px;
        	}
			table.tabbar {
				width:100%;
				color:white;
				font: bold 11px/13px helvetica;
				height:44px;
				padding:0px;
				margin:0px;
			}
			table.art_filter {
				width:100%;
				color:black;
				font: bold 12px/14px helvetica;
				height:18px;
				padding:0px;
				margin:0px;
				background:darkgray;
			}
			td.tab {
				width:20%; 
				opacity:0.6;
			}
			td.tab_sel {
				width:20%; 
				height:44px;
				-webkit-border-radius:5px; 
				background:url(images/opacity_4.png) repeat; 
				opacity:1.0;
			}
        #switching {
            position: absolute;
            width: 15%;
			height:10%;
            top:45%; left:45%;
            background:url(images/ajax-loader-big.gif) white no-repeat center; 
            opacity:.6;
        }
        .show_switching {
            display: inline;
        }
        .hide_switching {
            display: none;
        }
	</style>

	</head>
	<body>



<!------------------------------------------------------------------------------------------------------------
 !   Web Page Divisions
 !------------------------------------------------------------------------------------------------------------>

	<div id="jqt">
<!------------------------------------------------------------------------------------------------------------>
		
		<div id="home" class="current">
			<div class="toolbar">
				<h1>Demo</h1>
				<a class="button slideup" id="infoButton" href="#about">About</a>
			</div>
			<div class="vertical-scroll">
			<div>
			<ul class="rounded">
				<li class="arrow"><a class="slide-right" href="#hunt"><img src="images/map.png"/>Spinning Wheel Demo</a></li>
			</ul>
			<ul class="rounded">
				<li class="arrow"><a class="slide-right" href="#gallery_list"><img src="images/clapboard.png"/>Zflow Demo</a> </li>
			</ul>
		</div>
		</div>
		</div>

       	<div id="about" class="selectable">
			<p><img src="images/museum.png" /></p>
 			<p><strong>Deno</strong><br />Version 1.0 beta<br />
			<a href="">By Chris Couper</a></p>
            <p><em>A tour of some JQT capabilities.</em></p>
            <p><br /><br />Copyright 2010 by<br />Christopher C. Couper</p>
            <p><br /><br /><a href="#" class="grayButton goback">Close</a></p>
        </div>

<!------------------------------------------------------------------------------------------------------------>
		<div id="hunt">
			<div class="toolbar">
                <h1>Scavenger Hunt</h1>
				<a class="back" href="#">Back</a>
				<a id="sw-done">Find</a>
				<a id="hunt_clue" style="display:none" class="button goTo slide-right" href="art.php?id=1">Clue</a>
            </div>
			<div>
				<p id="hunt_text">Choose the building, style and type of artwork and then press FIND.
				<br/><br/>You may rotate the device to see all of the choices.</p>
				<a id="hunt_image" class="pop" href="show_image.php?fName=images/artwork/blank.png">
				<img class="art_pic" src="images/artwork/blank.png" BORDER="0"/></a>
				<div id="clue_button">
					<ul class="rounded">
						<li><a id="clue_button_a" class="pop" href="room.php?id=1">Reveal Answer</a></li>
					</ul>
				</div>
			</div>
		</div>
<!------------------------------------------------------------------------------------------------------------>
		<div id="gallery_list">
			<div class="toolbar">
                <h1>Behind the Scenes</h1>
                <a class="back" href="#">Back</a>
            </div>
			<div class="vertical-scroll">
				<div>
				<ul class="rounded">
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
	    			<li class="arrow"><a class="slide-right" href="gallery.php?id=1"><img src="images/photos.png"/>&nbsp;Flickr Gallery</a> </li>
				</ul>
				</div>
			</div>
		</div>

<!------------------------------------------------------------------------------------------------------------
 !   Outside Divisions
 !------------------------------------------------------------------------------------------------------------>
	</div>	<!-- id="jqt" --> 
		<div id="outside">
	 		<div id="switching" class="hide_switching">
				&nbsp;
			</div>

			<div id="tabbar" class="tabbar">
				<table id="art_filter" class="art_filter"><tr align="center"><td></td></tr></table>
				<table class="tabbar"><tr align="center">
					<td id="tab_1" class="tab_sel" onclick="tab_to(this, '#home', 'flip');"><img src="images/open_padlock_selected.png" ><br>Unfiltered</td>
					<td id="tab_2" class="tab" onclick="tab_to(this, '#home', 'flip');"><img src="images/house.png" ><br>Room</td>
					<td id="tab_3" class="tab" onclick="tab_to(this, '#home', 'flip');"><img src="images/icon_users.png" ><br>Artist</td>
					<td id="tab_4" class="tab" onclick="tab_to(this, '#home', 'flip');"><img src="images/picture-frame.png" ><br>Artwork</td>
					<td id="tab_5" class="tab" onclick="tab_to(this, '#home', 'flip');"><img src="images/palette.png" ><br>Style</td>
				</tr></table>
			</div> 
		</div> <!-- id="outside" -->
	</body>
<!------------------------------------------------------------------------------------------------------------>

	<script type="text/javascript"> 
		$(document).ready(function() // Init - keeps the user from scrolling the panel
			{
			window.scrollTo(0,0);
			document.addEventListener('touchmove', function(e){ e.preventDefault(); return false; }, false);
//			document.onselectstart=new Function ("return false"); // Disables selections
		});
	</script>
</html>