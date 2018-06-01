define('app/community', ['app'], function (App) {
    'use strict';

    App.Community = {
        initialize: function() {
            $(document).on('click', '.item-data .star a', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parents('.item-data');
                var $star = $parent.find('.star');
                var id = $parent.data('id');
                var type = $parent.data('type');

                $star.toggleClass('active');
                App.Utils.request({action: 'community/star/' + type, id: id}, function(res) {
                    if (res.success) {
                        var stars = res.object['stars'];
                        if (!stars) {
                            stars = '';
                        }
                        $star.find('.placeholder').text(stars);
                    }
                });
            });

            $(document).on('click', '.item-data .rating a.vote:not(".active")', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parents('.item-data');
                var $rating = $parent.find('.rating');
                var id = $parent.data('id');
                var type = $parent.data('type');
                var vote = $this.data('vote');

                $parent.find('.active').removeClass('active');
                App.Utils.request({action: 'community/vote/' + type, id: id, vote: vote}, function(res) {
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

            $(document).on('click', '.item-data .rating a.get_votes', function(e) {
                e.preventDefault();
                var $this = $(this);
                var $parent = $this.parents('.item-data');
                var $rating = $parent.find('.rating');
                var id = $parent.data('id');
                var type = $parent.data('type');

                App.Utils.request({action: 'community/vote/getlist', id: id, type: type}, function(res) {
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

            $(window).on('click scroll', function(e) {
                e.stopPropagation();
                $('a.get_votes').tooltip('dispose');
            });
        },
    };
    App.Community.initialize();

    return App;
});