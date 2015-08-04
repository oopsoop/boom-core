$.widget('boom.pageStatus', {

	menu : $('#b-page-publish-menu'),

	_buildMenu : function(status) {
		var self = this, options, moreOptions;

		options = {"Preview": function() {
			$.boom.editor.state('preview');
		}};

		if (status !== 'published') {
			moreOptions = this.options.publishable? this._get_publish_menu(status) : this._get_approvals_menu(status);

			options = $.extend(options, moreOptions, {
				"Revert to published version" : function() {
					// The call to setTimout fixes a bug in IE9 where the toolbar call is minimised (because the splitbutton menu has close) after the dialog is opened.
					// Therefore preventing the dialog from being seen.
					setTimeout(function() {
						self.options.page.revertToPublished()
							.done(function() {
								top.location.reload();
							});
						}, 0);
				}
			});
		}
	},

	_create : function() {
		this.set(this.element.text().trim());
	},

	_get_abbreviated_status : function(status) {
		switch(status) {
			case 'published':
				return "pub'd";
			case 'draft':
				return 'draft';
			case 'embargoed':
				return "emb'd";
			case 'pending approval':
				return "pen'd";
		}
	},

	_get_approvals_menu : function(status) {
		var self = this, options = {};

		(status == 'draft') && (options = {
			"Request approval" : function(){
				self.options.page.requestApproval()
					.done(function(response) {
						new boomNotification('This version of the page is awaiting approval.');
						self.set(response);
					});
			}
		});

		return options;
	},

	_get_publish_menu : function(status) {
		var self = this, options;

		options = {
			"Publish now" : function(){
				self.options.page.publish()
					.done(function(response) {
						new boomNotification('This version of the page is now published.');
						self.set(response);
					});
			}
		};

		if (status == 'embargoed') {
			options = $.extend(options, {
				'View or edit embargo time' : function() {
					self.options.page.embargo()
						.done(function(response) {
							self.set(response);
						});
				}
			});
		} else {
			options = $.extend(options, {
				'Publish later' : function() {
					self.options.page.embargo()
						.done(function(response) {
							self.set(response);
						});
				}
			});
		}

		return options;
	},

	set : function(status) {

		this._buildMenu(status);

		this.element
			.text(this._get_abbreviated_status(status))
			.attr('data-status', status)
			.attr('title', status.ucfirst());
	}
});