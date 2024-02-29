(()=>{var a=Shopware.Classes.ApiService,{Application:o}=Shopware,n=class extends a{constructor(t,s,i="bunnycdn-api-test"){super(t,s,i)}check(t){let s=this.getBasicHeaders({});return this.httpClient.post(`_action/${this.getApiBasePath()}/check`,t,{headers:s}).then(i=>a.handleResponse(i))}};o.addServiceProvider("bunnycdnApiTest",e=>{let t=o.getContainer("init");return new n(t.httpClient,e.loginService)});var c=`<div>
    <sw-button-process
        :isLoading="isLoading"
        :processSuccess="isSaveSuccessful"
        @process-finish="saveFinish"
        @click="check"
    >{{ btnLabel }}</sw-button-process>
</div>
`;var{Component:d,Mixin:u}=Shopware;d.register("bunnycdn-api-test-button",{template:c,props:["btnLabel"],inject:["bunnycdnApiTest"],mixins:[u.getByName("notification")],data(){return{isLoading:!1,isSaveSuccessful:!1}},computed:{pluginConfigData(){let e=this.$parent;for(let t=0;t<20;t++){if(e.actualConfigData)return e.actualConfigData.null;e=e.$parent}throw"Can not get pluginConfigData"}},methods:{saveFinish(){this.isSaveSuccessful=!1},check(){this.isLoading=!0,this.bunnycdnApiTest.check(this.pluginConfigData).then(e=>{e.success?(this.isSaveSuccessful=!0,this.createNotificationSuccess({title:this.$tc("bunnycdn-api-test-button.title"),message:this.$tc("bunnycdn-api-test-button.success")})):this.createNotificationError({title:this.$tc("bunnycdn-api-test-button.title"),message:this.$tc("bunnycdn-api-test-button.error")}),setTimeout(()=>{this.isLoading=!1},2500)})}}});var{Component:p,Mixin:w}=Shopware;p.register("bunnycdn-config-restriction",{template:" ",created(){this.checkAndHideSetting()},updated(){this.checkAndHideSetting()},methods:{checkAndHideSetting(){let e=document.querySelectorAll('input[name^="FroshPlatformBunnycdnMediaStorage.config"],.sw-plugin-config__save-action');this.pluginConfigData().currentSalesChannelId?e.forEach(t=>{t.setAttribute("disabled","disabled")}):e.forEach(t=>{t.removeAttribute("disabled")})},pluginConfigData(){let e=this.$parent;for(let t=0;t<20;t++){if(e.actualConfigData)return e;e=e.$parent}throw"Can not get pluginConfigData"}}});var l=`<!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
{% block sw_alert %}
    <div
        class="sw-alert"
        :class="alertClasses"
        role="alert"
    >
        <!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
        {% block sw_alert_inner %}
            <!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
            {% block sw_alert_icon %}
                <slot name="customIcon">
                    <sw-icon
                        v-if="showIcon"
                        size="20px"
                        class="sw-alert__icon"
                        :name="alertIcon"
                        decorative
                    />
                </slot>
            {% endblock %}

            <!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
            {% block sw_alert_body %}
                <div
                    class="sw-alert__body"
                    :class="alertBodyClasses"
                >
                    <!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
                    {% block sw_alert_title %}
                        <div
                            v-if="title"
                            class="sw-alert__title"
                        >
                            {{ title }}
                        </div>
                    {% endblock %}

                    <!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
                    {% block sw_alert_message %}
                        <div class="sw-alert__message">
                            {{ message }}
                        </div>
                    {% endblock %}
                </div>
            {% endblock %}
        {% endblock %}
    </div>
{% endblock %}
`;var{Component:g}=Shopware;g.extend("bunnycdn-alert","sw-alert",{template:l,props:{message:{type:String,required:!1,default:""}}});})();
