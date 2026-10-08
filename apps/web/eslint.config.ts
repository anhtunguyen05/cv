import { globalIgnores } from 'eslint/config'
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import pluginVue from 'eslint-plugin-vue'
import pluginOxlint from 'eslint-plugin-oxlint'
import skipFormatting from 'eslint-config-prettier/flat'

// To allow more languages other than `ts` in `.vue` files, uncomment the following lines:
// import { configureVueProject } from '@vue/eslint-config-typescript'
// configureVueProject({ scriptLangs: ['ts', 'tsx'] })
// More info at https://github.com/vuejs/eslint-config-typescript/#advanced-setup

export default defineConfigWithVueTs(
  {
    name: 'app/files-to-lint',
    files: ['**/*.{vue,ts,mts,tsx}'],
  },

  globalIgnores(['**/dist/**', '**/dist-ssr/**', '**/coverage/**']),

  ...pluginVue.configs['flat/essential'],
  vueTsConfigs.recommended,

  {
    rules: {
      'vue/multi-word-component-names': 'off',
    },
  },

  {
    name: 'shared/dependency-boundary',
    files: ['src/shared/**/*.{vue,ts,mts,tsx}'],
    rules: {
      'no-restricted-imports': [
        'error',
        {
          patterns: [
            { group: ['@/app/**'], message: 'shared must not depend on app.' },
            { group: ['@/pages/**'], message: 'shared must not depend on pages.' },
            { group: ['@/features/**'], message: 'shared must not depend on features.' },
          ],
        },
      ],
    },
  },

  {
    name: 'pages/controller-boundary',
    files: ['src/pages/**/*.{vue,ts,mts,tsx}'],
    rules: {
      'no-restricted-imports': [
        'error',
        {
          paths: [
            {
              name: '@tanstack/vue-query',
              message: 'Route pages must use feature controllers or feature query hooks.',
            },
          ],
          patterns: [
            {
              group: ['@/features/*/api/*.api'],
              message: 'Route pages must not call feature transport modules directly.',
            },
            {
              group: ['@/features/*/api/*.queries'],
              message: 'Route pages must render feature components instead of owning query orchestration.',
            },
          ],
        },
      ],
    },
  },

  ...pluginOxlint.buildFromOxlintConfigFile('.oxlintrc.json'),

  skipFormatting,
)
