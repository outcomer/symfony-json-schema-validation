import DefaultTheme from 'vitepress/theme'
import mediumZoom from 'medium-zoom'
import { h, onMounted, watch, nextTick } from 'vue'
import { useRoute } from 'vitepress'
import './custom.css'

function initImageZoom() {
  mediumZoom('.main img', {
    background: 'var(--vp-c-bg)',
    margin: 24
  })
}

function initMermaidZoom() {
  document.querySelectorAll('.main .mermaid svg:not([data-zoom-bound])').forEach((svg) => {
    svg.setAttribute('data-zoom-bound', '')
    svg.style.cursor = 'zoom-in'
    svg.addEventListener('click', () => openMermaidDialog(svg))
  })
}

function openMermaidDialog(svg) {
  const dialog = document.createElement('dialog')
  dialog.className = 'mermaid-zoom-dialog'
  dialog.innerHTML = svg.outerHTML
  dialog.addEventListener('click', () => dialog.close())
  dialog.addEventListener('close', () => dialog.remove())
  document.body.appendChild(dialog)
  dialog.showModal()
}

export default {
  extends: DefaultTheme,
  Layout() {
    return h(DefaultTheme.Layout, null, {
      'nav-bar-content-after': () =>
        h('iframe', {
          src: 'https://github.com/sponsors/outcomer/button',
          title: 'Sponsor outcomer',
          height: '32',
          width: '114',
          style: 'border: 0; border-radius: 6px; margin-left: 12px;'
        })
    })
  },
  setup() {
    const route = useRoute()
    const initZoom = () => {
      initImageZoom()
      initMermaidZoom()
    }
    onMounted(() => {
      initZoom()
      new MutationObserver(() => initMermaidZoom()).observe(document.querySelector('.main') ?? document.body, {
        childList: true,
        subtree: true
      })
    })
    watch(
      () => route.path,
      () => nextTick(() => initZoom())
    )
  }
}
