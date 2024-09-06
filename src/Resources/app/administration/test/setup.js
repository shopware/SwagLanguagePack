import VirtualCallStackPlugin from '@administration/app/plugin/virtual-call-stack.plugin'
import { config } from '@vue/test-utils';

config.global.plugins = [
    VirtualCallStackPlugin,
];
