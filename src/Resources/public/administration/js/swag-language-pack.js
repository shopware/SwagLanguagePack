(function(){var e,a,n,t,s,i,g,l,r={979:function(){},592:function(){},580:function(){},315:function(){let{Component:e,Defaults:a}=Shopware,{Criteria:n}=Shopware.Data;e.override("sw-language-switch",{computed:{languageCriteria(){return this.$super("languageCriteria").addFilter(n.multi("OR",[n.equals("extensions.swagLanguagePackLanguage.id",null),n.equals("extensions.swagLanguagePackLanguage.salesChannelActive",!0),n.equals("id",a.systemLanguageId)]))}}})},585:function(){let e;let{Criteria:a}=Shopware.Data,{warn:n}=Shopware.Utils.debug,t=Shopware.Service("repositoryFactory").create("swag_language_pack_language"),s=new a().addFilter(a.equals("administrationActive",!0)).addAggregation(a.terms("locales","language.locale.code",null,null,null)),i=Shopware.Plugin.addBootPromise();t.search(s).then(a=>{a.aggregations.locales.buckets.forEach(({key:a})=>{!1===Shopware.Locale.getByName(a)&&(e=a,Shopware.Locale.register(a,{}))}),i()}).catch(()=>{let a='Unable to register "packLanguages".';void 0!==e&&(a+=` Problems occurred while installing language: ${e}`),n("SwagLanguagePack",a),i()})},479:function(e,a,n){Shopware.Component.override("sw-extension-uninstall-modal",()=>n.e(561).then(n.bind(n,561)))},130:function(){let{Component:e,Defaults:a}=Shopware,{Criteria:n}=Shopware.Data;e.override("sw-first-run-wizard-welcome",{methods:{getLanguageCriteria(){return this.$super("getLanguageCriteria").addAssociation("swagLanguagePackLanguage").addSorting(n.sort("name","ASC")).addFilter(n.multi("OR",[n.equals("extensions.swagLanguagePackLanguage.id",null),n.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),n.equals("id",a.systemLanguageId)])).setLimit(null)}}})},823:function(){let{Component:e}=Shopware,{Criteria:a}=Shopware.Data;e.override("sw-sales-channel-detail-domains",{computed:{snippetSetCriteria(){let e=this.salesChannel.languages.map(e=>e.locale?.code).filter(Boolean);return 0===e.length?this.$super("snippetSetCriteria"):this.$super("snippetSetCriteria").addFilter(a.equalsAny("iso",e))}}})},410:function(){let{Component:e}=Shopware;e.override("sw-sales-channel-detail",{methods:{getLoadSalesChannelCriteria(){return this.$super("getLoadSalesChannelCriteria").addAssociation("languages.locale")}}})},786:function(){let{Component:e}=Shopware,{Criteria:a}=Shopware.Data;e.override("sw-users-permissions-user-create",{computed:{languageCriteria(){return this.$super("languageCriteria").addFilter(a.multi("OR",[a.equals("extensions.swagLanguagePackLanguage.id",null),a.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),a.equals("id",Shopware.Defaults.systemLanguageId)]))}},methods:{onSave(){this.$super("onSave")}}})},777:function(){let{Component:e}=Shopware,{Criteria:a}=Shopware.Data;e.override("sw-users-permissions-user-detail",{computed:{languageCriteria(){let e=this.$super("languageCriteria");return e.addFilter(a.multi("OR",[a.equals("extensions.swagLanguagePackLanguage.id",null),a.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),a.equals("id",Shopware.Defaults.systemLanguageId)])),e}}})},322:function(){Shopware.Service("privileges").addPrivilegeMappingEntry({category:"permissions",parent:"settings",key:"language",roles:{viewer:{privileges:["sales_channel:read","sales_channel_domain:read"]},editor:{privileges:["swag_language_pack_language:update","user:read","user:update"]}}})},197:function(e,a,n){var t=n(979);t.__esModule&&(t=t.default),"string"==typeof t&&(t=[[e.id,t,""]]),t.locals&&(e.exports=t.locals),n(346).Z("86ac3602",t,!0,{})},316:function(e,a,n){var t=n(592);t.__esModule&&(t=t.default),"string"==typeof t&&(t=[[e.id,t,""]]),t.locals&&(e.exports=t.locals),n(346).Z("4dde44e9",t,!0,{})},226:function(e,a,n){var t=n(580);t.__esModule&&(t=t.default),"string"==typeof t&&(t=[[e.id,t,""]]),t.locals&&(e.exports=t.locals),n(346).Z("4c33d812",t,!0,{})},346:function(e,a,n){"use strict";function t(e,a){for(var n=[],t={},s=0;s<a.length;s++){var i=a[s],g=i[0],l={id:e+":"+s,css:i[1],media:i[2],sourceMap:i[3]};t[g]?t[g].parts.push(l):n.push(t[g]={id:g,parts:[l]})}return n}n.d(a,{Z:function(){return w}});var s="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!s)throw Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var i={},g=s&&(document.head||document.getElementsByTagName("head")[0]),l=null,r=0,o=!1,c=function(){},u=null,d="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function w(e,a,n,s){o=n,u=s||{};var g=t(e,a);return m(g),function(a){for(var n=[],s=0;s<g.length;s++){var l=i[g[s].id];l.refs--,n.push(l)}a?m(g=t(e,a)):g=[];for(var s=0;s<n.length;s++){var l=n[s];if(0===l.refs){for(var r=0;r<l.parts.length;r++)l.parts[r]();delete i[l.id]}}}}function m(e){for(var a=0;a<e.length;a++){var n=e[a],t=i[n.id];if(t){t.refs++;for(var s=0;s<t.parts.length;s++)t.parts[s](n.parts[s]);for(;s<n.parts.length;s++)t.parts.push(k(n.parts[s]));t.parts.length>n.parts.length&&(t.parts.length=n.parts.length)}else{for(var g=[],s=0;s<n.parts.length;s++)g.push(k(n.parts[s]));i[n.id]={id:n.id,refs:1,parts:g}}}}function h(){var e=document.createElement("style");return e.type="text/css",g.appendChild(e),e}function k(e){var a,n,t=document.querySelector("style["+d+'~="'+e.id+'"]');if(t){if(o)return c;t.parentNode.removeChild(t)}if(p){var s=r++;a=f.bind(null,t=l||(l=h()),s,!1),n=f.bind(null,t,s,!0)}else a=b.bind(null,t=h()),n=function(){t.parentNode.removeChild(t)};return a(e),function(t){t?(t.css!==e.css||t.media!==e.media||t.sourceMap!==e.sourceMap)&&a(e=t):n()}}var _=function(){var e=[];return function(a,n){return e[a]=n,e.filter(Boolean).join("\n")}}();function f(e,a,n,t){var s=n?"":t.css;if(e.styleSheet)e.styleSheet.cssText=_(a,s);else{var i=document.createTextNode(s),g=e.childNodes;g[a]&&e.removeChild(g[a]),g.length?e.insertBefore(i,g[a]):e.appendChild(i)}}function b(e,a){var n=a.css,t=a.media,s=a.sourceMap;if(t&&e.setAttribute("media",t),u.ssrId&&e.setAttribute(d,a.id),s&&(n+="\n/*# sourceURL="+s.sources[0]+" */\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(s))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}}},o={};function c(e){var a=o[e];if(void 0!==a)return a.exports;var n=o[e]={id:e,exports:{}};return r[e](n,n.exports,c),n.exports}c.m=r,c.d=function(e,a){for(var n in a)c.o(a,n)&&!c.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:a[n]})},c.f={},c.e=function(e){return Promise.all(Object.keys(c.f).reduce(function(a,n){return c.f[n](e,a),a},[]))},c.u=function(e){return"static/js/e1e15971eecde1bc8bd9.js"},c.miniCssF=function(e){return"static/css/"+(728===e?"swag-language-pack":e)+".css"},c.o=function(e,a){return Object.prototype.hasOwnProperty.call(e,a)},e={},a="swag-language-pack:",c.l=function(n,t,s,i){if(e[n]){e[n].push(t);return}if(void 0!==s)for(var g,l,r=document.getElementsByTagName("script"),o=0;o<r.length;o++){var u=r[o];if(u.getAttribute("src")==n||u.getAttribute("data-webpack")==a+s){g=u;break}}g||(l=!0,(g=document.createElement("script")).charset="utf-8",g.timeout=120,c.nc&&g.setAttribute("nonce",c.nc),g.setAttribute("data-webpack",a+s),g.src=n),e[n]=[t];var d=function(a,t){g.onerror=g.onload=null,clearTimeout(p);var s=e[n];if(delete e[n],g.parentNode&&g.parentNode.removeChild(g),s&&s.forEach(function(e){return e(t)}),a)return a(t)},p=setTimeout(d.bind(null,void 0,{type:"timeout",target:g}),12e4);g.onerror=d.bind(null,g.onerror),g.onload=d.bind(null,g.onload),l&&document.head.appendChild(g)},c.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},c.p="bundles/swaglanguagepack/",n=function(e,a,n,t){var s=document.createElement("link");return s.rel="stylesheet",s.type="text/css",s.onerror=s.onload=function(i){if(s.onerror=s.onload=null,"load"===i.type)n();else{var g=i&&("load"===i.type?"missing":i.type),l=i&&i.target&&i.target.href||a,r=Error("Loading CSS chunk "+e+" failed.\n("+l+")");r.code="CSS_CHUNK_LOAD_FAILED",r.type=g,r.request=l,s.parentNode.removeChild(s),t(r)}},s.href=a,document.head.appendChild(s),s},t=function(e,a){for(var n=document.getElementsByTagName("link"),t=0;t<n.length;t++){var s=n[t],i=s.getAttribute("data-href")||s.getAttribute("href");if("stylesheet"===s.rel&&(i===e||i===a))return s}for(var g=document.getElementsByTagName("style"),t=0;t<g.length;t++){var s=g[t],i=s.getAttribute("data-href");if(i===e||i===a)return s}},s={728:0},c.f.miniCss=function(e,a){s[e]?a.push(s[e]):0!==s[e]&&({561:1})[e]&&a.push(s[e]=new Promise(function(a,s){var i=c.miniCssF(e),g=c.p+i;if(t(i,g))return a();n(e,g,a,s)}).then(function(){s[e]=0},function(a){throw delete s[e],a}))},i={728:0},c.f.j=function(e,a){var n=c.o(i,e)?i[e]:void 0;if(0!==n){if(n)a.push(n[2]);else{var t=new Promise(function(a,t){n=i[e]=[a,t]});a.push(n[2]=t);var s=c.p+c.u(e),g=Error();c.l(s,function(a){if(c.o(i,e)&&(0!==(n=i[e])&&(i[e]=void 0),n)){var t=a&&("load"===a.type?"missing":a.type),s=a&&a.target&&a.target.src;g.message="Loading chunk "+e+" failed.\n("+t+": "+s+")",g.name="ChunkLoadError",g.type=t,g.request=s,n[1](g)}},"chunk-"+e,e)}}},g=function(e,a){var n,t,s=a[0],g=a[1],l=a[2],r=0;if(s.some(function(e){return 0!==i[e]})){for(n in g)c.o(g,n)&&(c.m[n]=g[n]);l&&l(c)}for(e&&e(a);r<s.length;r++)t=s[r],c.o(i,t)&&i[t]&&i[t][0](),i[t]=0},(l=window["webpackJsonpPluginswag-language-pack"]=window["webpackJsonpPluginswag-language-pack"]||[]).forEach(g.bind(null,0)),l.push=g.bind(null,l.push.bind(l)),window?.__sw__?.assetPath&&(c.p=window.__sw__.assetPath+"/bundles/swaglanguagepack/"),function(){"use strict";for(let e of(c(315),c(585),["ad","ae","af","ag","ai","al","am","ao","aq","ar","as","at","au","aw","ax","az","ba","bb","bd","be","bf","bg","bh","bi","bj","bl","bm","bn","bo","bq","br","bs","bt","bv","bw","by","bz","ca","cc","cd","cf","cg","ch","ci","ck","cl","cm","cn","co","cr","cu","cv","cw","cx","cy","cz","de","dj","dk","dm","do","dz","ec","ee","eg","eh","er","es-ca","es-ga","es","et","eu","fi","fj","fk","fm","fo","fr","ga","gb-eng","gb-nir","gb-sct","gb-wls","gb","gd","ge","gf","gg","gh","gi","gl","gm","gn","gp","gq","gr","gs","gt","gu","gw","gy","hk","hm","hn","hr","ht","hu","id","ie","il","im","in","io","iq","ir","is","it","je","jm","jo","jp","ke","kg","kh","ki","km","kn","kp","kr","kw","ky","kz","la","lb","lc","li","lk","lr","ls","lt","lu","lv","ly","ma","mc","md","me","mf","mg","mh","mk","ml","mm","mn","mo","mp","mq","mr","ms","mt","mu","mv","mw","mx","my","mz","na","nc","ne","nf","ng","ni","nl","no","np","nr","nu","nz","om","pa","pe","pf","pg","ph","pk","pl","pm","pn","pr","ps","pt","pw","py","qa","re","ro","rs","ru","rw","sa","sb","sc","sd","se","sg","sh","si","sj","sk","sl","sm","sn","so","sr","ss","st","sv","sx","sy","sz","tc","td","tf","tg","th","tj","tk","tl","tm","tn","to","tr","tt","tv","tw","tz","ua","ug","um","un","us","uy","uz","va","vc","ve","vg","vi","vn","vu","wf","ws","xk","ye","yt","za","zm","zw"])){let a=`swag-language-pack-flag-${e}`;Shopware.Component.register(a,()=>(function(e,a){return{template:`<div><img :src="assetFilter('swaglanguagepack/static/flags/${a}.svg')"></div>`,name:e,mounted(){console.warn(`DEPRECATED: Replace '<${e} />' with '<img :src="assetFilter('swaglanguagepack/static/flags/${a}.svg')">'`)},computed:{assetFilter(){return Shopware.Filter.getByName("asset")}}}})(a,e))}c(479),c(130),c(823),c(410);let{Component:e,Defaults:a}=Shopware,{Criteria:n}=Shopware.Data;e.override("sw-sales-channel-detail-base",{template:'{% block sw_sales_channel_detail_base_general_input_languages %}\n    <sw-sales-channel-defaults-select\n        v-if="!isProductComparison"\n        :salesChannel="salesChannel"\n        :criteria="languageCriteria"\n        propertyName="languages"\n        defaultPropertyName="languageId"\n        propertyNameInDomain="languageId"\n        :propertyLabel="$tc(\'sw-sales-channel.detail.labelInputLanguages\')"\n        :defaultPropertyLabel="$tc(\'sw-sales-channel.detail.labelInputDefaultLanguage\')"\n        :disabled="!acl.can(\'sales_channel.editor\')"\n    ></sw-sales-channel-defaults-select>\n{% endblock %}\n',computed:{languageCriteria(){return new n().addAssociation("swagLanguagePackLanguage").addAssociation("locale").addSorting(n.sort("name","ASC")).addFilter(n.multi("OR",[n.equals("extensions.swagLanguagePackLanguage.id",null),n.equals("extensions.swagLanguagePackLanguage.salesChannelActive",!0),n.equals("id",a.systemLanguageId)]))}}});let{Component:t}=Shopware,{Criteria:s}=Shopware.Data;t.override("sw-settings-language-list",{template:'{% block sw_settings_language_list_content_list_delete_action %}\n    <template #delete-action="{ item, showDelete }">\n\n        {% block sw_settings_language_list_content_list_delete_action_language_pack %}\n            <template v-if="isPackLanguage(item.id)">\n                <sw-context-menu-item\n                        v-tooltip.bottom="$tc(\'swag-language-pack.sw-settings-language-list.deleteLanguagePackTooltip\')"\n                        class="sw-settings-language-list__delete-action"\n                        variant="danger"\n                        disabled\n                        @click="showDelete(item.id)">\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </template>\n        {% endblock %}\n\n        {% block sw_settings_language_list_content_list_delete_action_language %}\n            <template v-else>\n                <sw-context-menu-item\n                        v-tooltip.bottom="tooltipDelete(item.id)"\n                        class="sw-settings-language-list__delete-action"\n                        variant="danger"\n                        :disabled="isDefault(item.id) || !allowDelete"\n                        @click="showDelete(item.id)">\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </template>\n        {% endblock %}\n\n    </template>\n{% endblock %}\n',data(){return{packLanguageLanguageIds:[]}},computed:{packLanguageCriteria(){return new s(1,50).addFilter(s.not("and",[s.equals("swagLanguagePackLanguage.id",null)]))}},created(){this.createdComponent()},methods:{createdComponent(){this.getPackLanguageLanguageIds()},getPackLanguageLanguageIds(){this.languageRepository.searchIds(this.packLanguageCriteria).then(e=>{this.packLanguageLanguageIds=e.data})},isPackLanguage(e){return this.packLanguageLanguageIds.includes(e)}}}),c(322),c(197);let{Component:i}=Shopware;i.register("swag-language-pack-flag",{template:'{% block swag_language_pack_flag %}\n<div class="swag-language-pack-flag">\n    <img :src="assetFilter(`swaglanguagepack/static/flags/${countryCode}.svg`)">\n</div>\n{% endblock %}\n',props:{locale:{type:String,required:!1,default:""}},computed:{assetFilter(){return Shopware.Filter.getByName("asset")},countryCode(){return this.locale.split("-")[1].toLowerCase()}}}),c(316);let{Component:g}=Shopware;g.register("swag-pack-language-entry",{template:'{% block swag_pack_language_entry %}\n<div class="swag-language-pack-entry">\n\n    {% block swag_pack_language_entry_flag %}\n    <swag-language-pack-flag class="swag-language-pack-entry__flag" :locale="flagLocale"/>\n    {% endblock %}\n\n    {% block swag_pack_language_entry_content %}\n    <div class="swag-language-pack-entry__content">\n\n        {% block swag_pack_language_entry_content_name %}\n        <div class="swag-language-pack-entry__name">\n            {{ label }}\n        </div>\n        {% endblock %}\n\n        {% block swag_pack_language_entry_content_description %}\n        <div class="swag-language-pack-entry__description">\n            {{ description }}\n        </div>\n        {% endblock %}\n\n    </div>\n    {% endblock %}\n\n    {% block swag_pack_language_entry_switch %}\n    <sw-switch-field\n        v-model:value="value[field]"\n        ref="packLanguageToggle"\n        :disabled="disabled"\n    />\n    {% endblock %}\n</div>\n{% endblock %}\n',inject:["acl"],props:{value:{type:Object,required:!0},field:{type:String,required:!0},label:{type:String,required:!0},disabled:{type:Boolean,required:!0,default:!1},description:{type:String,required:!1,default:""},flagLocale:{type:String,required:!1,default:""}}});let{Component:l}=Shopware;l.register("swag-language-pack-settings-icon",{template:'{% block swag_language_pack_settings_icon %}\n    <sw-icon name="default-location-flag"></sw-icon>\n{% endblock %}\n'});let{Component:r,Defaults:o}=Shopware,{Criteria:u}=Shopware.Data;r.register("swag-language-pack-settings",{template:'{% block swag_language_pack_settings %}\n<sw-page>\n\n    {% block swag_language_pack_settings_header %}\n    <template #smart-bar-header>\n        <h2>\n            {{ $tc(\'sw-settings.index.title\') }}\n            <sw-icon\n                name="regular-chevron-right-xs"\n                small\n            />\n            {{ $tc(\'swag-language-pack.settings.header\') }}\n        </h2>\n    </template>\n    {% endblock %}\n\n    {% block swag_language_pack_settings_actions %}\n    <template #smart-bar-actions>\n\n        {% block swag_language_pack_settings_actions_save %}\n        <sw-button-process\n            class="swag-language-pack-settings__save-action"\n            variant="primary"\n            :process-success="isSaveSuccessful"\n            :disabled="!acl.can(\'swag_language_pack_language:update\')"\n            @process-finish="onSaveFinish"\n            @click="onSave"\n        >\n            {{ $tc(\'global.default.save\') }}\n        </sw-button-process>\n        {% endblock %}\n\n    </template>\n    {% endblock %}\n\n    {% block swag_language_pack_settings_content %}\n    <template #content>\n\n        {% block swag_language_pack_settings_content_card_view %}\n        <sw-card-view>\n\n            {% block swag_language_pack_settings_content_tabs %}\n            <sw-tabs\n                v-if="!isLoading"\n                class="swag-language-pack-settings__tabs"\n                position-identifier="swag-language-pack-settings__tabs"\n            >\n\n                {% block swag_language_pack_settings_content_tabs_administration %}\n                <sw-tabs-item\n                    class="swag-language-pack-settings__tab-administration"\n                    :route="{ name: \'swag.language.pack.index.administration\' }"\n                    :disabled="!acl.can(\'language.viewer\')"\n                >\n                    {{ $tc(\'swag-language-pack.settings.card.administration.tabTitle\') }}\n                </sw-tabs-item>\n                {% endblock %}\n\n                {% block swag_language_pack_settings_content_tabs_sales_channel %}\n                <sw-tabs-item\n                    class="swag-language-pack-settings__tab-sales-channel"\n                    :route="{ name: \'swag.language.pack.index.salesChannel\' }"\n                    :disabled="!acl.can(\'language.viewer\')"\n                >\n                    {{ $tc(\'swag-language-pack.settings.card.salesChannel.tabTitle\') }}\n                </sw-tabs-item>\n                {% endblock %}\n            </sw-tabs>\n            {% endblock %}\n\n            {% block swag_language_pack_settings_content_router_view %}\n            <router-view\n                v-if="!isLoading"\n                :is-loading="isLoading"\n                :pack-languages="packLanguages"\n            />\n            {% endblock %}\n\n            {% block swag_language_pack_settings_content_loader %}\n            <sw-loader v-if="isLoading"/>\n            {% endblock %}\n\n            {% block swag_language_pack_settings_content_verify_user_modal %}\n            <sw-verify-user-modal\n                v-if="confirmPasswordModal"\n                @verified="savePackLanguages"\n                @close="onCloseConfirmPasswordModal"\n            />\n            {% endblock %}\n\n        </sw-card-view>\n        {% endblock %}\n\n    </template>\n    {% endblock %}\n\n</sw-page>\n{% endblock %}\n',inject:["repositoryFactory","userService","acl"],mixins:["notification"],data(){return{isLoading:!1,isSaveSuccessful:!1,hasChanges:!1,packLanguages:[],fallbackLocaleId:null,confirmPasswordModal:!1}},metaInfo(){return{title:this.$createTitle()}},computed:{packLanguageRepository(){return this.repositoryFactory.create("swag_language_pack_language")},languageRepository(){return this.repositoryFactory.create("language")},userRepository(){return this.repositoryFactory.create("user")},packLanguageCriteria(){return new u().addSorting(u.sort("language.name","ASC")).addAssociation("language.salesChannels.domains").addAssociation("language.locale")}},created(){this.createdComponent()},beforeRouteLeave(e,a,n){n(),this.hasChanges&&window.location.reload()},methods:{createdComponent(){this.loadPackLanguages()},loadPackLanguages(){return this.isLoading=!0,this.packLanguageRepository.search(this.packLanguageCriteria).then(e=>{this.packLanguages=e}).finally(()=>{this.isLoading=!1})},onSave(){this.confirmPasswordModal=!0},onSaveFinish(){this.isSaveSuccessful=!1},onCloseConfirmPasswordModal(){this.confirmPasswordModal=!1},savePackLanguages(){return this.isLoading=!0,this.validateStates(this.packLanguages).then(()=>this.packLanguageRepository.saveAll(this.packLanguages).then(()=>(this.hasChanges=!0,this.resetInvalidUserLanguages())).catch(()=>{this.createNotificationError({message:this.$tc("swag-language-pack.settings.card.messageSaveError")})})).catch(e=>{let a=e.map(e=>e.language.name),n=`<b>${a.join(", ")}</b>`;this.createNotificationError({message:this.$tc("swag-language-pack.settings.card.messageSalesChannelActiveError",0,{languages:n}),autoClose:!1})}).finally(()=>{this.isLoading=!1,this.isSaveSuccessful=!0,this.loadPackLanguages()})},validateStates(e){return new Promise((a,n)=>{let t=e.filter(e=>!(e.salesChannelActive||e.language.salesChannels.length<=0));t.length>0&&n(t),a()})},async resetInvalidUserLanguages(){let e=await this.fetchInvalidLocaleIds();if(!e||e.length<=0)return Promise.resolve();let a=await this.userService.getUser(),n=new u().addFilter(u.equalsAny("localeId",e)),t=await this.userRepository.search(n);return t=t.reduce((e,n)=>(n.localeId=this.fallbackLocaleId,a.data.id===n.id&&Shopware.Service("localeHelper").setLocaleWithId(n.localeId),e.push(n),e),[]),this.userRepository.saveAll(t)},async fetchInvalidLocaleIds(){let e=new u().addFilter(u.equals("extensions.swagLanguagePackLanguage.administrationActive",!1)),a=await this.languageRepository.search(e),n=new u().setIds([o.systemLanguageId]),t=await this.languageRepository.search(n);return this.fallbackLocaleId=t.first().localeId,a.map(e=>e.localeId)}}}),c(226);let{Component:d}=Shopware;d.register("swag-language-pack-settings-base",{template:'{% block swag_language_pack_settings_base %}\n{% block swag_language_pack_settings_base_card_view_language_selection %}\n    <sw-card\n        class="swag-language-pack-settings-base"\n        position-identifier="swag-language-pack-settings-base"\n        :title="$tc(`swag-language-pack.settings.card.${settingsType}.title`)"\n        :disabled="isLoading"\n    >\n\n        {% block swag_language_pack_settings_base_card_view_card_loader %}\n        <sw-loader v-if="isLoading"/>\n        {% endblock %}\n\n        {% block swag_language_pack_settings_base_card_view_description%}\n        <div\n            v-html="description"\n            class="swag-language-pack-settings-base__description"\n        />\n        {% endblock %}\n\n        {% block swag_language_pack_settings_base_card_view_language_selection_languages %}\n        <template\n            v-for="packLanguage in packLanguages"\n            :key="packLanguage.id"\n        >\n            {% block swag_language_pack_settings_base_card_view_language_selection_language %}\n            <swag-pack-language-entry\n                class="swag-language-pack-settings-base__entry"\n                v-model:value="packLanguage"\n                :field="`${settingsType}Active`"\n                :label="packLanguage.language.name"\n                :description="packLanguage.language.locale?.code"\n                :flag-locale="packLanguage.language.locale?.code"\n                :disabled="!acl.can(\'swag_language_pack_language:update\')"\n            />\n            {% endblock %}\n        </template>\n        {% endblock %}\n\n    </sw-card>\n{% endblock %}\n{% endblock %}\n',inject:["acl"],props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0},settingsType:{type:String,required:!0,validator(e){return["administration","salesChannel"].includes(e)}}},computed:{description(){let e=`<a href="#/sw/profile/index">
                ${this.$tc("swag-language-pack.settings.card.administration.descriptionTargetLinkText")}
            </a>`;return this.$tc(`swag-language-pack.settings.card.${this.settingsType}.description`,0,{userInterfaceLanguageLink:e})}}});let{Component:p}=Shopware;p.register("swag-language-pack-settings-administration",{template:'{% block swag_language_pack_settings_administration %}\n    <swag-language-pack-settings-base\n        :isLoading="isLoading"\n        :packLanguages="packLanguages"\n        settingsType="administration">\n    </swag-language-pack-settings-base>\n{% endblock %}',props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0}}});let{Component:w}=Shopware;w.register("swag-language-pack-settings-sales-channel",{template:'{% block swag_language_pack_settings_sales_channel %}\n    <swag-language-pack-settings-base\n        :isLoading="isLoading"\n        :packLanguages="packLanguages"\n        settingsType="salesChannel">\n    </swag-language-pack-settings-base>\n{% endblock %}\n',props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0}}});let{Module:m}=Shopware;m.register("swag-language-pack",{type:"plugin",name:"SwagLanguagePack",title:"swag-language-pack.general.mainMenuItemGeneral",description:"swag-language-pack.general.descriptionTextModule",version:"1.0.0",targetVersion:"1.0.0",color:"#9AA8B5",icon:"regular-cog",routes:{index:{component:"swag-language-pack-settings",path:"index",redirect:{name:"swag.language.pack.index.administration"},meta:{parentPath:"sw.settings.index",privilege:"language.viewer"},children:{administration:{component:"swag-language-pack-settings-administration",path:"administration",meta:{parentPath:"sw.settings.index",privilege:"language.viewer"}},salesChannel:{component:"swag-language-pack-settings-sales-channel",path:"sales-channel",meta:{parentPath:"sw.settings.index",privilege:"language.viewer"}}}}},settingsItem:{group:"plugins",to:"swag.language.pack.index",icon:"regular-language",backgroundEnabled:!0,privilege:"language.viewer"}}),c(777),c(786)}()})();