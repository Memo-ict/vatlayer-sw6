import template from "./memo-vatlayer-api-credentials.twig";

const {Component, Mixin} = Shopware;

Component.register('memo-vatlayer-api-credentials', {
    template,

    inject: [
        'VatlayerApiService',
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
        }
    },

    methods: {
        onTestButtonClicked() {
            this.isLoading = true;

            const apiKeyInput = document.querySelector('input[name="MemoVatlayer6.config.apiKey"]');

            const apiKey = !!apiKeyInput ? apiKeyInput.value : null;

            this.VatlayerApiService.checkCredentials({apiKey})
                .then((response) => {
                    if (response.valid === true) {
                        this.createNotificationSuccess({
                            title: this.$tc('global.default.success'),
                            message: this.$tc(response.message),
                        });
                    } else {
                        this.createNotificationError({
                            title: this.$tc('global.default.error'),
                            message: this.$tc(response.message),
                        });
                    }
                })
                .finally(() => this.isLoading = false);
        }
    }
});
