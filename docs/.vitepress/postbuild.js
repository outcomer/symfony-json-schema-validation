import { readFileSync, writeFileSync } from 'fs'
import { resolve, dirname } from 'path'
import { fileURLToPath } from 'url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const sitemapPath = resolve(__dirname, 'dist/sitemap.xml')

let sitemap = readFileSync(sitemapPath, 'utf-8')
sitemap = sitemap
  .replace(/\s+xmlns:news="[^"]+"/g, '')
  .replace(/\s+xmlns:xhtml="[^"]+"/g, '')
  .replace(/\s+xmlns:image="[^"]+"/g, '')
  .replace(/\s+xmlns:video="[^"]+"/g, '')
writeFileSync(sitemapPath, sitemap)
