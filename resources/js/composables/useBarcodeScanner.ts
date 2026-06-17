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

  function processCode(code: string) {
    const el = document.activeElement
    if (discardWhen?.(el)) {
      if (el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement) {
        el.value = ''
        el.dispatchEvent(new Event('input', { bubbles: true }))
      }
      return
    }
    lastCode.value = code
    onScan(code)
  }

  function onKeydown(e: KeyboardEvent) {
    if (e.ctrlKey || e.altKey || e.metaKey) return

    const activeEl = document.activeElement
    const onAllowed = isAllowed(activeEl)

    // ── Enter ──
    if (e.key === 'Enter') {
      if (buffer.length > 0) {
        // Scanner burst captured → process buffer
        e.preventDefault()
        const code = buffer
        clearBuffer()
        if (flushTimer) {
          clearTimeout(flushTimer)
          flushTimer = null
        }
        processCode(code)
      } else if (onAllowed && activeEl instanceof HTMLInputElement) {
        // Manual typing in scanner/search input → read input value directly
        const val = activeEl.value.trim()
        if (val) {
          e.preventDefault()
          processCode(val)
        }
      }
      return
    }

    // ── Only printable single characters ──
    if (e.key.length !== 1) return

    // ── Allowlisted inputs (scanner / search) ──
    // Let keystrokes flow naturally into the input.
    // On Enter we read the input value directly — no timing tracking needed.
    if (onAllowed) return

    // ── Protected inputs (payment amounts, etc.) ──
    // Use strict timing to detect scanner bursts and prevent contamination.
    const now = Date.now()
    const gap = now - lastTime
    lastTime = now

    if (gap > threshold && buffer.length > 0) {
      clearBuffer()
      if (flushTimer) {
        clearTimeout(flushTimer)
        flushTimer = null
      }
    }

    buffer += e.key
    isScanning.value = buffer.length > 0

    if (flushTimer) clearTimeout(flushTimer)
    flushTimer = setTimeout(clearBuffer, threshold * 15)

    if (gap <= threshold) {
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
