import { mount } from '@vue/test-utils';
import '../src/module/swag-language-pack-settings/page/swag-language-pack-settings/index.js';

async function createWrapperWithVersion() {
    return mount(await Shopware.Component.build('swag-language-pack-settings'), {
        global: {
            mocks: {
                $tc: v => v,
            },
            provide: {
                repositoryFactory: {
                    create: () => ({
                        search: jest.fn(() => Promise.resolve([])),
                    }),
                },
                userService: v => v,
                acl: v => v
            },
            stubs: {
                'mt-icon': true,
                'mt-banner': true,
                'sw-button-process': true,
                'sw-tabs-item': true,
                'sw-tabs': true,
                'router-view': true,
                'mt-loader': true,
                'sw-verify-user-modal': true,
                'sw-card-view': true,
                'sw-page': true,
            },
        },
        props: {},
    });
}

describe('shopwareHasTranslationSystem', () => {
    it.each([
        ['6.7.3', true],
        ['6.8.0', true],
        ['6.7.9999999999-dev', true],
        ['6.6.10', false],
        ['6.6.99999-dev', false],
        ['6.7.2', false],
    ])('returns %s ⇒ %s', async (version, expected) => {
        Shopware.Context.app.config.version = version;
        const wrapper = await createWrapperWithVersion();
        expect(wrapper.vm.shopwareHasTranslationSystem).toBe(expected);
    });
});
