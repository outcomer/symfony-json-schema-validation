# Documentation Setup

This directory contains VitePress documentation for the Symfony JSON Schema Validation bundle.

## Quick Start

1. **Install dependencies:**
   ```bash
   npm install
   ```

2. **Start development server:**
   ```bash
   npm run docs:dev
   ```

3. **Build for production:**
   ```bash
   npm run docs:build
   ```

## Scripts

- `npm run docs:dev` - Start development server with hot reload
- `npm run docs:build` - Build static files for production
- `npm run docs:preview` - Preview production build locally

## Structure

```
docs/
├── .vitepress/
│   └── config.js          # VitePress configuration
├── guide/                 # User guide pages
│   ├── how-it-works.md
│   ├── installation.md
│   └── quick-start.md
├── api/                   # API reference
├── examples/              # Usage examples
└── index.md              # Homepage
```

## Deployment

The documentation can be deployed to:
- GitHub Pages
- Netlify
- Vercel
- Any static hosting service

Simply run `npm run docs:build` and deploy the `docs/.vitepress/dist/` directory.
