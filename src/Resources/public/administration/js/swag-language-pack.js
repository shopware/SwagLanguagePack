(function(){var e={176:function(){},145:function(){},972:function(){},404:function(){let{Component:e,Defaults:a}=Shopware,{Criteria:n}=Shopware.Data;e.override("sw-language-switch",{computed:{languageCriteria(){return this.$super("languageCriteria").addFilter(n.multi("OR",[n.equals("extensions.swagLanguagePackLanguage.id",null),n.equals("extensions.swagLanguagePackLanguage.salesChannelActive",!0),n.equals("id",a.systemLanguageId)]))}}})},304:function(){let e;let{Criteria:a}=Shopware.Data,{warn:n}=Shopware.Utils.debug,t=Shopware.Service("repositoryFactory").create("swag_language_pack_language"),s=new a().addFilter(a.equals("administrationActive",!0)).addAggregation(a.terms("locales","language.locale.code",null,null,null)),i=Shopware.Plugin.addBootPromise();t.search(s).then(a=>{a.aggregations.locales.buckets.forEach(({key:a})=>{!1===Shopware.Locale.getByName(a)&&(e=a,Shopware.Locale.register(a,{}))}),i()}).catch(()=>{let a='Unable to register "packLanguages".';void 0!==e&&(a+=` Problems occurred while installing language: ${e}`),n("SwagLanguagePack",a),i()})},825:function(){let{Component:e,Defaults:a}=Shopware,{Criteria:n}=Shopware.Data;e.override("sw-first-run-wizard-welcome",{methods:{getLanguageCriteria(){return this.$super("getLanguageCriteria").addAssociation("swagLanguagePackLanguage").addSorting(n.sort("name","ASC")).addFilter(n.multi("OR",[n.equals("extensions.swagLanguagePackLanguage.id",null),n.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),n.equals("id",a.systemLanguageId)])).setLimit(null)}}})},577:function(){let{Component:e}=Shopware,{Criteria:a}=Shopware.Data;e.override("sw-sales-channel-detail-domains",{computed:{snippetSetCriteria(){let e=this.salesChannel.languages.map(e=>e.locale?.code).filter(Boolean);return 0===e.length?this.$super("snippetSetCriteria"):this.$super("snippetSetCriteria").addFilter(a.equalsAny("iso",e))}}})},281:function(){let{Component:e}=Shopware;e.override("sw-sales-channel-detail",{methods:{getLoadSalesChannelCriteria(){return this.$super("getLoadSalesChannelCriteria").addAssociation("languages.locale")}}})},876:function(){let{Component:e}=Shopware,{Criteria:a}=Shopware.Data;e.override("sw-users-permissions-user-create",{computed:{languageCriteria(){return this.$super("languageCriteria").addFilter(a.multi("OR",[a.equals("extensions.swagLanguagePackLanguage.id",null),a.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),a.equals("id",Shopware.Defaults.systemLanguageId)]))}},methods:{onSave(){this.$super("onSave")}}})},673:function(){let{Component:e}=Shopware,{Criteria:a}=Shopware.Data;e.override("sw-users-permissions-user-detail",{computed:{languageCriteria(){let e=this.$super("languageCriteria");return e.addFilter(a.multi("OR",[a.equals("extensions.swagLanguagePackLanguage.id",null),a.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),a.equals("id",Shopware.Defaults.systemLanguageId)])),e}}})},26:function(){Shopware.Service("privileges").addPrivilegeMappingEntry({category:"permissions",parent:"settings",key:"language",roles:{viewer:{privileges:["sales_channel:read","sales_channel_domain:read"]},editor:{privileges:["swag_language_pack_language:update","user:read","user:update"]}}})},873:function(e,a,n){var t=n(176);t.__esModule&&(t=t.default),"string"==typeof t&&(t=[[e.id,t,""]]),t.locals&&(e.exports=t.locals),n(346).Z("f6c66fc0",t,!0,{})},52:function(e,a,n){var t=n(145);t.__esModule&&(t=t.default),"string"==typeof t&&(t=[[e.id,t,""]]),t.locals&&(e.exports=t.locals),n(346).Z("24bdfff0",t,!0,{})},427:function(e,a,n){var t=n(972);t.__esModule&&(t=t.default),"string"==typeof t&&(t=[[e.id,t,""]]),t.locals&&(e.exports=t.locals),n(346).Z("3bb17ada",t,!0,{})},346:function(e,a,n){"use strict";function t(e,a){for(var n=[],t={},s=0;s<a.length;s++){var i=a[s],g=i[0],l={id:e+":"+s,css:i[1],media:i[2],sourceMap:i[3]};t[g]?t[g].parts.push(l):n.push(t[g]={id:g,parts:[l]})}return n}n.d(a,{Z:function(){return w}});var s="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!s)throw Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var i={},g=s&&(document.head||document.getElementsByTagName("head")[0]),l=null,r=0,o=!1,c=function(){},u=null,d="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function w(e,a,n,s){o=n,u=s||{};var g=t(e,a);return k(g),function(a){for(var n=[],s=0;s<g.length;s++){var l=i[g[s].id];l.refs--,n.push(l)}a?k(g=t(e,a)):g=[];for(var s=0;s<n.length;s++){var l=n[s];if(0===l.refs){for(var r=0;r<l.parts.length;r++)l.parts[r]();delete i[l.id]}}}}function k(e){for(var a=0;a<e.length;a++){var n=e[a],t=i[n.id];if(t){t.refs++;for(var s=0;s<t.parts.length;s++)t.parts[s](n.parts[s]);for(;s<n.parts.length;s++)t.parts.push(_(n.parts[s]));t.parts.length>n.parts.length&&(t.parts.length=n.parts.length)}else{for(var g=[],s=0;s<n.parts.length;s++)g.push(_(n.parts[s]));i[n.id]={id:n.id,refs:1,parts:g}}}}function h(){var e=document.createElement("style");return e.type="text/css",g.appendChild(e),e}function _(e){var a,n,t=document.querySelector("style["+d+'~="'+e.id+'"]');if(t){if(o)return c;t.parentNode.removeChild(t)}if(p){var s=r++;a=b.bind(null,t=l||(l=h()),s,!1),n=b.bind(null,t,s,!0)}else a=f.bind(null,t=h()),n=function(){t.parentNode.removeChild(t)};return a(e),function(t){t?(t.css!==e.css||t.media!==e.media||t.sourceMap!==e.sourceMap)&&a(e=t):n()}}var m=function(){var e=[];return function(a,n){return e[a]=n,e.filter(Boolean).join("\n")}}();function b(e,a,n,t){var s=n?"":t.css;if(e.styleSheet)e.styleSheet.cssText=m(a,s);else{var i=document.createTextNode(s),g=e.childNodes;g[a]&&e.removeChild(g[a]),g.length?e.insertBefore(i,g[a]):e.appendChild(i)}}function f(e,a){var n=a.css,t=a.media,s=a.sourceMap;if(t&&e.setAttribute("media",t),u.ssrId&&e.setAttribute(d,a.id),s&&(n+="\n/*# sourceURL="+s.sources[0]+" */\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(s))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}}},a={};function n(t){var s=a[t];if(void 0!==s)return s.exports;var i=a[t]={id:t,exports:{}};return e[t](i,i.exports,n),i.exports}n.d=function(e,a){for(var t in a)n.o(a,t)&&!n.o(e,t)&&Object.defineProperty(e,t,{enumerable:!0,get:a[t]})},n.o=function(e,a){return Object.prototype.hasOwnProperty.call(e,a)},n.p="bundles/swaglanguagepack/",window?.__sw__?.assetPath&&(n.p=window.__sw__.assetPath+"/bundles/swaglanguagepack/"),function(){"use strict";for(let e of(n(404),n(304),["ad","ae","af","ag","ai","al","am","ao","aq","ar","as","at","au","aw","ax","az","ba","bb","bd","be","bf","bg","bh","bi","bj","bl","bm","bn","bo","bq","br","bs","bt","bv","bw","by","bz","ca","cc","cd","cf","cg","ch","ci","ck","cl","cm","cn","co","cr","cu","cv","cw","cx","cy","cz","de","dj","dk","dm","do","dz","ec","ee","eg","eh","er","es-ca","es-ga","es","et","eu","fi","fj","fk","fm","fo","fr","ga","gb-eng","gb-nir","gb-sct","gb-wls","gb","gd","ge","gf","gg","gh","gi","gl","gm","gn","gp","gq","gr","gs","gt","gu","gw","gy","hk","hm","hn","hr","ht","hu","id","ie","il","im","in","io","iq","ir","is","it","je","jm","jo","jp","ke","kg","kh","ki","km","kn","kp","kr","kw","ky","kz","la","lb","lc","li","lk","lr","ls","lt","lu","lv","ly","ma","mc","md","me","mf","mg","mh","mk","ml","mm","mn","mo","mp","mq","mr","ms","mt","mu","mv","mw","mx","my","mz","na","nc","ne","nf","ng","ni","nl","no","np","nr","nu","nz","om","pa","pe","pf","pg","ph","pk","pl","pm","pn","pr","ps","pt","pw","py","qa","re","ro","rs","ru","rw","sa","sb","sc","sd","se","sg","sh","si","sj","sk","sl","sm","sn","so","sr","ss","st","sv","sx","sy","sz","tc","td","tf","tg","th","tj","tk","tl","tm","tn","to","tr","tt","tv","tw","tz","ua","ug","um","un","us","uy","uz","va","vc","ve","vg","vi","vn","vu","wf","ws","xk","ye","yt","za","zm","zw"])){let a=`swag-language-pack-flag-${e}`;Shopware.Component.register(a,()=>(function(e,a){return{template:`<div><img :src="assetFilter('swaglanguagepack/static/flags/${a}.svg')"></div>`,name:e,mounted(){console.warn(`DEPRECATED: Replace '<${e} />' with '<img :src="assetFilter('swaglanguagepack/static/flags/${a}.svg')">'`)},computed:{assetFilter(){return Shopware.Filter.getByName("asset")}}}})(a,e))}n(825),n(577),n(281);let{Component:e,Defaults:a}=Shopware,{Criteria:t}=Shopware.Data;e.override("sw-sales-channel-detail-base",{template:'{% block sw_sales_channel_detail_base_general_input_languages %}\n    <sw-sales-channel-defaults-select\n        v-if="!isProductComparison"\n        :salesChannel="salesChannel"\n        :criteria="languageCriteria"\n        propertyName="languages"\n        defaultPropertyName="languageId"\n        propertyNameInDomain="languageId"\n        :propertyLabel="$tc(\'sw-sales-channel.detail.labelInputLanguages\')"\n        :defaultPropertyLabel="$tc(\'sw-sales-channel.detail.labelInputDefaultLanguage\')"\n        :disabled="!acl.can(\'sales_channel.editor\')"\n    ></sw-sales-channel-defaults-select>\n{% endblock %}\n',computed:{languageCriteria(){return new t().addAssociation("swagLanguagePackLanguage").addAssociation("locale").addSorting(t.sort("name","ASC")).addFilter(t.multi("OR",[t.equals("extensions.swagLanguagePackLanguage.id",null),t.equals("extensions.swagLanguagePackLanguage.salesChannelActive",!0),t.equals("id",a.systemLanguageId)]))}}});let{Component:s}=Shopware,{Criteria:i}=Shopware.Data;s.override("sw-settings-language-list",{template:'{% block sw_settings_language_list_content_list_delete_action %}\n    <template #delete-action="{ item, showDelete }">\n\n        {% block sw_settings_language_list_content_list_delete_action_language_pack %}\n            <template v-if="isPackLanguage(item.id)">\n                <sw-context-menu-item\n                        v-tooltip.bottom="$tc(\'swag-language-pack.sw-settings-language-list.deleteLanguagePackTooltip\')"\n                        class="sw-settings-language-list__delete-action"\n                        variant="danger"\n                        disabled\n                        @click="showDelete(item.id)">\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </template>\n        {% endblock %}\n\n        {% block sw_settings_language_list_content_list_delete_action_language %}\n            <template v-else>\n                <sw-context-menu-item\n                        v-tooltip.bottom="tooltipDelete(item.id)"\n                        class="sw-settings-language-list__delete-action"\n                        variant="danger"\n                        :disabled="isDefault(item.id) || !allowDelete"\n                        @click="showDelete(item.id)">\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </template>\n        {% endblock %}\n\n    </template>\n{% endblock %}\n',data(){return{packLanguageLanguageIds:[]}},computed:{packLanguageCriteria(){return new i(1,50).addFilter(i.not("and",[i.equals("swagLanguagePackLanguage.id",null)]))}},created(){this.createdComponent()},methods:{createdComponent(){this.getPackLanguageLanguageIds()},getPackLanguageLanguageIds(){this.languageRepository.searchIds(this.packLanguageCriteria).then(e=>{this.packLanguageLanguageIds=e.data})},isPackLanguage(e){return this.packLanguageLanguageIds.includes(e)}}}),n(26),n(873);let{Component:g}=Shopware;g.register("swag-language-pack-flag",{template:'{% block swag_language_pack_flag %}\n<div class="swag-language-pack-flag">\n    <img :src="assetFilter(`swaglanguagepack/static/flags/${countryCode}.svg`)">\n</div>\n{% endblock %}\n',props:{locale:{type:String,required:!1,default:""}},computed:{assetFilter(){return Shopware.Filter.getByName("asset")},countryCode(){return this.locale.split("-")[1].toLowerCase()}}}),n(52);let{Component:l}=Shopware;l.register("swag-pack-language-entry",{template:'{% block swag_pack_language_entry %}\n<div class="swag-language-pack-entry">\n\n    {% block swag_pack_language_entry_flag %}\n    <swag-language-pack-flag class="swag-language-pack-entry__flag" :locale="flagLocale"/>\n    {% endblock %}\n\n    {% block swag_pack_language_entry_content %}\n    <div class="swag-language-pack-entry__content">\n\n        {% block swag_pack_language_entry_content_name %}\n        <div class="swag-language-pack-entry__name">\n            {{ label }}\n        </div>\n        {% endblock %}\n\n        {% block swag_pack_language_entry_content_description %}\n        <div class="swag-language-pack-entry__description">\n            {{ description }}\n        </div>\n        {% endblock %}\n\n    </div>\n    {% endblock %}\n\n    {% block swag_pack_language_entry_switch %}\n    <sw-switch-field\n        v-model:value="value[field]"\n        ref="packLanguageToggle"\n        :disabled="disabled"\n    />\n    {% endblock %}\n</div>\n{% endblock %}\n',inject:["acl"],props:{value:{type:Object,required:!0},field:{type:String,required:!0},label:{type:String,required:!0},disabled:{type:Boolean,required:!0,default:!1},description:{type:String,required:!1,default:""},flagLocale:{type:String,required:!1,default:""}}});let{Component:r}=Shopware;r.register("swag-language-pack-settings-icon",{template:'{% block swag_language_pack_settings_icon %}\n    <sw-icon name="default-location-flag"></sw-icon>\n{% endblock %}\n'});let{Component:o,Defaults:c}=Shopware,{Criteria:u}=Shopware.Data;o.register("swag-language-pack-settings",{template:'{% block swag_language_pack_settings %}\n<sw-page>\n\n    {% block swag_language_pack_settings_header %}\n    <template #smart-bar-header>\n        <h2>\n            {{ $tc(\'sw-settings.index.title\') }}\n            <sw-icon\n                name="regular-chevron-right-xs"\n                small\n            />\n            {{ $tc(\'swag-language-pack.settings.header\') }}\n        </h2>\n    </template>\n    {% endblock %}\n\n    {% block swag_language_pack_settings_actions %}\n    <template #smart-bar-actions>\n\n        {% block swag_language_pack_settings_actions_save %}\n        <sw-button-process\n            class="swag-language-pack-settings__save-action"\n            variant="primary"\n            :process-success="isSaveSuccessful"\n            :disabled="!acl.can(\'swag_language_pack_language:update\')"\n            @process-finish="onSaveFinish"\n            @click="onSave"\n        >\n            {{ $tc(\'global.default.save\') }}\n        </sw-button-process>\n        {% endblock %}\n\n    </template>\n    {% endblock %}\n\n    {% block swag_language_pack_settings_content %}\n    <template #content>\n\n        {% block swag_language_pack_settings_content_card_view %}\n        <sw-card-view>\n\n            {% block swag_language_pack_settings_content_tabs %}\n            <sw-tabs\n                v-if="!isLoading"\n                class="swag-language-pack-settings__tabs"\n                position-identifier="swag-language-pack-settings__tabs"\n            >\n\n                {% block swag_language_pack_settings_content_tabs_administration %}\n                <sw-tabs-item\n                    class="swag-language-pack-settings__tab-administration"\n                    :route="{ name: \'swag.language.pack.index.administration\' }"\n                    :disabled="!acl.can(\'language.viewer\')"\n                >\n                    {{ $tc(\'swag-language-pack.settings.card.administration.tabTitle\') }}\n                </sw-tabs-item>\n                {% endblock %}\n\n                {% block swag_language_pack_settings_content_tabs_sales_channel %}\n                <sw-tabs-item\n                    class="swag-language-pack-settings__tab-sales-channel"\n                    :route="{ name: \'swag.language.pack.index.salesChannel\' }"\n                    :disabled="!acl.can(\'language.viewer\')"\n                >\n                    {{ $tc(\'swag-language-pack.settings.card.salesChannel.tabTitle\') }}\n                </sw-tabs-item>\n                {% endblock %}\n            </sw-tabs>\n            {% endblock %}\n\n            {% block swag_language_pack_settings_content_router_view %}\n            <router-view\n                v-if="!isLoading"\n                :is-loading="isLoading"\n                :pack-languages="packLanguages"\n            />\n            {% endblock %}\n\n            {% block swag_language_pack_settings_content_loader %}\n            <sw-loader v-if="isLoading"/>\n            {% endblock %}\n\n            {% block swag_language_pack_settings_content_verify_user_modal %}\n            <sw-verify-user-modal\n                v-if="confirmPasswordModal"\n                @verified="savePackLanguages"\n                @close="onCloseConfirmPasswordModal"\n            />\n            {% endblock %}\n\n        </sw-card-view>\n        {% endblock %}\n\n    </template>\n    {% endblock %}\n\n</sw-page>\n{% endblock %}\n',inject:["repositoryFactory","userService","acl"],mixins:["notification"],data(){return{isLoading:!1,isSaveSuccessful:!1,hasChanges:!1,packLanguages:[],fallbackLocaleId:null,confirmPasswordModal:!1}},metaInfo(){return{title:this.$createTitle()}},computed:{packLanguageRepository(){return this.repositoryFactory.create("swag_language_pack_language")},languageRepository(){return this.repositoryFactory.create("language")},userRepository(){return this.repositoryFactory.create("user")},packLanguageCriteria(){return new u().addSorting(u.sort("language.name","ASC")).addAssociation("language.salesChannels.domains").addAssociation("language.locale")}},created(){this.createdComponent()},beforeRouteLeave(e,a,n){n(),this.hasChanges&&window.location.reload()},methods:{createdComponent(){this.loadPackLanguages()},loadPackLanguages(){return this.isLoading=!0,this.packLanguageRepository.search(this.packLanguageCriteria).then(e=>{this.packLanguages=e}).finally(()=>{this.isLoading=!1})},onSave(){this.confirmPasswordModal=!0},onSaveFinish(){this.isSaveSuccessful=!1},onCloseConfirmPasswordModal(){this.confirmPasswordModal=!1},savePackLanguages(){return this.isLoading=!0,this.validateStates(this.packLanguages).then(()=>this.packLanguageRepository.saveAll(this.packLanguages).then(()=>(this.hasChanges=!0,this.resetInvalidUserLanguages())).catch(()=>{this.createNotificationError({message:this.$tc("swag-language-pack.settings.card.messageSaveError")})})).catch(e=>{let a=e.map(e=>e.language.name),n=`<b>${a.join(", ")}</b>`;this.createNotificationError({message:this.$tc("swag-language-pack.settings.card.messageSalesChannelActiveError",0,{languages:n}),autoClose:!1})}).finally(()=>{this.isLoading=!1,this.isSaveSuccessful=!0,this.loadPackLanguages()})},validateStates(e){return new Promise((a,n)=>{let t=e.filter(e=>!(e.salesChannelActive||e.language.salesChannels.length<=0));t.length>0&&n(t),a()})},async resetInvalidUserLanguages(){let e=await this.fetchInvalidLocaleIds();if(!e||e.length<=0)return Promise.resolve();let a=await this.userService.getUser(),n=new u().addFilter(u.equalsAny("localeId",e)),t=await this.userRepository.search(n);return t=t.reduce((e,n)=>(n.localeId=this.fallbackLocaleId,a.data.id===n.id&&Shopware.Service("localeHelper").setLocaleWithId(n.localeId),e.push(n),e),[]),this.userRepository.saveAll(t)},async fetchInvalidLocaleIds(){let e=new u().addFilter(u.equals("extensions.swagLanguagePackLanguage.administrationActive",!1)),a=await this.languageRepository.search(e),n=new u().setIds([c.systemLanguageId]),t=await this.languageRepository.search(n);return this.fallbackLocaleId=t.first().localeId,a.map(e=>e.localeId)}}}),n(427);let{Component:d}=Shopware;d.register("swag-language-pack-settings-base",{template:'{% block swag_language_pack_settings_base %}\n{% block swag_language_pack_settings_base_card_view_language_selection %}\n    <sw-card\n        class="swag-language-pack-settings-base"\n        position-identifier="swag-language-pack-settings-base"\n        :title="$tc(`swag-language-pack.settings.card.${settingsType}.title`)"\n        :disabled="isLoading"\n    >\n\n        {% block swag_language_pack_settings_base_card_view_card_loader %}\n        <sw-loader v-if="isLoading"/>\n        {% endblock %}\n\n        {% block swag_language_pack_settings_base_card_view_description%}\n        <div\n            v-html="description"\n            class="swag-language-pack-settings-base__description"\n        />\n        {% endblock %}\n\n        {% block swag_language_pack_settings_base_card_view_language_selection_languages %}\n        <template\n            v-for="packLanguage in packLanguages"\n            :key="packLanguage.id"\n        >\n            {% block swag_language_pack_settings_base_card_view_language_selection_language %}\n            <swag-pack-language-entry\n                class="swag-language-pack-settings-base__entry"\n                v-model:value="packLanguage"\n                :field="`${settingsType}Active`"\n                :label="packLanguage.language.name"\n                :description="packLanguage.language.locale?.code"\n                :flag-locale="packLanguage.language.locale?.code"\n                :disabled="!acl.can(\'swag_language_pack_language:update\')"\n            />\n            {% endblock %}\n        </template>\n        {% endblock %}\n\n    </sw-card>\n{% endblock %}\n{% endblock %}\n',inject:["acl"],props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0},settingsType:{type:String,required:!0,validator(e){return["administration","salesChannel"].includes(e)}}},computed:{description(){let e=`<a href="#/sw/profile/index">
                ${this.$tc("swag-language-pack.settings.card.administration.descriptionTargetLinkText")}
            </a>`;return this.$tc(`swag-language-pack.settings.card.${this.settingsType}.description`,0,{userInterfaceLanguageLink:e})}}});let{Component:p}=Shopware;p.register("swag-language-pack-settings-administration",{template:'{% block swag_language_pack_settings_administration %}\n    <swag-language-pack-settings-base\n        :isLoading="isLoading"\n        :packLanguages="packLanguages"\n        settingsType="administration">\n    </swag-language-pack-settings-base>\n{% endblock %}',props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0}}});let{Component:w}=Shopware;w.register("swag-language-pack-settings-sales-channel",{template:'{% block swag_language_pack_settings_sales_channel %}\n    <swag-language-pack-settings-base\n        :isLoading="isLoading"\n        :packLanguages="packLanguages"\n        settingsType="salesChannel">\n    </swag-language-pack-settings-base>\n{% endblock %}\n',props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0}}});let{Module:k}=Shopware;k.register("swag-language-pack",{type:"plugin",name:"SwagLanguagePack",title:"swag-language-pack.general.mainMenuItemGeneral",description:"swag-language-pack.general.descriptionTextModule",version:"1.0.0",targetVersion:"1.0.0",color:"#9AA8B5",icon:"regular-cog",routes:{index:{component:"swag-language-pack-settings",path:"index",redirect:{name:"swag.language.pack.index.administration"},meta:{parentPath:"sw.settings.index",privilege:"language.viewer"},children:{administration:{component:"swag-language-pack-settings-administration",path:"administration",meta:{parentPath:"sw.settings.index",privilege:"language.viewer"}},salesChannel:{component:"swag-language-pack-settings-sales-channel",path:"sales-channel",meta:{parentPath:"sw.settings.index",privilege:"language.viewer"}}}}},settingsItem:{group:"plugins",to:"swag.language.pack.index",icon:"regular-language",backgroundEnabled:!0,privilege:"language.viewer"}}),n(673),n(876)}()})();