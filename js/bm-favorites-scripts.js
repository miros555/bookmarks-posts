jQuery(document).ready(function($) {
	
/**********************Add and Delete Posts in Front**************************/	

  $('.bm-favorites').click(function(e){
		e.preventDefault();
		var action = $(this).data('action'), //
		    post = $(this).data('post'),
			li = $(this).closest('li');
	   $.ajax({ 
			type: 'POST',
			url: bmFavorites.url, 
			data: {
				security: bmFavorites.nonce,
				  action: 'bm',       
				     arg: action,
				  postId: post
			},
			beforeSend: function(){
				$('.bm-link.'+post+' a').fadeOut(300, function(){
				$('.bm-hidden.'+post).fadeIn();
				});
			},
			success: function(res){
				    $('.bm-hidden.'+post).fadeOut(300, function(){
				    $('.bm-link2.'+post).fadeIn();
				});
					li.html(res);
			},
			error: function(){
				console.log('Error!');
			}
		});	
    });
	

 
});


if(performance.navigation.type == 2){
	location.reload(true); 
 }

var url=document.location.href;
if(performance.navigation.type !== 1&&document.referrer !== url&&url.indexOf('login')<0){
   //location.reload(true);
}

