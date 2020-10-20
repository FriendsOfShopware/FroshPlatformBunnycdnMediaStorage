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

            if (this.$parent.$parent.$parent.currentSalesChannelId) {
                fields.forEach(el => {
                    el.setAttribute('disabled', 'disabled');
                });
            } else {
                fields.forEach(el => {
                    el.removeAttribute('disabled');
                });
            }
        },
    }
})
