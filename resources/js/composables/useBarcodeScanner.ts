import { onMounted, onUnmounted, ref, type Ref } from 'vue'

export interface BarcodeScannerOptions {
  /** Max ms between keystrokes to qualify as scanner burst (default: 30) */
  threshold?: number
  /** Refs to inputs where scanner characters are allowed to pass through */
  allowlist?: Ref<HTMLElement | null>[]
  /** Called when a complete barcode is scanned (Enter received) */
  onScan: (code: string) => void
  /** Return true to silently discard the scan (no onScan call), e.g. when focus is on a payment input */
  discardWhen?: (activeElement: Element | null) => boolean
}

export function useBarcodeScanner(options: BarcodeScannerOptions) {
  const { threshold = 30, allowlist = [], onScan, discardWhen } = options

  const isScanning = ref(false)
  const lastCode = ref('')

  let buffer = ''
  let lastTime = 0
  let flushTimer: ReturnType<typeof setTimeout> | null = null

  function isAllowed(el: Element | null): boolean {
    return el !== null && allowlist.some((r) => r.value === el)
  }

  function clearBuffer() {
    buffer = ''
    isScanning.value = false
  }

  function onKeydown(e: KeyboardEvent) {
    if (e.ctrlKey || e.altKey || e.metaKey) return

    const now = Date.now()

    // ── Enter finalises a scan ──
    if (e.key === 'Enter') {
      if (buffer.length > 0) {
        e.preventDefault()
        if (discardWhen?.(document.activeElement)) {
          // Clear any leaked characters from the active input
          const el = document.activeElement
          if (el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement) {
            el.value = ''
            el.dispatchEvent(new Event('input', { bubbles: true }))
          }
          clearBuffer()
          if (flushTimer) {
            clearTimeout(flushTimer)
            flushTimer = null
          }
          return
        }
        const code = buffer
        clearBuffer()
        if (flushTimer) {
          clearTimeout(flushTimer)
          flushTimer = null
        }
        lastCode.value = code
        onScan(code)
      }
      return
    }

    // ── Only printable single characters ──
    if (e.key.length !== 1) return

    const gap = now - lastTime
    lastTime = now

    // Gap too large for a scanner burst → reset buffer
    if (gap > threshold && buffer.length > 0) {
      clearBuffer()
      if (flushTimer) {
        clearTimeout(flushTimer)
        flushTimer = null
      }
    }

    // Accumulate character
    buffer += e.key
    isScanning.value = buffer.length > 0

    // Extend the window waiting for the final Enter
    if (flushTimer) clearTimeout(flushTimer)
    flushTimer = setTimeout(clearBuffer, threshold * 15)

    // Prevent scanner keystrokes from reaching protected inputs
    if (gap <= threshold && !isAllowed(document.activeElement)) {
      e.preventDefault()
    }
  }

  onMounted(() => document.addEventListener('keydown', onKeydown))
  onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
    if (flushTimer) clearTimeout(flushTimer)
  })

  return { isScanning, lastCode }
}
