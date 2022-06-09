const {Component, Mixin} = Shopware;

Component.register('bunnycdn-config-restriction', {
    template: ' ', // we need content to be created

    created() {
        this.checkAndHideSetting();
    },

    updated() {
        this.checkAndHideSetting();
    },

    methods: {
        checkAndHideSetting() {
            const fields = document.querySelectorAll('input[name^="FroshPlatformBunnycdnMediaStorage.config"],.sw-plugin-config__save-action');

            if (this.pluginConfigData().currentSalesChannelId) {
                fields.forEach(el => {
                    el.setAttribute('disabled', 'disabled');
                });
            } else {
                fields.forEach(el => {
                    el.removeAttribute('disabled');
                });
            }
        },

        pluginConfigData() {
            let configData = this.$parent;
            for (let i = 0; i < 20; i++) {
                if (configData.actualConfigData) {
                    return configData;
                }

                configData = configData.$parent;
            }

            throw "Can not get pluginConfigData";
        }
    },

})
