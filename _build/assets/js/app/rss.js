define(['app'], function (App) {
    'use strict';

    App.Rss = Backbone.View.extend({
        el: '#rss-config',
        $input: $('#rss-input'),
        events: {
            submit: 'submit',
            'click [name="copy"]': 'copy',
            'click [name="link"]': 'link',
            'change form': 'change',
        },

        change: function(e) {
            e.preventDefault();

            var sections = [];
            this.$el.find('input:checked').each(function () {
                sections.push($(this).attr('name'));
            });

            var val = this.$input.val().replace(/\?.*/, '');
            if (sections.length) {
                this.$input.val(val + '?blogs=' + sections.join(','))
            } else {
                this.$input.val(val);
            }
            // this.copy(e);
        },

        copy: function(e) {
            e.preventDefault();

            var $ta = $('<textarea>').append(this.$input.val())
                .css({opacity: 0})
                .appendTo('body').select();
            document.execCommand('copy');
            $ta.blur().remove();

            App.Message.success(App.Utils.lexicon('rss_link_success'));
        },

        link: function(e) {
            e.preventDefault();
            window.open(this.$input.val());
        }
    });

    new App.Rss();
});