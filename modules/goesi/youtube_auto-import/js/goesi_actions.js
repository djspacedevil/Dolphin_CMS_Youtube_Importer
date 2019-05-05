$(document).ready(function(){

$(".Adden").live('click', function() {
//Neuen Eintrag erstellen
		$("#loading").html('<img src="'+Bilderpfad+'8-1.gif">').hide().fadeIn(1000);
		Channel = $(this).parent().parent().parent().find(".ChannelURL").val();
		nAlbum = $(this).parent().parent().parent().find(".Album").val();
		Typ = $(this).parent().parent().parent().find(".Typ").val();

		if (Channel != "" && nAlbum != "") { 
		$.post(PostDir,{ NewChannel: Channel, NewAlbum: nAlbum, dbTyp:Typ},
			function(data){ 
		if (data != "Error") {
				$("#loading").fadeOut("600"); 
				$("#ok").html('<img src="'+Bilderpfad+'yes.png">').css("display", "inline").show("slow").fadeOut(3000); 
				$('.YoutubeListe tr:last').after(data); 
			} else {
				$("#loading").fadeOut("600"); 
				$("#ok").html('<img src="'+Bilderpfad+'bullet_red_order.png"><br>No Album set').css("display", "inline").show("slow").fadeOut(3000);
				} });
		} else {
		$("#loading").fadeOut("600");}			
	});
//

$(".aktiv").live('click', function() {
//Eintrag deaktivieren
		$("#loading").html('<img src="'+Bilderpfad+'8-1.gif">').hide().fadeIn(1000);
		DBid = $(this).parent().find(".DBid").val();
		this.src = this.src.replace("bullet_green.png","bullet_red.png");
		$(this).attr("class", "deaktiv");
		$.post(PostDir,{ AKTIV: 'no', db_id: DBid},
			function(data){ $("#loading").fadeOut("600");});
			});
//

$(".deaktiv").live('click', function() {
//Eintrag aktivieren
		$("#loading").html('<img src="'+Bilderpfad+'8-1.gif">').hide().fadeIn(1000);
		DBid = $(this).parent().find(".DBid").val();
		this.src = this.src.replace("bullet_red.png","bullet_green.png");
		$(this).attr("class", "aktiv");
		$.post(PostDir,{ AKTIV: 'yes', db_id: DBid},
			function(data){ $("#loading").fadeOut("600"); });
			});
//


$(".delete").live('click', function() {
//Eintrag löschen
		$("#loading").html('<img src="'+Bilderpfad+'8-1.gif">').hide().fadeIn(1000);
		DBid = $(this).parent().find(".DBid").val();
		zeile = 'zeile_'+DBid;
		$.post(PostDir,{ DELETE: 'yes', db_id: DBid},
			function(data){ $("#loading").fadeOut("600") + $("tr."+zeile+" td").fadeOut("slow") ; });
			});
//

$(".sync").live('click', function() {
//Eintrag sync
		$("#loading").html('<img src="'+Bilderpfad+'8-1.gif">').hide().fadeIn(1000);
		DBid = $(this).parent().find(".DBid").val();
		$.post(PostDir,{ SYNC: 'yes', db_id: DBid},
			function(data){ 
				if (data == "New" ) {

		$("#loading").html('<img src="'+Bilderpfad+'green.gif"><br><b>Updated</b>').fadeOut(3000);
		
		//Datum und Links neuladen
		$.post(PostDir,{ TIME: 'yes', db_id: DBid},
			function(data){ $("#Time_"+DBid).html(data); });
			
		$.post(PostDir,{ LINKS: 'yes', db_id: DBid},
			function(data){ $("#Links_"+DBid).html(data); });
		//
			 }
			else if (data == "NoNew" ){$("#loading").html('<img src="'+Bilderpfad+'yellow.gif"><br><b>No updates found</b>').fadeOut(3000);}
			else ( $("#loading").html('<img src="'+Bilderpfad+'red.gif"><br><b>ERROR FOUND - IMPORT FILE IS BROKEN</b><br>Please check your inputs.<br>'+data).fadeOut(20500) ); });
	
		});
//



});