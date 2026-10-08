export function countCodePoints(value: string): number {
  if (!/[\uD800-\uDBFF][\uDC00-\uDFFF]/.test(value)) return value.length

  let count = 0
  for (const _codePoint of value) count += 1
  return count
}

export function limitCodePoints(value: string, limit: number): string {
  if (value.length <= limit) return value

  let count = 0
  let endIndex = 0
  for (const codePoint of value) {
    if (count >= limit) break
    endIndex += codePoint.length
    count += 1
  }

  return value.slice(0, endIndex)
}
