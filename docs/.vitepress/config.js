import { defineConfig } from 'vitepress'

const REPO_NAME = 'symfony-json-schema-validation'

export default defineConfig({
  title: 'JSON Schema Validation',
  description: 'Single Source of Truth for Symfony API contracts with JSON Schema validation and automatic OpenAPI documentation',
  base: `/${REPO_NAME}/`,

  ignoreDeadLinks: false,

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Guide', link: '/guide/how-it-works' },
      { text: 'Examples', link: '/examples/' },
      { text: 'API', link: '/api/' },
      { text: 'GitHub', link: 'https://github.com/outcomer/symfony-json-schema-validation' }
    ],

    sidebar: [
      {
        text: 'Introduction',
        items: [
          { text: 'How It Works', link: '/guide/how-it-works' },
          { text: 'Installation', link: '/guide/installation' },
          { text: 'Quick Start', link: '/guide/quick-start' }
        ]
      },
      {
        text: 'Core Concepts',
        items: [
          { text: 'Schema Basics', link: '/guide/schema-basics' },
          { text: 'Configuration', link: '/guide/configuration' },
          { text: 'DTO Injection', link: '/guide/dto-injection' },
          { text: 'OpenAPI Integration', link: '/guide/openapi-integration' }
        ]
      },
      {
        text: 'Examples',
        items: [
          { text: 'Real-world Examples', link: '/examples/' }
        ]
      },
      {
        text: 'API Reference',
        items: [
          { text: 'Complete API Docs', link: '/api/' }
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/outcomer/symfony-json-schema-validation' }
    ],

    footer: {
      message: 'Released under the MIT License. Built with <a href="https://github.com/opis/json-schema" target="_blank">Opis JSON Schema</a>.',
      copyright: 'Copyright © 2026 Outcomer'
    },

    search: {
      provider: 'local'
    }
  },

  head: [
    ['link', { rel: 'icon', type: 'image/svg+xml', href: '/symfony-json-schema-validation/favicon.svg' }]
  ]
})
