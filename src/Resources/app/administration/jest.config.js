// For a detailed explanation regarding each configuration property, visit:
// https://jestjs.io/docs/en/configuration.html

const { resolve, join } = require('path');

const admin_path = process.env.ADMIN_PATH || resolve('../../../../../../../src/Administration/Resources/app/administration');

const { existsSync } = require('fs');

if (!existsSync(admin_path)) {
    throw new Error('The provided admin path is invalid!');
}

process.env.ADMIN_PATH = admin_path;

module.exports = {
    preset: '@shopware-ag/jest-preset-sw6-admin',
    globals: {
        adminPath: admin_path, // required, e.g. /www/sw6/platform/src/Administration/Resources/app/administration
    },

    moduleNameMapper: {
        '^test(.*)$': '<rootDir>/test$1',
        vue$: 'vue/dist/vue.common.dev.js',
    },
};
