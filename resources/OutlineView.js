var $tree = $('#OutlineView');
var treeData = JSON.parse( $tree.attr( 'data-tree-layout' ) );
$tree.jstree({
        "core" : {
                "animation" : 0,
                "check_callback" : true,
                "dblclick_toggle" : false,
                'force_text' : true,
                "themes" : { "stripes" : true },
                "strings": {
                        'New node' : 'New entry'
                },
                'data' : treeData
        },
        "plugins" : [ "contextmenu", "dnd", "types", "wholerow" ]
}).bind("loaded.jstree", function (event, data) {
        $tree.jstree("open_all");
	var mainPage = treeData[0]['text'];
	outlineViewDisplayPage( mainPage );
	$('a.jstree-anchor').click( function() {
		var pageName = $(this).text();
		outlineViewDisplayPage( pageName );
	} );
});

function outlineViewDisplayPage( pageName ) {
	var basePageURL = mw.config.get('wgServer') + mw.config.get('wgScript') + '?title=' + pageName;
	var renderURL = basePageURL + '&action=render';
	$.get( renderURL, function(data) {
		var viewLink = '<a href="' + basePageURL + '" target="_blank">' + mw.msg('outlineview-viewpage') + '</a>';
		var editURL = basePageURL + "&action=edit";
		var editLink = '<a href="' + editURL + '" target="_blank">' + mw.msg('edit') + '</a>';
		var formEditURL = basePageURL + "&action=formedit";
		var formEditLink = '<a href="' + formEditURL + '" target="_blank">' + mw.msg('formedit') + '</a>';
		data = '<div class="pageLinks">' + viewLink + ' &middot; ' + editLink + ' &middot; ' + formEditLink + "</div>\n" +
			'<h1>' + pageName + '</h1>' + "\n" + data;
		$('#displayPane').html(data);
	} )
	.fail( function() {
		var viewLink = '<a href="' + basePageURL + '" class="new" target="_blank">' + pageName + '</a>';
		$('#displayPane').html('<em>' + mw.msg('outlineview-pagenotfound', viewLink) + '</em>');
	});
}
