import HttpClient from 'src/service/http-client.service';

(function($, window) {
    'use strict';

    $.fn.Vatlayer = function() {
        return this.each(function() {
            var self = $(this);

            self.client = new HttpClient(window.accessKey, window.contextToken);

            self.find('input[name*="vatId"]').on('blur', function(e) {
                var me = $(e.target);
                if(me.val().length > 0) {
                    self.client.get('/api/v1/memo/vatlayer/' + me.val(), function(data) {
                        const json = JSON.parse(data);

                        console.log(self.siblings('.alert').filter('.alert-' + json.message.type));
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
