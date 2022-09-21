!function(t){var e={};function n(o){if(e[o])return e[o].exports;var r=e[o]={i:o,l:!1,exports:{}};return t[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=t,n.c=e,n.d=function(t,e,o){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)n.d(o,r,function(e){return t[e]}.bind(null,r));return o},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="/bundles/froshplatformbunnycdnmediastorage/",n(n.s="fC+e")}({DSHB:function(t){t.exports=JSON.parse('{"bunnycdn-api-test-button":{"title":"API Test","success":"Verbindung wurde erfolgreich getestet","error":"Verbindung konnte nicht hergestellt werden. Bitte prüfe die Konfiguration."}}')},K4CE:function(t,e){var n=Shopware,o=n.Component;n.Mixin;o.register("bunnycdn-config-restriction",{template:" ",created:function(){this.checkAndHideSetting()},updated:function(){this.checkAndHideSetting()},methods:{checkAndHideSetting:function(){var t=document.querySelectorAll('input[name^="FroshPlatformBunnycdnMediaStorage.config"],.sw-plugin-config__save-action');this.pluginConfigData().currentSalesChannelId?t.forEach((function(t){t.setAttribute("disabled","disabled")})):t.forEach((function(t){t.removeAttribute("disabled")}))},pluginConfigData:function(){for(var t=this.$parent,e=0;e<20;e++){if(t.actualConfigData)return t;t=t.$parent}throw"Can not get pluginConfigData"}}})},"X+Vo":function(t,e){function n(t){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function o(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function r(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}function i(t,e){return(i=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}function c(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=a(t);if(e){var r=a(this).constructor;n=Reflect.construct(o,arguments,r)}else n=o.apply(this,arguments);return u(this,n)}}function u(t,e){if(e&&("object"===n(e)||"function"==typeof e))return e;if(void 0!==e)throw new TypeError("Derived constructors may only return object or undefined");return function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t)}function a(t){return(a=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}var s=Shopware.Classes.ApiService,f=Shopware.Application,l=function(t){!function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),Object.defineProperty(t,"prototype",{writable:!1}),e&&i(t,e)}(f,t);var e,n,u,a=c(f);function f(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"bunnycdn-api-test";return o(this,f),a.call(this,t,e,n)}return e=f,(n=[{key:"check",value:function(t){var e=this.getBasicHeaders({});return this.httpClient.post("_action/".concat(this.getApiBasePath(),"/check"),t,{headers:e}).then((function(t){return s.handleResponse(t)}))}}])&&r(e.prototype,n),u&&r(e,u),Object.defineProperty(e,"prototype",{writable:!1}),f}(s);f.addServiceProvider("bunnycdnApiTest",(function(t){var e=f.getContainer("init");return new l(e.httpClient,t.loginService)}))},"fC+e":function(t,e,n){"use strict";n.r(e);n("X+Vo");var o=Shopware,r=o.Component,i=o.Mixin;r.register("bunnycdn-api-test-button",{template:'<div>\n    <sw-button-process\n        :isLoading="isLoading"\n        :processSuccess="isSaveSuccessful"\n        @process-finish="saveFinish"\n        @click="check"\n    >{{ btnLabel }}</sw-button-process>\n</div>\n',props:["btnLabel"],inject:["bunnycdnApiTest"],mixins:[i.getByName("notification")],data:function(){return{isLoading:!1,isSaveSuccessful:!1}},computed:{pluginConfigData:function(){for(var t=this.$parent,e=0;e<20;e++){if(t.actualConfigData)return t.actualConfigData.null;t=t.$parent}throw"Can not get pluginConfigData"}},methods:{saveFinish:function(){this.isSaveSuccessful=!1},check:function(){var t=this;this.isLoading=!0,this.bunnycdnApiTest.check(this.pluginConfigData).then((function(e){e.success?(t.isSaveSuccessful=!0,t.createNotificationSuccess({title:t.$tc("bunnycdn-api-test-button.title"),message:t.$tc("bunnycdn-api-test-button.success")})):t.createNotificationError({title:t.$tc("bunnycdn-api-test-button.title"),message:t.$tc("bunnycdn-api-test-button.error")}),setTimeout((function(){t.isLoading=!1}),2500)}))}}});n("K4CE");var c=n("DSHB"),u=n("wPwm");Shopware.Locale.extend("de-DE",c),Shopware.Locale.extend("en-GB",u)},wPwm:function(t){t.exports=JSON.parse('{"bunnycdn-api-test-button":{"title":"API Test","success":"Connection was successfully tested","error":"Connection could not be established. Please check the config."}}')}});