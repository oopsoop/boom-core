$.widget('ui.chunkHtml', $.ui.chunk, {
    edit: function() {
        var self = this,
            dialog;

        dialog = new BoomCMS.Dialog({
            url: this.options.currentPage.baseUrl + 'chunk/edit?type=html&slotname=' + this.options.name,
            width: 600,
            title: 'Edit HTML'
        }).done(function() {
            var html = dialog.contents.find('textarea').val();

            if (html.trim() !== '') {
                self.insert(html);
            } else {
                self.remove();
            }
        })
        .always(function() {
            self.bind();
        });
    },

    getData: function() {
        return {html : this.html};
    },

    /**
    @param {Int} id Tag ID
    */
    insert: function(html) {
        this.html = html;

        return this._save();
    }
});