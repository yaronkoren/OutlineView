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
	var pageURL = mw.config.get('wgServer') + mw.config.get('wgScript') + '?title=' + pageName + '&action=render';
	$.get( pageURL, function(data) {
		var realPageURL = mw.config.get('wgServer') + mw.config.get('wgScript') + '?title=' + pageName;
		var pageLink = '<a href="' + realPageURL + '" target="_blank">' + pageName + '</a>';
		data = '<h1>' + pageLink + '</h1>' + "\n" + data;
		$('#displayPane').html(data);
	} );
}
