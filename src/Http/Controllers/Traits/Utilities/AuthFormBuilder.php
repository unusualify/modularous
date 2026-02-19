<?php

declare(strict_types=1);

namespace Unusualify\Modularity\Http\Controllers\Traits\Utilities;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

/**
 * Provides reusable methods for building auth form view data.
 * Reduces duplication across Login, Register, ForgotPassword, and ResetPassword controllers.
 */
trait AuthFormBuilder
{
    /**
     * Returns common banner attributes for auth pages.
     */
    protected function authBannerAttributes(): array
    {
        return [
            'bannerDescription' => ___('authentication.banner-description'),
            'bannerSubDescription' => Lang::has('authentication.banner-sub-description')
                ? ___('authentication.banner-sub-description')
                : null,
            'redirectButtonText' => ___('authentication.redirect-button-text'),
            'redirectUrl' => Route::has(modularityConfig('auth_guest_route'))
                ? route(modularityConfig('auth_guest_route'))
                : null,
        ];
    }

    /**
     * Returns form title structure for auth pages.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function authFormTitle(string $text, array $overrides = []): array
    {
        return array_merge([
            'text' => $text,
            'tag' => 'h1',
            'color' => 'primary',
            'type' => 'h5',
            'weight' => 'bold',
            'transform' => 'uppercase',
            'align' => 'center',
            'justify' => 'center',
            'class' => 'justify-md-center',
        ], $overrides);
    }

    /**
     * Returns base form attributes for auth forms.
     *
     * @param  string|array  $formDraft  Form draft name or array schema
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function authFormBaseAttributes(
        string|array $formDraft,
        string $actionUrl,
        string $buttonText,
        array $overrides = []
    ): array {
        $schema = is_array($formDraft)
            ? $this->createFormSchema($formDraft)
            : $this->createFormSchema(getFormDraft($formDraft));

        return array_merge([
            'schema' => $schema,
            'actionUrl' => $actionUrl,
            'buttonText' => $buttonText,
            'formClass' => 'py-6',
            'no-default-form-padding' => true,
            'hasSubmit' => true,
            'noSchemaUpdatingProgressBar' => true,
        ], $overrides);
    }

    /**
     * Returns OAuth Google button slot element.
     */
    protected function oauthGoogleButtonSlot(string $type = 'sign-in'): array
    {
        $translationKey = $type === 'sign-up'
            ? 'authentication.sign-up-oauth'
            : 'authentication.sign-in-oauth';

        return [
            'tag' => 'v-btn',
            'elements' => ___($translationKey, ['provider' => 'Google']),
            'attributes' => [
                'variant' => 'outlined',
                'href' => route('admin.login.provider', ['provider' => 'google']),
                'class' => 'mt-5 mb-2 custom-auth-button',
                'color' => 'grey-lighten-1',
                'density' => 'default',
            ],
            'slots' => [
                'prepend' => [
                    'tag' => 'ue-svg-icon',
                    'attributes' => [
                        'symbol' => 'google',
                        'width' => '16',
                        'height' => '16',
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns create account button slot element.
     */
    protected function createAccountButtonSlot(): array
    {
        $registerRoute = modularityConfig('email_verified_register')
            ? Route::hasAdmin('register.email_form')
            : Route::hasAdmin('register.form');

        return [
            'tag' => 'v-btn',
            'elements' => ___('authentication.create-an-account'),
            'attributes' => [
                'variant' => 'outlined',
                'href' => route($registerRoute),
                'class' => 'my-2 custom-auth-button',
                'color' => 'grey-lighten-1',
                'density' => 'default',
            ],
        ];
    }

    /**
     * Returns form option slot (e.g. forgot password, have account link).
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function authFormOptionSlot(string $text, string $href, array $attributes = []): array
    {
        return [
            'tag' => 'v-btn',
            'elements' => $text,
            'attributes' => array_merge([
                'variant' => 'plain',
                'href' => $href,
                'class' => '',
                'color' => 'grey-lighten-1',
                'density' => 'default',
            ], $attributes),
        ];
    }

    /**
     * Returns bottom slots wrapper with given elements.
     *
     * @param  array<int, array>  $elements
     * @param  array<string, mixed>  $sheetAttributes
     */
    protected function authBottomSlots(array $elements, array $sheetAttributes = []): array
    {
        return [
            'tag' => 'v-sheet',
            'attributes' => array_merge([
                'class' => 'd-flex pb-5 justify-end flex-column w-100 text-black',
            ], $sheetAttributes),
            'elements' => $elements,
        ];
    }

    /**
     * Returns form slots wrapper for bottom buttons (e.g. sign-in, reset-password).
     *
     * @param  array<int, array>  $elements
     */
    protected function authFormBottomSlots(array $elements): array
    {
        return [
            'bottom' => [
                'tag' => 'v-sheet',
                'attributes' => [
                    'class' => 'd-flex pb-5 justify-space-between w-100 text-black my-5',
                ],
                'elements' => $elements,
            ],
        ];
    }

    /**
     * Returns "have an account" link slot for register forms.
     */
    protected function haveAccountOptionSlot(): array
    {
        return [
            'options' => [
                'tag' => 'v-btn',
                'elements' => __('authentication.have-an-account'),
                'attributes' => [
                    'variant' => 'text',
                    'href' => route(Route::hasAdmin('login.form')),
                    'class' => 'd-flex flex-1-0 flex-md-grow-0',
                    'color' => 'grey-lighten-1',
                    'density' => 'default',
                ],
            ],
        ];
    }

    /**
     * Returns "restart" link slot for complete register form.
     */
    protected function restartOptionSlot(): array
    {
        return [
            'options' => [
                'tag' => 'v-btn',
                'elements' => __('Restart'),
                'attributes' => [
                    'variant' => 'text',
                    'href' => route(Route::hasAdmin('register.email_form')),
                    'class' => 'd-flex flex-1-0 flex-md-grow-0',
                    'color' => 'grey-lighten-1',
                    'density' => 'default',
                ],
            ],
        ];
    }

    /**
     * Returns "resend" link slot for password reset form.
     */
    protected function resendOptionSlot(): array
    {
        return [
            'options' => [
                'tag' => 'v-btn',
                'elements' => __('Resend'),
                'attributes' => [
                    'variant' => 'plain',
                    'href' => route('admin.password.reset.link'),
                    'class' => '',
                    'color' => 'grey-lighten-1',
                    'density' => 'default',
                ],
            ],
        ];
    }
}
