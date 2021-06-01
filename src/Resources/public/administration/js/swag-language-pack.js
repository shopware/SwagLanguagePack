(this.webpackJsonp=this.webpackJsonp||[]).push([["swag-language-pack"],{"0/bW":function(e,a){Shopware.Service("privileges").addPrivilegeMappingEntry({category:"permissions",parent:"settings",key:"language",roles:{editor:{privileges:["swag_language_pack_language:update"]}}})},"24t7":function(e,a){var n=Shopware,t=n.Component,s=n.Defaults,i=Shopware.Data.Criteria;t.override("sw-language-switch",{computed:{languageCriteria:function(){return this.$super("languageCriteria").addFilter(i.multi("OR",[i.equals("extensions.swagLanguagePackLanguage.id",null),i.equals("extensions.swagLanguagePackLanguage.salesChannelActive",!0),i.equals("id",s.systemLanguageId)]))}}})},"4iBN":function(e,a){e.exports='{% block swag_language_pack_settings_sales_channel %}\n    <swag-language-pack-settings-base\n            :isLoading="isLoading"\n            :packLanguages="packLanguages"\n            settingsType="salesChannel">\n    </swag-language-pack-settings-base>\n{% endblock %}'},"7vF2":function(e,a){Shopware.Component.override("sw-sales-channel-detail",{methods:{getLoadSalesChannelCriteria:function(){return this.$super("getLoadSalesChannelCriteria").addAssociation("languages.locale")}}})},"8WEj":function(e,a){var n=Shopware,t=n.Component,s=n.Defaults,i=Shopware.Data.Criteria;t.override("sw-first-run-wizard-welcome",{methods:{getLanguageCriteria:function(){return this.$super("getLanguageCriteria").addAssociation("swagLanguagePackLanguage").addSorting(i.sort("name","ASC")).addFilter(i.multi("OR",[i.equals("extensions.swagLanguagePackLanguage.id",null),i.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),i.equals("id",s.systemLanguageId)])).setLimit(500)}}})},Cn1C:function(e,a,n){},DJPE:function(e,a){var n,t=Shopware.Data.Criteria,s=Shopware.Utils.debug.warn,i=Shopware.Service("repositoryFactory").create("swag_language_pack_language"),l=(new t).addFilter(t.equals("administrationActive",!0)).addAggregation(t.terms("locales","language.locale.code",null,null,null)),g=Shopware.Plugin.addBootPromise();i.search(l,Shopware.Context.api).then((function(e){e.aggregations.locales.buckets.forEach((function(e){var a=e.key;!1===Shopware.Locale.getByName(a)&&(n=a,Shopware.Locale.register(a,{}))})),g()})).catch((function(){var e='Unable to register "packLanguages".';void 0!==n&&(e+=" Problems occurred while installing language: ".concat(n)),s("SwagLanguagePack",e),g()}))},NC68:function(e,a,n){},"Ohi/":function(e,a){e.exports='{% block sw_sales_channel_defaults_select %}\n    {% block swag_sales_channel_defaults_select_filterable %}\n        <sw-container class="sw-sales-channel-defaults-select" gap="0px 30px" columns="1fr 200px">\n\n            {% block swag_sales_channel_defaults_select_filterable_content %}\n                <template v-if="salesChannel">\n\n                    {% block swag_sales_channel_defaults_select_filterable_content_languages %}\n                        <sw-entity-multi-select\n                                :disabled="disabled"\n                                :class="multiSelectClass"\n                                :criteria="criteria"\n                                :label="propertyLabel"\n                                :helpText="helpText"\n                                :entityCollection="propertyCollection"\n                                @change="updateCollection">\n                        </sw-entity-multi-select>\n                    {% endblock %}\n\n                    {% block swag_sales_channel_defaults_select_filterable_content_default_language %}\n                        <sw-entity-single-select\n                                :disabled="disabled"\n                                :class="singleSelectClass"\n                                :criteria="criteria"\n                                :label="defaultPropertyLabel"\n                                :helpText="helpText"\n                                :entity="propertyEntityName"\n                                :value="defaultId"\n                                :error="defaultsValueError"\n                                required\n                                @change="updateDefault">\n                        </sw-entity-single-select>\n                    {% endblock %}\n\n                </template>\n            {% endblock %}\n\n        </sw-container>\n    {% endblock %}\n{% endblock %}'},RzzX:function(e,a,n){var t=n("j36l");"string"==typeof t&&(t=[[e.i,t,""]]),t.locals&&(e.exports=t.locals);(0,n("SZ7m").default)("add2e17c",t,!0,{})},"SLm+":function(e,a,n){"use strict";n.r(a);var t;n("24t7"),n("DJPE");(t=n("m7in")).keys().reduce((function(e,a){var n=a.split("/")[1].split(".")[0],s={name:"".concat("swag-language-pack-flag-").concat(n),functional:!0,render:function(e,n){var s=n.data;return e("span",{class:[s.staticClass,s.class],style:s.style,attrs:s.attrs,on:s.on,domProps:{innerHTML:t(a).default}})}};return e.push(s),e}),[]).forEach((function(e){Shopware.Component.register(e.name,e)}));n("8WEj"),n("nvql");var s=n("Ohi/"),i=n.n(s),l=Shopware.Component,g=Shopware.Data.Criteria;l.extend("swag-sales-channel-defaults-select-filterable","sw-sales-channel-defaults-select",{template:i.a,props:{criteria:{type:Object,required:!1,default:function(){return new g(1,25)}}}});n("7vF2");var r=n("i5Xd"),o=n.n(r),c=Shopware,u=c.Component,d=c.Defaults,p=Shopware.Data.Criteria;u.override("sw-sales-channel-detail-base",{template:o.a,computed:{languageCriteria:function(){return(new p).addAssociation("swagLanguagePackLanguage").addSorting(p.sort("name","ASC")).addFilter(p.multi("OR",[p.equals("extensions.swagLanguagePackLanguage.id",null),p.equals("extensions.swagLanguagePackLanguage.salesChannelActive",!0),p.equals("id",d.systemLanguageId)]))}}});var w=n("j4gI"),_=n.n(w),k=Shopware.Component,f=Shopware.Data.Criteria;k.override("sw-settings-language-list",{template:_.a,data:function(){return{packLanguageLanguageIds:[]}},computed:{packLanguageCriteria:function(){return new f(1,50).addFilter(f.not("and",[f.equals("swagLanguagePackLanguage.id",null)]))}},created:function(){this.createdComponent()},methods:{createdComponent:function(){this.getPackLanguageLanguageIds()},getPackLanguageLanguageIds:function(){var e=this;this.languageRepository.searchIds(this.packLanguageCriteria,Shopware.Context.api).then((function(a){e.packLanguageLanguageIds=a.data}))},isPackLanguage:function(e){return this.packLanguageLanguageIds.includes(e)}}});n("0/bW");var m=n("uPme"),h=n.n(m);n("pPNE");Shopware.Component.register("swag-language-pack-flag",{template:h.a,props:{locale:{type:String,required:!1,default:""}},computed:{flagComponentName:function(){var e=this.locale.split("-")[1].toLowerCase();return"".concat("swag-language-pack-flag-").concat(e)}}});var v=n("qr73"),b=n.n(v);n("m9Nv");Shopware.Component.register("swag-pack-language-entry",{template:b.a,inject:["acl"],props:{value:{type:Object,required:!0},field:{type:String,required:!0},label:{type:String,required:!0},disabled:{type:Boolean,required:!0,default:!1},description:{type:String,required:!1,default:""},flagLocale:{type:String,required:!1,default:""}}});var L=n("lPQM"),y=n.n(L);Shopware.Component.register("swag-language-pack-settings-icon",{template:y.a});var C=n("grAk"),S=n.n(C);function x(e,a,n,t,s,i,l){try{var g=e[i](l),r=g.value}catch(e){return void n(e)}g.done?a(r):Promise.resolve(r).then(t,s)}function P(e){return function(){var a=this,n=arguments;return new Promise((function(t,s){var i=e.apply(a,n);function l(e){x(i,t,s,l,g,"next",e)}function g(e){x(i,t,s,l,g,"throw",e)}l(void 0)}))}}var q=Shopware,I=q.Component,A=q.Defaults,D=Shopware.Data.Criteria;I.register("swag-language-pack-settings",{template:S.a,inject:["repositoryFactory","userService","acl"],mixins:["notification"],data:function(){return{isLoading:!1,isSaveSuccessful:!1,hasChanges:!1,packLanguages:[],fallbackLocaleId:null,confirmPasswordModal:!1}},metaInfo:function(){return{title:this.$createTitle()}},computed:{packLanguageRepository:function(){return this.repositoryFactory.create("swag_language_pack_language")},languageRepository:function(){return this.repositoryFactory.create("language")},userRepository:function(){return this.repositoryFactory.create("user")},packLanguageCriteria:function(){return(new D).addSorting(D.sort("language.name","ASC")).addAssociation("language.salesChannels.domains")}},created:function(){this.createdComponent()},beforeRouteLeave:function(e,a,n){n(),this.hasChanges&&window.location.reload()},methods:{createdComponent:function(){this.loadPackLanguages()},loadPackLanguages:function(){var e=this;return this.isLoading=!0,this.packLanguageRepository.search(this.packLanguageCriteria,Shopware.Context.api).then((function(a){e.packLanguages=a})).finally((function(){e.isLoading=!1}))},onSave:function(){this.confirmPasswordModal=!0},onSaveFinish:function(){this.isSaveSuccessful=!1},onCloseConfirmPasswordModal:function(){this.confirmPasswordModal=!1},savePackLanguages:function(){var e=this;return this.isLoading=!0,this.validateStates(this.packLanguages).then((function(){return e.packLanguageRepository.saveAll(e.packLanguages,Shopware.Context.api).then((function(){return e.hasChanges=!0,e.resetInvalidUserLanguages()})).catch((function(){e.createNotificationError({message:e.$tc("swag-language-pack.settings.card.messageSaveError")})}))})).catch((function(a){var n=a.map((function(e){return e.language.name})),t="<b>".concat(n.join(", "),"</b>");e.createNotificationError({message:e.$tc("swag-language-pack.settings.card.messageSalesChannelActiveError",0,{languages:t}),autoClose:!1})})).finally((function(){e.isLoading=!1,e.isSaveSuccessful=!0,e.loadPackLanguages()}))},validateStates:function(e){return new Promise((function(a,n){var t=e.filter((function(e){return!(e.salesChannelActive||e.language.salesChannels.length<=0)}));t.length>0&&n(t),a()}))},resetInvalidUserLanguages:function(){var e=this;return P(regeneratorRuntime.mark((function a(){var n,t,s,i;return regeneratorRuntime.wrap((function(a){for(;;)switch(a.prev=a.next){case 0:return a.next=2,e.userService.getUser();case 2:return n=a.sent,a.next=5,e.fetchInvalidLocaleIds();case 5:return t=a.sent,s=new D,t&&t.length>0&&s.addFilter(D.equalsAny("localeId",t)),a.next=10,e.userRepository.search(s,Shopware.Context.api);case 10:return i=(i=a.sent).reduce((function(a,t){return t.localeId=e.fallbackLocaleId,n.data.id===t.id&&Shopware.Service("localeHelper").setLocaleWithId(t.localeId),a.push(t),a}),[]),a.abrupt("return",e.userRepository.saveAll(i,Shopware.Context.api));case 13:case"end":return a.stop()}}),a)})))()},fetchInvalidLocaleIds:function(){var e=this;return P(regeneratorRuntime.mark((function a(){var n,t,s,i;return regeneratorRuntime.wrap((function(a){for(;;)switch(a.prev=a.next){case 0:return n=(new D).addFilter(D.equals("extensions.swagLanguagePackLanguage.administrationActive",!1)),a.next=3,e.languageRepository.search(n,Shopware.Context.api);case 3:return t=a.sent,s=(new D).setIds([A.systemLanguageId]),a.next=7,e.languageRepository.search(s,Shopware.Context.api);case 7:return i=a.sent,e.fallbackLocaleId=i.first().localeId,a.abrupt("return",t.map((function(e){return e.localeId})));case 10:case"end":return a.stop()}}),a)})))()}}});var $=n("jFHS"),R=n.n($);n("RzzX");Shopware.Component.register("swag-language-pack-settings-base",{template:R.a,inject:["acl"],props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0},settingsType:{type:String,required:!0,validator:function(e){return["administration","salesChannel"].includes(e)}}},computed:{description:function(){var e='<a href="#/sw/profile/index">\n                '.concat(this.$tc("swag-language-pack.settings.card.administration.descriptionTargetLinkText"),"\n            </a>");return this.$tc("swag-language-pack.settings.card.".concat(this.settingsType,".description"),0,{userInterfaceLanguageLink:e})}}});var F=n("tRG2"),N=n.n(F);Shopware.Component.register("swag-language-pack-settings-administration",{template:N.a,props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0}}});var T=n("4iBN"),E=n.n(T);Shopware.Component.register("swag-language-pack-settings-sales-channel",{template:E.a,props:{isLoading:{type:Boolean,required:!0},packLanguages:{type:Array,required:!0}}}),Shopware.Module.register("swag-language-pack",{type:"plugin",name:"SwagLanguagePack",title:"swag-language-pack.general.mainMenuItemGeneral",description:"swag-language-pack.general.descriptionTextModule",version:"1.0.0",targetVersion:"1.0.0",color:"#9AA8B5",icon:"default-action-settings",routes:{index:{component:"swag-language-pack-settings",path:"index",redirect:{name:"swag.language.pack.index.administration"},meta:{parentPath:"sw.settings.index",privilege:"language.viewer"},children:{administration:{component:"swag-language-pack-settings-administration",path:"administration",meta:{parentPath:"sw.settings.index",privilege:"language.viewer"}},salesChannel:{component:"swag-language-pack-settings-sales-channel",path:"sales-channel",meta:{parentPath:"sw.settings.index",privilege:"language.viewer"}}}}},settingsItem:{group:"plugins",to:"swag.language.pack.index",iconComponent:"swag-language-pack-settings-icon",backgroundEnabled:!0,privilege:"language.viewer"}});n("nna2"),n("mN17")},grAk:function(e,a){e.exports='{% block swag_language_pack_settings %}\n    <sw-page>\n\n        {% block swag_language_pack_settings_header %}\n            <template #smart-bar-header>\n                <h2>\n                    {{ $tc(\'sw-settings.index.title\') }}\n                    <sw-icon name="small-arrow-medium-right" small></sw-icon>\n                    {{ $tc(\'swag-language-pack.settings.header\') }}\n                </h2>\n            </template>\n        {% endblock %}\n\n        {% block swag_language_pack_settings_actions %}\n            <template #smart-bar-actions>\n\n                {% block swag_language_pack_settings_actions_save %}\n                    <sw-button-process\n                            class="swag-language-pack-settings__save-action"\n                            variant="primary"\n                            :processSuccess="isSaveSuccessful"\n                            :disabled="!acl.can(\'swag_language_pack_language:update\')"\n                            @process-finish="onSaveFinish"\n                            @click="onSave">\n                        {{ $tc(\'global.default.save\') }}\n                    </sw-button-process>\n                {% endblock %}\n\n            </template>\n        {% endblock %}\n\n        {% block swag_language_pack_settings_content %}\n            <template #content>\n\n                {% block swag_language_pack_settings_content_card_view %}\n                    <sw-card-view>\n\n                        {% block swag_language_pack_settings_content_tabs %}\n                            <sw-tabs v-if="!isLoading" class="swag-language-pack-settings__tabs">\n\n                                {% block swag_language_pack_settings_content_tabs_administration %}\n                                    <sw-tabs-item\n                                            class="swag-language-pack-settings__tab-administration"\n                                            :route="{ name: \'swag.language.pack.index.administration\' }"\n                                            :disabled="!acl.can(\'language.viewer\')">\n                                        {{ $tc(\'swag-language-pack.settings.card.administration.tabTitle\') }}\n                                    </sw-tabs-item>\n                                {% endblock %}\n\n                                {% block swag_language_pack_settings_content_tabs_sales_channel %}\n                                    <sw-tabs-item\n                                            class="swag-language-pack-settings__tab-sales-channel"\n                                            :route="{ name: \'swag.language.pack.index.salesChannel\' }"\n                                            :disabled="!acl.can(\'language.viewer\')">\n                                        {{ $tc(\'swag-language-pack.settings.card.salesChannel.tabTitle\') }}\n                                    </sw-tabs-item>\n                                {% endblock %}\n                            </sw-tabs>\n                        {% endblock %}\n\n                        {% block swag_language_pack_settings_content_router_view %}\n                            <router-view\n                                    v-if="!isLoading"\n                                    :isLoading="isLoading"\n                                    :packLanguages="packLanguages">\n                            </router-view>\n                        {% endblock %}\n\n                        {% block swag_language_pack_settings_content_loader %}\n                            <sw-loader v-if="isLoading"></sw-loader>\n                        {% endblock %}\n\n                        {% block swag_language_pack_settings_content_verify_user_modal %}\n                            <sw-verify-user-modal\n                                    v-if="confirmPasswordModal"\n                                    @verified="savePackLanguages"\n                                    @close="onCloseConfirmPasswordModal">\n                            </sw-verify-user-modal>\n                        {% endblock %}\n\n                    </sw-card-view>\n                {% endblock %}\n\n            </template>\n        {% endblock %}\n\n    </sw-page>\n{% endblock %}\n'},i5Xd:function(e,a){e.exports='{% block sw_sales_channel_detail_base_general_input_languages %}\n    <swag-sales-channel-defaults-select-filterable\n            v-if="!isProductComparison"\n            :salesChannel="salesChannel"\n            :criteria="languageCriteria"\n            propertyName="languages"\n            defaultPropertyName="languageId"\n            propertyNameInDomain="languageId"\n            :propertyLabel="$tc(\'sw-sales-channel.detail.labelInputLanguages\')"\n            :defaultPropertyLabel="$tc(\'sw-sales-channel.detail.labelInputDefaultLanguage\')"\n            :disabled="!acl.can(\'sales_channel.editor\')">\n    </swag-sales-channel-defaults-select-filterable>\n{% endblock %}'},j36l:function(e,a,n){},j4gI:function(e,a){e.exports='{% block sw_settings_language_list_content_list_delete_action %}\n    <template #delete-action="{ item, showDelete }">\n\n        {% block sw_settings_language_list_content_list_delete_action_language_pack %}\n            <template v-if="isPackLanguage(item.id)">\n                <sw-context-menu-item\n                        v-tooltip.bottom="$tc(\'swag-language-pack.sw-settings-language-list.deleteLanguagePackTooltip\')"\n                        class="sw-settings-language-list__delete-action"\n                        variant="danger"\n                        disabled\n                        @click="showDelete(item.id)">\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </template>\n        {% endblock %}\n\n        {% block sw_settings_language_list_content_list_delete_action_language %}\n            <template v-else>\n                <sw-context-menu-item\n                        v-tooltip.bottom="tooltipDelete(item.id)"\n                        class="sw-settings-language-list__delete-action"\n                        variant="danger"\n                        :disabled="isDefault(item.id) || !allowDelete"\n                        @click="showDelete(item.id)">\n                    {{ $tc(\'global.default.delete\') }}\n                </sw-context-menu-item>\n            </template>\n        {% endblock %}\n\n    </template>\n{% endblock %}\n'},jFHS:function(e,a){e.exports='{% block swag_language_pack_settings_base %}\n    {% block swag_language_pack_settings_base_card_view_language_selection %}\n        <sw-card\n                class="swag-language-pack-settings-base"\n                :title="$tc(`swag-language-pack.settings.card.${settingsType}.title`)"\n                :disabled="isLoading">\n\n            {% block swag_language_pack_settings_base_card_view_card_loader %}\n                <sw-loader v-if="isLoading"></sw-loader>\n            {% endblock %}\n\n            {% block swag_language_pack_settings_base_card_view_description%}\n                <div v-html="description"\n                     class="swag-language-pack-settings-base__description">\n                </div>\n            {% endblock %}\n\n            {% block swag_language_pack_settings_base_card_view_language_selection_languages %}\n                {% block swag_language_pack_settings_base_card_view_language_selection_language %}\n                    <swag-pack-language-entry\n                            v-for="packLanguage in packLanguages"\n                            class="swag-language-pack-settings-base__entry"\n                            :key="packLanguage.id"\n                            :value="packLanguage"\n                            :field="`${settingsType}Active`"\n                            :label="packLanguage.language.name"\n                            :description="packLanguage.language.locale.code"\n                            :flagLocale="packLanguage.language.locale.code"\n                            :disabled="!acl.can(\'swag_language_pack_language:update\')">\n                    </swag-pack-language-entry>\n                {% endblock %}\n            {% endblock %}\n\n        </sw-card>\n    {% endblock %}\n{% endblock %}\n'},lPQM:function(e,a){e.exports='{% block swag_language_pack_settings_icon %}\n    <sw-icon name="default-location-flag"></sw-icon>\n{% endblock %}\n'},m7in:function(e,a,n){var t={};function s(e){var a=i(e);return n(a)}function i(e){if(!n.o(t,e)){var a=new Error("Cannot find module '"+e+"'");throw a.code="MODULE_NOT_FOUND",a}return t[e]}s.keys=function(){return Object.keys(t)},s.resolve=i,e.exports=s,s.id="m7in"},m9Nv:function(e,a,n){var t=n("Cn1C");"string"==typeof t&&(t=[[e.i,t,""]]),t.locals&&(e.exports=t.locals);(0,n("SZ7m").default)("47ae687a",t,!0,{})},mN17:function(e,a){var n=Shopware.Component,t=Shopware.Data.Criteria;n.override("sw-users-permissions-user-create",{computed:{languageCriteria:function(){return this.$super("languageCriteria").addFilter(t.multi("OR",[t.equals("extensions.swagLanguagePackLanguage.id",null),t.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),t.equals("id",Shopware.Defaults.systemLanguageId)]))}},methods:{onSave:function(){this.$super("onSave")}}})},nna2:function(e,a){var n=Shopware.Component,t=Shopware.Data.Criteria;n.override("sw-users-permissions-user-detail",{computed:{languageCriteria:function(){var e=this.$super("languageCriteria");return e.addFilter(t.multi("OR",[t.equals("extensions.swagLanguagePackLanguage.id",null),t.equals("extensions.swagLanguagePackLanguage.administrationActive",!0),t.equals("id",Shopware.Defaults.systemLanguageId)])),e}}})},nvql:function(e,a){var n=Shopware.Component,t=Shopware.Data.Criteria;n.override("sw-sales-channel-detail-domains",{computed:{snippetSetCriteria:function(){var e=this.salesChannel.languages.map((function(e){return e.locale.code}));return this.$super("snippetSetCriteria").addFilter(t.equalsAny("iso",e))}}})},pPNE:function(e,a,n){var t=n("NC68");"string"==typeof t&&(t=[[e.i,t,""]]),t.locals&&(e.exports=t.locals);(0,n("SZ7m").default)("1302760a",t,!0,{})},qr73:function(e,a){e.exports='{% block swag_pack_language_entry %}\n    <div class="swag-language-pack-entry">\n\n        {% block swag_pack_language_entry_flag %}\n            <swag-language-pack-flag class="swag-language-pack-entry__flag" :locale="flagLocale"></swag-language-pack-flag>\n        {% endblock %}\n\n        {% block swag_pack_language_entry_content %}\n            <div class="swag-language-pack-entry__content">\n\n                {% block swag_pack_language_entry_content_name %}\n                    <div class="swag-language-pack-entry__name">\n                        {{ label }}\n                    </div>\n                {% endblock %}\n\n                {% block swag_pack_language_entry_content_description %}\n                    <div class="swag-language-pack-entry__description">\n                        {{ description }}\n                    </div>\n                {% endblock %}\n\n            </div>\n        {% endblock %}\n\n        {% block swag_pack_language_entry_switch %}\n            <sw-switch-field\n                    v-model="value[field]"\n                    ref="packLanguageToggle"\n                    :disabled="disabled">\n            </sw-switch-field>\n        {% endblock %}\n    </div>\n{% endblock %}\n'},tRG2:function(e,a){e.exports='{% block swag_language_pack_settings_administration %}\n    <swag-language-pack-settings-base\n        :isLoading="isLoading"\n        :packLanguages="packLanguages"\n        settingsType="administration">\n    </swag-language-pack-settings-base>\n{% endblock %}'},uPme:function(e,a){e.exports='{% block swag_language_pack_flag %}\n    <component :is="flagComponentName" :class="locale"></component>\n{% endblock %}\n'}},[["SLm+","runtime","vendors-node"]]]);