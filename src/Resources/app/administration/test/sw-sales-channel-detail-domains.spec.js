import { mount } from '@vue/test-utils';

import swSalesChannelDetailDomains from '@administration/module/sw-sales-channel/component/sw-sales-channel-detail-domains/index.js';
import  './../src/module/sw-sales-channel-detail/component/sw-sales-channel-detail-domains/index.js';

Shopware.Component.register('sw-sales-channel-detail-domains', swSalesChannelDetailDomains);

async function createWrapper(salesChannel) {
    return mount(await Shopware.Component.build('sw-sales-channel-detail-domains'), {
        global: {
            mocks: {
                $tc: v => v,
            },
            provide: {
                repositoryFactory: {
                    create: () => ({})
                },
            },
            stubs: {
                'sw-button': true,
                'sw-card': true,
            },
        },
        props: {
            salesChannel
        },
    });
}

describe('sw-sales-channel-detail-domains', () => {
    it('should filter snippetSets by locale code if it exist', async () => {
        const wrapper = await createWrapper({
            languages: [
                {
                    locale: {
                        name: 'Deutsch',
                        code: 'de-DE'
                    }
                },
                {
                    locale: {
                        name: 'English',
                        code: 'en-GB'
                    }
                },
                {
                    locale: {
                        name: 'American English',
                    }
                }
            ],
        });

        expect(wrapper.vm.snippetSetCriteria.filters).toEqual([{
            field: 'iso',
            type: 'equalsAny',
            value: 'de-DE|en-GB'
        }]);
    });

    it('should early return if no languages with langauge code are present', async () => {
        const wrapper = await createWrapper({
            languages: [
                {
                    locale: {
                        name: 'American English',
                    }
                }
            ]
        });

        expect(wrapper.vm.snippetSetCriteria.filters).toEqual([]);
    });
});
