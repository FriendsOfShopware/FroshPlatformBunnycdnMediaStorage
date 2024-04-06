(()=>{var e=`<!-- eslint-disable-next-line sw-deprecation-rules/no-twigjs-blocks -->
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
`;var{Component:l}=Shopware;l.extend("bunnycdn-alert","sw-alert",{template:e,props:{message:{type:String,required:!1,default:""}}});})();
