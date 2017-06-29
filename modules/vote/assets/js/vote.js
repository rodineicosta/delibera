function delibera_update_vote_events()
{
	jQuery(".delibera_pairwise_voto label.label-voto").click(function(){
		var data = {
            action : "delibera_vote_callback",
            nonce: jQuery('#_wpnonce_delibera_vote_callback').val(),
            delibera_voto: jQuery(this).find('input[name=delibera_voto]').val(),
            post_id: delibera.post_id,
            pair: jQuery('#delibera-votes-pair').val()
        };
		jQuery.post(
			delibera.ajax_url, 
            data,
            function(response) {
            	jQuery("#delibera-pairwise-entry").replaceWith(response);
            	delibera_update_pair_events();
            }
		);
	});
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
}

jQuery(document).ready(function() {
	delibera_update_vote_events();
});