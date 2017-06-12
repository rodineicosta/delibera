jQuery(document).ready(function() {
	jQuery('.delibera-voto-modal-close').click(function (){
		jQuery(this).parent().parent().hide();
	});
	jQuery('.delibera-voto-bt-read').click(function () {
		var id = jQuery(this).attr('id').replace('delibera-voto-bt-read-', '');
		jQuery('#delibera-voto-modal-' + id).show();
	});
	jQuery('.delibera-voto-modal .delibera-voto-content').click(function(event){
		jQuery(this).find('.delibera-voto-text').toggle();
		jQuery('.delibera-voto-modal-window').animate({
	        scrollTop: jQuery('.delibera-voto-title').offset().top
	    }, 800);
	});
});