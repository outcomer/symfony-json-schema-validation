import { defineConfig } from 'vitepress'

const REPO_NAME = 'symfony-json-schema-validation'

export default defineConfig({
  title: 'JSON Schema Validation',
  description: 'Single Source of Truth for Symfony API contracts with JSON Schema validation and automatic OpenAPI documentation',
  base: `/${REPO_NAME}/`,

  sitemap: {
    hostname: `https://outcomer.github.io/${REPO_NAME}/`
  },

  ignoreDeadLinks: false,
  lastUpdated: true,

  themeConfig: {
    logo: '/logo.svg',

    nav: [
      { text: 'Guide', link: '/guide/how-it-works' },
      { text: 'Examples', link: '/guide/examples' },
      { text: 'API', link: '/guide/api' },
      { text: 'GitHub', link: `https://github.com/outcomer/${REPO_NAME}` }
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
          { text: 'Real-world Examples', link: '/guide/examples' }
        ]
      },
      {
        text: 'API Reference',
        items: [
          { text: 'Complete API Docs', link: '/guide/api' }
        ]
      }
    ],

    socialLinks: [
      { icon: 'github', link: `https://github.com/outcomer/${REPO_NAME}` }
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
    ['link', { rel: 'icon', type: 'image/svg+xml', href: `/${REPO_NAME}/favicon.svg` }],
    ['meta', { name: 'google-site-verification', content: 'Jd6v0TBT246H7NMdiAivOkkemvW9_KGGJC3EKzZ7AtU' }],
    ['meta', { name: 'msvalidate.01', content: 'CDE6B128D984CC16F15D6CAA4C06DA97' }],

    // Facebook Open Graph
    ['meta', { property: 'og:url', content: `https://outcomer.github.io/${REPO_NAME}/` }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'JSON Schema Validation for Symfony API Contracts' }],
    ['meta', { property: 'og:description', content: 'Single Source of Truth for Symfony API contracts with JSON Schema validation and automatic OpenAPI documentation' }],
    ['meta', { property: 'og:image', content: `https://outcomer.github.io/${REPO_NAME}/og-image.png` }],

    // Twitter
    ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ['meta', { property: 'twitter:domain', content: 'outcomer.github.io' }],
    ['meta', { property: 'twitter:url', content: `https://outcomer.github.io/${REPO_NAME}/` }],
    ['meta', { name: 'twitter:title', content: 'JSON Schema Validation for Symfony API Contracts' }],
    ['meta', { name: 'twitter:description', content: 'Single Source of Truth for Symfony API contracts with JSON Schema validation and automatic OpenAPI documentation' }],
    ['meta', { name: 'twitter:image', content: `https://outcomer.github.io/${REPO_NAME}/og-image.png` }],

    // Microsoft Clarity tracking
    ['script', {}, `(function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i+"?ref=bwt";
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "vv6kxraikb");`]
  ]
})
