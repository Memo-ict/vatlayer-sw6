import {Component} from 'src/core/shopware';
import template from './sw-plugin-config.html.twig';

Component.override('sw-plugin-config', {
    template,

    inject: ['VatlayerApiService'],

    data() {
        const domain = `${this.$route.params.namespace}.config`;
        return {
            isLoading: false,
            namespace: this.$route.params.namespace,
            domain: domain,
            salesChannelId: null,
            config: {},
            actualConfigData: {}
        };
    },

    computed: {
        isVatlayerPlugin()
        {
            return 'memoVatlayerPlugin' === this.$route.params.namespace;
        }
    },

    methods: {
        checkVatlayerApiCredentials() {
            this.isLoading = true;
            this.saveConfiguration(() => {
                this.VatlayerApiService.checkCredentials()
                    .then((response) => {
                        this.createNotificationSuccess({
                            title: this.$tc('sw-plugin-config.titleSaveSuccess'),
                            message: response.message,
                        });
                    })
                    .catch((errorResponse) => {
                        // This is an AxiosError, see file for details
                        // platform/src/Administration/Resources/app/administration/node_modules/axios/index.d.ts:79
                        this.createNotificationError({
                            title: this.$tc('sw-plugin-config.titleSaveError'),
                            message: errorResponse.response.data.message,
                        });
                    });
            });
        },

        saveConfiguration(callback = null) {
            this.$refs.systemConfig.saveAll().then(() => {
                this.createNotificationSuccess({
                    title: this.$tc('sw-plugin-config.titleSaveSuccess'),
                    message: this.$tc('sw-plugin-config.messageSaveSuccess')
                });
                if (callback !== null) {
                    callback();
                }
            }).catch((error) => {
                this.createNotificationError({
                    title: this.$tc('sw-plugin-config.titleSaveError'),
                    message: error
                });
            });
        }
    }
});
