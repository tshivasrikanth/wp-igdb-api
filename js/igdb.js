$(function(){
	
});

jQuery(document).ready(function($) 
{
	/*$(".processSearchValue").on('click',function(){
	});*/

    $(".processSearchValue").on('click',function(){

		var that = this;
		var str = $(this).attr('id');
		var id = str.replace("search_key_bt_", "");

        var data = {
            action: 'process_search_key',
			nonce: myAjax.nonce,
			id: id
        };

        $.post( myAjax.url, data, function( response ) 
        {
            if( response.data == "Success" ){
				$(that).parent().html('<input type="button" value="Processed" class="button button-primary button-large button-disabled">');
			}else{
				alert("Something went wrong try again!");
			}
        });
    });
});