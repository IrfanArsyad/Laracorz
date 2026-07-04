import js from '@eslint/js';
import tseslint from 'typescript-eslint';
import vue from 'eslint-plugin-vue';
import vueParser from 'vue-eslint-parser';

export default [
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    {
        files: ['**/*.{ts,vue}'],
        languageOptions: {
            parser: vueParser,
            parserOptions: {
                parser: tseslint.parser,
                ecmaVersion: 2024,
                sourceType: 'module',
                extraFileExtensions: ['.vue'],
            },
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                localStorage: 'readonly',
                sessionStorage: 'readonly',
                navigator: 'readonly',
                route: 'readonly',
                axios: 'readonly',
                FormData: 'readonly',
                File: 'readonly',
                Blob: 'readonly',
                URL: 'readonly',
                setTimeout: 'readonly',
                clearTimeout: 'readonly',
                setInterval: 'readonly',
                clearInterval: 'readonly',
                fetch: 'readonly',
                CustomEvent: 'readonly',
                Event: 'readonly',
                HTMLElement: 'readonly',
                HTMLInputElement: 'readonly',
                HTMLButtonElement: 'readonly',
                HTMLDivElement: 'readonly',
                HTMLMetaElement: 'readonly',
                HTMLTextAreaElement: 'readonly',
                MouseEvent: 'readonly',
                KeyboardEvent: 'readonly',
                FocusEvent: 'readonly',
                DragEvent: 'readonly',
                Node: 'readonly',
                FileList: 'readonly',
                FileReader: 'readonly',
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            'vue/no-multiple-template-root': 'off',
            'vue/attribute-hyphenation': 'off',
            'vue/v-on-event-hyphenation': 'off',
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
        },
    },
    {
        ignores: ['vendor/**', 'node_modules/**', 'public/**', 'storage/**', 'bootstrap/cache/**'],
    },
];
