/**
 * Author: Denisov Denis
 * Email: denisovdenis@me.com
 * Date: 12.08.13
 * Time: 13:25
 */

(function ($, mw) {

    var Likes = {
        load: function () {
            var actionURL = wgServer + wgScriptPath + '/api.php?action=likes&format=json';
            var dataString = 'pageId=' + $('#ext-Likes-pageId').val() + '&userId=' + $('#ext-Likes-userId').val();

            $('body').css('cursor', 'busy');

            $.ajax({
                type: 'POST',
                url: actionURL,
                dataType: 'json',
                data: dataString,
                success: function (data) {
                    var isLiked = parseInt($('#ext-Likes-isLiked').val()),
                        count = parseInt($('.likes span').text());

                    if (isLiked) {
                        $('#ext-Likes-isLiked').val(0);
                        count--;
                        $('.likes img').removeClass('reflection');
                    } else {
                        $('#ext-Likes-isLiked').val(1)
                        count++;
                        $('.likes img').addClass('reflection');
                    }

                    $('.likes span').text(count);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    if (typeof console != 'undefined') {
                    }
                },
                complete: function(){
                    $('body').css('cursor', 'default');
                }
            });
        }
    };

    $(function ($) {
        $('.likes a').click(function () {
            Likes.load();
            return false;
        });

        $('.likes a').hover(function() {
            $(this).find('img').fadeTo(200, 0.75);
        }, function() {
            $(this).find('img').fadeTo(200, 1);
        });

    });

})(jQuery, mediaWiki);