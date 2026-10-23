/**
*
* @package National Flags
* @copyright (c) 2020 RMcGirr83
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

(function($) { // Avoid conflicts with other libraries
	'use strict';

	$("#user_flag").change(function () {
		var flag_id = $(this).val();
		$.ajax({
			url: AJAX_FLAG_INFO.replace(/[^\/]+$/, flag_id),
			dataType: 'text',
			success: function(data){
				var json = $.parseJSON(data);
				if (json.error)
				{
					$('#flag_image').html(''+json.error+'').show();
				}
				else
				{
					$('#flag_image').html('<img class="flag_image" src="'+json.flag_image+'" alt="'+json.flag_name+'" title="'+json.flag_name+'" />').show();
				}
			}
		});
	});
})(jQuery); // Avoid conflicts with other libraries
