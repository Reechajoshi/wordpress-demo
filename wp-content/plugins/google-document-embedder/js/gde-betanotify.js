jQuery(function ($) {

	/*
	 * jQuery library for GDE (used for beta notification)
	 */
	
	// change plugin row text - they don't make this one easy!
	var newtxt = 'There is a new pre-release version of Google Doc Embedder available.';
	
	var html = $('#google-doc-embedder').closest('tr').next('tr').children('.plugin-update').children('.update-message').html();
	
	var links = gde_extract_urls(html);
	var detailLink = links[0];
	var updateLink = links[1];
	
	newtxt += ' <a class="thickbox" title="Google Doc Embedder" href="' + detailLink + '">';
	newtxt += 'See what\'s new' + '</a>' + ' or ';
	newtxt += '<a href="' + updateLink + '">';
	newtxt += 'update now' + '</a>.';
	
	$('#google-doc-embedder').closest('tr').next('tr').children('.plugin-update').children('.update-message').html(newtxt);
	
	function gde_extract_urls(str) {
		var doc = document.createElement("html");
		doc.innerHTML = str;
		var links = doc.getElementsByTagName("a")
		var urls = [];

		for (var i=0; i<links.length; i++) {
			urls.push(links[i].getAttribute("href"));
		}
		
		return urls;
	}
});