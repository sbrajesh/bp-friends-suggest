jQuery( document ).ready( function () {
	
	var jq = jQuery;
	jq( document ).on( 'click', '.suggested-friend-item-list span.remove-friend-suggestion a', function () {
		
		//hide the suggestion
		var li = jq( this ).parent().parent().parent();
		
		jq( li ).remove();
		var url = jq( this ).attr( 'href' );
		var nonce = get_var_in_url( url, '_wpnonce' );
		
		var suggested_user_id = get_var_in_url( url, 'suggest_id' );
		
		jq.post( ajaxurl, {
			action: "friend_suggest_remove_suggestion",
			cookie: encodeURIComponent( document.cookie ),
			suggestion_id: suggested_user_id,
			_wpnonce: nonce
		},
		function () {
			//nothing here

		} );

		return false;

	} );
	//let us copy paste the code to allow ajax request sending
	//this code is from bp-template
	jq( '#members-suggestion-list' ).on('click', '.friendship-button a', function() {
		jq(this).parent().addClass('loading');
		var fid   = jq(this).attr('id'),
			nonce   = jq(this).attr('href'),
			thelink = jq(this);

		fid = fid.split('-');
		fid = fid[1];

		nonce = nonce.split('?_wpnonce=');
		nonce = nonce[1].split('&');
		nonce = nonce[0];

		jq.post( ajaxurl, {
			action: 'addremove_friend',
			'cookie': bp_get_cookies(),
			'fid': fid,
			'_wpnonce': nonce
		},
		function(response)
		{
			var action  = thelink.attr('rel');
				parentdiv = thelink.parent();

			if ( action === 'add' ) {
				jq(parentdiv).fadeOut(200,
					function() {
						parentdiv.removeClass('add_friend');
						parentdiv.removeClass('loading');
						parentdiv.addClass('pending_friend');
						parentdiv.fadeIn(200).html(response);
					}
					);

			} else if ( action === 'remove' ) {
				jq(parentdiv).fadeOut(200,
					function() {
						parentdiv.removeClass('remove_friend');
						parentdiv.removeClass('loading');
						parentdiv.addClass('add');
						parentdiv.fadeIn(200).html(response);
					}
					);
			}
		});
		return false;
	} );
//helper
//get a variable from url
	function get_var_in_url( url, name ) {
		var urla = url.split( "?" );
		var qvars = urla[1].split( "&" );//so we hav an arry of name=val,name=val
		for ( var i = 0; i < qvars.length; i++ ) {
			var qv = qvars[i].split( "=" );
			if ( qv[0] == name )
				return qv[1];
		}
		return '';
	}
} );
