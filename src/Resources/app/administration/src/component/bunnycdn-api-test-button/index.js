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
        pluginConfig() {
            let config = this.$parent.$parent.$parent.actualConfigData;
            if (config) {
                return config.null;
            }

            // in SW6.3.4 it's one step above
            return this.$parent.$parent.$parent.$parent.actualConfigData.null;
        }
    },

    methods: {
        saveFinish() {
            this.isSaveSuccessful = false;
        },

        check() {
            this.isLoading = true;
            this.bunnycdnApiTest.check(this.pluginConfig).then((res) => {
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
