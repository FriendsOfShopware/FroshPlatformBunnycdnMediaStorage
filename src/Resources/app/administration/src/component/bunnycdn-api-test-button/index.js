const {Component, Mixin} = Shopware;
import template from './bunnycdn-api-test-button.html.twig';

Component.register('bunnycdn-api-test-button', {
    template,

    props: ['btnLabel'],
    inject: ['bunnycdnApiTest'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    computed: {
        pluginConfigData() {
            let configData = this.$parent;
            for (let i = 0; i < 20; i++) {
                if (configData.actualConfigData) {
                    return configData.actualConfigData.null;
                }

                configData = configData.$parent;
            }

            throw "Can not get pluginConfigData";
        }
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            this.bunnycdnApiTest.check(this.pluginConfigData).then((res) => {
                if (res.success) {
                    this.isSaveSuccessful = true;
                    this.createNotificationSuccess({
                        title: this.$tc('bunnycdn-api-test-button.title'),
                        message: this.$tc('bunnycdn-api-test-button.success')
                    });
                } else {
                    this.createNotificationError({
                        title: this.$tc('bunnycdn-api-test-button.title'),
                        message: this.$tc('bunnycdn-api-test-button.error')
                    });
                }

                setTimeout(() => {
                    this.isLoading = false;
                }, 2500);
            });
        }
    }
})
