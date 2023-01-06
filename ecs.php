<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $services = $ecsConfig->services();

    $services->set(HeaderCommentFixer::class)->call('configure', [
        [
            'header' => '(c) shopware AG <info@shopware.com>
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.',
            'separate' => 'bottom',
            'location' => 'after_declare_strict',
            'comment_type' => 'comment'
        ]
    ]);
    $services->set(NativeFunctionInvocationFixer::class)->call('configure', [['include' => [NativeFunctionInvocationFixer::SET_ALL], 'scope' => 'namespaced',]]);
    $services->set(MbStrFunctionsFixer::class);

    $parameters = $ecsConfig->parameters();
    $parameters->set('cache_directory', __DIR__ . '/var/cache/cs_fixer');
    $parameters->set('cache_namespace', 'SwagLanguagePack');
    $parameters->set('paths', [__DIR__ . '/src', __DIR__ . '/tests']);
};
