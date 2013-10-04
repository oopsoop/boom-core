/**
Create a tree widget for selecting pages.
*/
$.widget('boom.pageTree', {
	options : {
		onPageSelect : function() {}
	},

	_create : function() {
		var self = this;

		var treeConfig = $.extend({}, $.boom.config.tree, {
			toggleSelected: false,
			onClick: function(event) {
				event.preventDefault();

				self.itemClick($(this));
			},
			onToggle: function(page_id) {
				return self.getChildren(page_id);
			}
		});

		this.element.tree('destroy').tree(treeConfig);
	},

	itemClick : function($node) {
		var link = {
			url : $node.attr('href'),
			page_rid : $node.attr('rel'),
			title : $node.text()
		};

		this.highlightItem($node);
		this.options.onPageSelect(link);
	},

	getChildren : function(page_id) {
		$.boom.loader.show();

		var list_ready = $.Deferred();
		$.ajax({
			type: 'POST',
			url: '/page/children.json',
			data: {parent : page_id},
			dataType: 'json'
		})
		.done(function(data) {

			var children = $('<ul></ul>');

			$( data ).each( function( i, item ){
				var li = $('<li></li>')
					.data( 'children', parseInt(item.has_children, 10) )
					.appendTo( children );
				$('<a></a>')
					.attr( 'id', 'page_' + item.id )
					.attr( 'href', item.url )
					.attr( 'rel', item.id )
					.text( item.title )
					.appendTo( li );
			});

			var parent_id = $( 'input[name=parent_id]' ).val();
			children.find( '#page_' + parent_id ).addClass( 'ui-state-active' );

			list_ready.resolve( { childList: children } );

			$.boom.loader.hide();
		});

		return list_ready;
	},

	highlightItem : function($item) {
		$item
			.addClass('ui-state-active')
			.parents('.boom-tree')
			.find('a.ui-state-active')
			.not($item)
			.removeClass('ui-state-active');
	}
});