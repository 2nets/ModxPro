define('app/community', ['app'], function (App) {
    'use strict';

    App.Community = {
        initialize: function () {
            $(document).on('click', '.item-data .star a', function (e) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parents('.item-data');
                var $star = $parent.find('.star');
                var id = $parent.data('id');
                var type = $parent.data('type');

                $star.toggleClass('active');
                App.Utils.request({action: 'community/star/' + type, id: id}, function (res) {
                    if (res.success) {
                        var stars = res.object['stars'];
                        if (!stars) {
                            stars = '';
                        }
                        $star.find('.placeholder').text(stars);
                    }
                });
            });

            $(document).on('click', '.item-data .rating a.vote:not(".active")', function (e) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parents('.item-data');
                var $rating = $parent.find('.rating');
                var id = $parent.data('id');
                var type = $parent.data('type');
                var vote = $this.data('vote');

                $parent.find('.active').removeClass('active');
                App.Utils.request({action: 'community/vote/' + type, id: id, vote: vote}, function (res) {
                    if (res.success) {
                        $this.addClass('active');
                        var rating = res.object['rating'];
                        var $value = $rating.find('.placeholder');
                        $value.text(rating > 0 ? '+' + rating : rating);
                        if (rating < 0) {
                            $value.removeClass('positive').addClass('negative');
                        } else if (rating > 0) {
                            $value.removeClass('negative').addClass('positive');
                        } else {
                            $value.removeClass('negative positive');
                        }
                    }
                });
            });

            $(document).on('click', '.item-data .rating a.get_votes', function (e) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parents('.item-data');
                var id = $parent.data('id');
                var type = $parent.data('type');

                App.Utils.request({action: 'community/vote/getlist', id: id, type: type}, function (res) {
                    if (res.success) {
                        $this.tooltip({
                            html: true,
                            title: res['results'],
                            trigger: 'manual',
                            placement: 'top',
                            template: '<div class="tooltip rating-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                            // animation: false,
                            // container: $parent.find('.rating')
                        });
                        $this.tooltip('show');
                    }
                });
            });

            $(document).on('change', '#subscription, .subscription', function (e) {
                e.stopPropagation();
                var $this = $(this);
                App.Utils.request({
                    action: 'community/subscription/' + $this.data('type'),
                    id: $this.data('id')
                });
            });

            $(document).on('click', 'a', function() {
                var site = /http(s|):\/\/(.*?\.|)mod(x|host|store)\.pro/;
                var href = $(this).attr('href');
                if (/^(http|https|ftp)/.test(href) && !site.test(href)) {
                    $(this).attr('href', '/outside?url=' + encodeURIComponent(href)).attr('target', '_blank');
                }
            });

            $(document).on('click scroll', function (e) {
                e.stopPropagation();
                $('a.get_votes').tooltip('dispose');
            });

            if ($('#comments').length) {
                $(document).on('click', '.item-data .link a', function (e) {
                    e.preventDefault();
                    var $this = $(this);
                    var $parent = $this.parents('.item-data');
                    var id = $parent.data('id');
                    var $ta = $('<textarea>').append(document.location.href.replace(/#.*/, '') + '#comment-' + id)
                        .css({opacity: 0})
                        .appendTo($this).focus().select();
                    document.execCommand('copy');
                    $ta.blur().remove();
                    App.Message.success(App.Utils.lexicon('comment_link_success'));
                });
            }
        },

        Topics: {
            initialize: function () {
                this.Form = new this.Form();
            },

            Form: App.Form.extend({
                el: '#topic-form',

                initialize: function () {
                    App.Form.prototype.initialize.call(this);

                    var $el = this.$el;
                    $el.find('[name="parent"]').on('change', function (e) {
                        var id = $(this).val();
                        App.Utils.request({action: 'community/topic/getsection', id: id}, function (res) {
                            var $fields = $el.find('.topic-fields');
                            if (res.object['fields'] != '') {
                                $fields.html(res.object['fields']).show();
                            } else {
                                $fields.html('').hide();
                            }
                            $el.find('.topic-parent-desc').html(res.object['description']);
                        });
                    });

                    if ($el.find('[name="id"]').val() == 0) {
                        requirejs(['sisyphus'], function () {
                            $el.sisyphus({
                                excludeFields: $el.find('[name="parent"]'),
                            });
                        });
                    }
                },

                draft: function (e) {
                    var data = Backbone.Syphon.serialize(this);
                    data['published'] = 0;

                    this.submit(e, data);
                },

                publish: function (e) {
                    var data = Backbone.Syphon.serialize(this);
                    data['published'] = 1;

                    this.submit(e, data);
                },

                success: function (res) {
                    if (res.object['redirect']) {
                        this.$el.sisyphus().manuallyReleaseData();
                        window.location = res.object['redirect'];
                    } else {
                        this.data = this.$el.serialize();
                    }
                }
            }),
        },

        Comments: {
            _unseen: [],
            $comments: $('#comments'),
            $load: $('#comments-panel > .reload'),
            $new: $('#comments-panel > .new'),

            initialize: function () {
                this.Form = new this.Form();
                var el = this;

                $(document).on('click touchend', '.item-data .goto span', function () {
                    var $this = $(this);
                    var id = $this.data('id');

                    var $comment = $('#comment-' + id);
                    if ($comment.length) {
                        el.go(id);
                        if ($this.data('dir') == 'up') {
                            $comment.find('.goto [data-dir="down"]')
                                .attr('data-id', $this.parents('.item-data').data('id'))
                                .show();
                        }
                    }
                });

                this.unseen();
                $(document).on('click', '#comments-panel > .new', function(e) {
                    e.preventDefault();
                    el.go(el._unseen.shift());
                    el.$new.text(el._unseen.length);
                    if (!el._unseen.length) {
                        el.$new.hide();
                    }
                });
            },

            Form: App.Form.extend({
                el: '#comment-form',
                $placeholder: $('#comment-form-placeholder'),
                $opener: $('#comment-form-open'),

                initialize: function () {
                    App.Form.prototype.initialize.call(this);

                    var $el = this.$el;
                    requirejs(['sisyphus'], function () {
                        $el.sisyphus({
                            excludeFields: $el.find('[name="parent"]'),
                        });
                    });
                },

                submit: function (e, data) {
                    if (data === undefined) {
                        data = Backbone.Syphon.serialize(this);
                    }
                    data.topic = this.$el.data('topic');
                    App.Form.prototype.submit.call(this, e, data);
                },

                show: function (data) {
                    if (data !== undefined) {
                        Backbone.Syphon.deserialize(this, data);
                    }
                    this.$preview_close.hide();
                    this.$el.show().find('textarea').focus();
                },

                hide: function () {
                    this.$el.appendTo(this.$placeholder);
                    Backbone.Syphon.deserialize(this, {
                        id: 0,
                        parent: 0,
                        action: 'community/comment/create',
                        content: '',
                    });
                    this.$opener.show();
                    this.$preview_elem.hide().html('');
                    this.$preview_close.hide();
                    this.$el.hide();
                },

                open: function () {
                    $('.comment-footer').show();
                    this.$el.appendTo(this.$placeholder);
                    this.$opener.hide();
                    this.show();
                },

                move: function (id, data) {
                    var $comment = $('#comment-' + id);
                    $comment.removeClass('unseen');
                    var $links = $comment.find('.comment-footer');
                    this.$el.insertAfter($links);
                    this.$opener.show();
                    $links.hide();
                    this.show(data);
                },

                success: function (res) {
                    this.$el.sisyphus().manuallyReleaseData();
                    this.hide();
                    App.Community.Comments.insert(res.object);
                    App.Community.Comments.go(res.object.id);
                    App.Community.Comments.load();
                }
            }),

            insert: function (obj) {
                // noinspection JSJQueryEfficiency
                var update = document.getElementById('comment-' + obj.id) !== null;
                if (update) {
                    $('#comment-' + obj.id).replaceWith(obj.html);
                } else if (obj.parent != 0) {
                    $('#comment-' + obj.parent).next('.comments-list').append(obj.html);
                } else {
                    $('#comments').append(obj.html);
                }
                // noinspection JSJQueryEfficiency
                App.Utils.highlight($('#comment-' + obj.id));
                App.Community.Comments.count(obj.count);
            },

            edit: function (id) {
                var $comment = $('#comment-' + id);
                if ($comment.length) {
                    var form = this.Form;
                    App.Utils.request({action: 'community/comment/get', id: id}, function (res) {
                        res.object.action = 'community/comment/update';
                        form.move(id, res.object);
                    });
                }
            },

            reply: function (id) {
                var $comment = $('#comment-' + id);
                if ($comment.length) {
                    var data = {
                        id: 0,
                        parent: id,
                        action: 'community/comment/create',
                        content: this.Form.$el.find('textarea').val(),
                    };
                    this.Form.move(id, data);
                }
            },

            remove: function (id) {
                App.Message.confirm(App.Utils.lexicon('comment_remove_confirm'), function () {
                    App.Utils.request({action: 'community/comment/remove', id: id}, function (res) {
                        var $comment = $('#comment-' + id);
                        $comment.next('.comments-list').remove();
                        $comment.remove();
                        App.Community.Comments.count(res.object.count);
                        // App.Community.Comments.load();
                    })
                });
            },

            'delete': function (id) {
                App.Utils.request({action: 'community/comment/delete', id: id}, function (res) {
                    App.Community.Comments.insert(res.object);
                    // App.Community.Comments.load();
                })
            },

            restore: function (id) {
                App.Utils.request({action: 'community/comment/restore', id: id}, function (res) {
                    App.Community.Comments.insert(res.object);
                    // App.Community.Comments.load();
                })
            },

            go: function (id) {
                var $comment = $('#comment-' + id);
                if ($comment.length) {
                    $('html, body').animate({
                        scrollTop: $comment.offset().top || 0
                    }, 200);
                    window.setTimeout(function() {
                        $comment.removeClass('unseen');
                    }, 500);
                }
            },

            load: function (clear) {
                if (clear === true) {
                    $('.comment-row.unseen').removeClass('unseen');
                }
                var topic = this.Form.$el.data('topic');
                var el = this;

                el.$load.find('.far').addClass('fa-spin');
                App.Utils.request({action: 'community/comment/getnewcomments', topic: topic}, function (res) {
                    App.Community.Comments.count(res.topic.comments);
                    el.$comments.html(res.html);
                    el.$load.find('.far').removeClass('fa-spin');
                    el.unseen();
                })
            },

            count: function (count) {
                $('#comments-count').text(count);
            },

            unseen: function() {
                var el = this;
                this._unseen = [];
                this.$comments.find('.unseen').each(function() {
                    el._unseen.push($(this).find('.item-data').data('id'));
                });
                if (this._unseen.length) {
                    this._unseen.sort();
                    this.$new.text(this._unseen.length).show()
                } else {
                    this.$new.text('0').hide()
                }
            }
        }
    };
    App.Community.initialize();

    App.Router.route('draft/:id', 'topic-draft', function (id) {
        App.Message.confirm(App.Utils.lexicon('topic_draft_confirm'), function () {
            App.Utils.request({action: 'community/topic/draft', id: id}, function (res) {
                App.Router.clear();
                window.location.reload();
            })
        });
    });

    App.Router.route('publish/:id', 'topic-publish', function (id) {
        App.Message.confirm(App.Utils.lexicon('topic_publish_confirm'), function () {
            App.Utils.request({action: 'community/topic/publish', id: id}, function (res) {
                App.Router.clear();
                window.location.reload();
            })
        });
    });

    if ($('#topic-form').length) {
        App.Community.Topics.initialize();
    }

    if ($('#comments').length) {
        App.Router.route('create', 'comment-create', function () {
            App.Router.clear();
            App.Community.Comments.Form.open();
        });

        App.Router.route('load', 'comment-load', function () {
            App.Router.clear();
            App.Community.Comments.load(true);
        });

        App.Router.route('reply/:id', 'comment-reply', function (id) {
            App.Router.clear();
            App.Community.Comments.reply(id);
        });

        App.Router.route('edit/:id', 'comment-edit', function (id) {
            App.Router.clear();
            App.Community.Comments.edit(id);
        });

        App.Router.route('delete/:id', 'comment-delete', function (id) {
            App.Router.clear();
            App.Community.Comments.delete(id);
        });

        App.Router.route('restore/:id', 'comment-restore', function (id) {
            App.Router.clear();
            App.Community.Comments.restore(id);
        });

        App.Router.route('remove/:id', 'comment-remove', function (id) {
            App.Router.clear();
            App.Community.Comments.remove(id);
        });

        App.Community.Comments.initialize();
    }

    return App;
});