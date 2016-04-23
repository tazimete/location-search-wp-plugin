

jQuery( document ).ready(function($) {
	
	var MIN_LENGTH = 3;
	var slug = [];
	
	$("#search-input-text").keyup(function() {
		var keyword = $("#search-input-text").val().trim();
		if (keyword.length >= MIN_LENGTH) {	
			
			var data = {
				action: 'ls_response',
				post_var: keyword
			};
			
			$.ajax({
				url: the_ajax_script.ajaxurl,
				type: 'GET',
				data: data,
				beforeSend: function(){				
					$(".auto-loader").fadeIn('200');
				},
				success:function(result){
					
					//console.log(result);
					//var result;
					try{
						var result= JSON.parse(result);
					}catch(e){
						//console.log(e); //error in the above string(in this case,yes)!
					}
					
					//var result = $.parseJSON(result);
					//$('#auto-complete-ul').append(result);
					$('#auto-complete-ul').empty();
					$.each(result, function(key, value){							
							$('#auto-complete-ul').append('<li class="location-list">'+value.location+'</li>');
							slug.push(value.slug);
					});
					
				if(!result){
					$('.location-list').html('').hide();
				}},
				error: function(err){				
					//alert("Error !!! Request Failed");
				},
				complete: function(){
					$(".auto-loader").fadeOut('200');
				}
		}); //End of ajax request
				
		} else {
			$('.location-list').html('').hide();
		}
	}); //End of keyup event
		
		
		$("#search-input-text").blur(function(){
			$("#auto-complete-ul li.location-list").click(function() {
				var indexOfLi = $("li.location-list").index(this);
			$("#auto-complete-ul").show();
			var text = $(this).text();
			$("#search-input-text").val(text);
			var slugOfLi = slug[indexOfLi];
			
			$("#search-input-text").keydown(function(event){
				var lengthOfLocation = $("#search-input-text").val();
				var keycode = event.charCode || event.keyCode || event.which ;
				if(keycode == 13 && lengthOfLocation.length >= 3){
					window.location = slugOfLi;
				}
			});
		});
			$("#auto-complete-ul").fadeOut(200);
		})
		.focus(function() {		
    	    $("#auto-complete-ul").show();
    	});

}); //End of document load
	