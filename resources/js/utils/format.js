export function formatCurrency(value) {
  const number = Number(value ?? 0)

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(number)
}

export function formatDate(value) {
  if (!value) {
    return '-'
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function numberToIndonesianWords(value) {
  const number = Math.floor(Math.abs(Number(value ?? 0)))

  if (number === 0) {
    return 'nol'
  }

  if (number < 12) {
    return [
      'nol',
      'satu',
      'dua',
      'tiga',
      'empat',
      'lima',
      'enam',
      'tujuh',
      'delapan',
      'sembilan',
      'sepuluh',
      'sebelas',
    ][number]
  }

  if (number < 20) {
    return `${numberToIndonesianWords(number - 10)} belas`
  }

  if (number < 100) {
    const tens = Math.floor(number / 10)
    const remainder = number % 10

    return `${numberToIndonesianWords(tens)} puluh${remainder ? ` ${numberToIndonesianWords(remainder)}` : ''}`
  }

  if (number < 200) {
    return `seratus${number > 100 ? ` ${numberToIndonesianWords(number - 100)}` : ''}`
  }

  if (number < 1000) {
    const hundreds = Math.floor(number / 100)
    const remainder = number % 100

    return `${numberToIndonesianWords(hundreds)} ratus${remainder ? ` ${numberToIndonesianWords(remainder)}` : ''}`
  }

  if (number < 2000) {
    return `seribu${number > 1000 ? ` ${numberToIndonesianWords(number - 1000)}` : ''}`
  }

  if (number < 1000000) {
    const thousands = Math.floor(number / 1000)
    const remainder = number % 1000

    return `${numberToIndonesianWords(thousands)} ribu${remainder ? ` ${numberToIndonesianWords(remainder)}` : ''}`
  }

  return String(number)
}

function titleCaseWords(value) {
  return String(value)
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

export function formatDateInWords(value) {
  if (!value) {
    return ''
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const weekday = new Intl.DateTimeFormat('id-ID', { weekday: 'long' }).format(date)
  const month = new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(date)
  const day = numberToIndonesianWords(date.getDate())
  const year = numberToIndonesianWords(date.getFullYear())

  return `${titleCaseWords(weekday)}, ${titleCaseWords(day)} ${titleCaseWords(month)} ${titleCaseWords(year)}`
}
