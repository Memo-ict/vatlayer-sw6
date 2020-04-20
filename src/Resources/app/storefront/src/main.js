import HttpClient from 'src/service/http-client.service';

(function($, window) {
    'use strict';

    $.fn.Vatlayer = function() {
        return this.each(function() {
            var self = $(this);
            var baseUrl = self.data('base-url');

            self.client = new HttpClient(window.accessKey, window.contextToken);

            self.find('input[name*="vatId"]').on('blur', function(e) {
                var me = $(e.target);
                if(me.val().length > 0) {
                    self.client.get(baseUrl + '/api/v1/memo/vatlayer/check-id/' + me.val(), function(json) {
                        self.siblings('.alert')
                            .css('display', 'none')
                            .filter('.alert-' + json.message.type)
                            .css('display', 'flex')
                            .find('.alert-content')
                            .text(json.message.message);

                        $('button:submit').attr('disabled', (json.message.type === 'danger' && me.is(':required')));
                    });
                }
            });
        });
    };

    $(document).ready(function () {
        $('.vatlayer').Vatlayer();
    });
})(jQuery, window);
