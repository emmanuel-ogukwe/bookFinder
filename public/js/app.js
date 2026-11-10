const FILTER_OPTIONS = [
  { key: 'all', label: 'All' },
  { key: 'Public Domain', label: 'Public Domain' },
  { key: 'Free Download', label: 'Free Download' },
  { key: 'Read Online', label: 'Read Online' },
  { key: 'Full Preview', label: 'Full Preview' },
  { key: 'Preview', label: 'Preview' },
  { key: 'PDF Available', label: 'PDF' },
  { key: 'EPUB Available', label: 'EPUB' },
]

const state = {
  books: [],
  loading: false,
  searched: false,
  filter: 'all',
  showFilters: false,
}

const els = {
  form: document.getElementById('searchForm'),
  input: document.getElementById('searchInput'),
  searchBtn: document.getElementById('searchBtn'),
  resultCount: document.getElementById('resultCount'),
  loader: document.getElementById('loader'),
  empty: document.getElementById('empty'),
  resultsList: document.getElementById('resultsList'),
  fab: document.getElementById('filterFab'),
  filterPanel: document.getElementById('filterPanel'),
  filterClose: document.getElementById('filterClose'),
  filterOptions: document.getElementById('filterOptions'),
}

els.searchBtn.disabled = true

els.input.addEventListener('input', () => {
  els.searchBtn.disabled = !els.input.value.trim() || state.loading
})

els.form.addEventListener('submit', (e) => {
  e.preventDefault()
  search(els.input.value)
})

els.fab.addEventListener('click', () => {
  state.showFilters = !state.showFilters
  updatePanel()
})

els.filterClose.addEventListener('click', () => {
  state.showFilters = false
  updatePanel()
})

async function search(query) {
  const trimmed = query.trim()
  if (!trimmed) return

  state.loading = true
  state.searched = true
  render()

  try {
    const res = await fetch(`/api/search?q=${encodeURIComponent(trimmed)}`)
    const data = await res.json()
    state.books = data.books || []
  } catch {
    state.books = []
  } finally {
    state.loading = false
    render()
  }
}

function filtered() {
  if (state.filter === 'all') return state.books
  return state.books.filter((book) => book.tags.some((tag) => tag === state.filter))
}

function render() {
  els.searchBtn.disabled = !els.input.value.trim() || state.loading
  els.loader.hidden = !state.loading
  els.searchBtn.textContent = state.loading ? '...' : 'Search'

  const visible = filtered()

  if (state.searched && !state.loading) {
    els.resultCount.hidden = false
    els.resultCount.textContent = `${visible.length} ${visible.length === 1 ? 'result' : 'results'} found`
  } else {
    els.resultCount.hidden = true
  }

  els.empty.hidden = !(state.searched && !state.loading && visible.length === 0)

  els.resultsList.innerHTML = ''
  const fragment = document.createDocumentFragment()

  for (const book of visible) {
    fragment.appendChild(bookCard(book))
  }

  els.resultsList.appendChild(fragment)
}

function bookCard(book) {
  const card = document.createElement('div')
  card.className = 'card'

  const header = document.createElement('div')
  header.className = 'cardHeader'

  if (book.cover) {
    const img = document.createElement('img')
    img.className = 'bookImg'
    img.src = book.cover
    img.alt = book.title
    img.loading = 'lazy'
    header.appendChild(img)
  } else {
    const placeholder = document.createElement('div')
    placeholder.className = 'bookImg placeholder'
    header.appendChild(placeholder)
  }

  const details = document.createElement('div')
  details.className = 'details'

  const title = document.createElement('p')
  title.className = 'title'
  title.textContent = book.title

  const author = document.createElement('p')
  author.className = 'author'
  author.textContent = book.author

  const tags = document.createElement('div')
  tags.className = 'tags'

  for (const tag of book.tags) {
    const span = document.createElement('span')
    span.className = 'tag'
    span.textContent = tag
    tags.appendChild(span)
  }

  if (book.year) {
    const year = document.createElement('span')
    year.className = 'tag'
    year.textContent = book.year
    tags.appendChild(year)
  }

  details.appendChild(title)
  details.appendChild(author)
  details.appendChild(tags)
  header.appendChild(details)

  const actions = document.createElement('div')
  actions.className = 'cardActions'

  const readBtn = document.createElement('a')
  readBtn.className = 'btnPrimary'
  readBtn.href = book.link
  readBtn.target = '_blank'
  readBtn.rel = 'noopener noreferrer'
  readBtn.textContent = 'Read Now'

  const sourceBtn = document.createElement('a')
  sourceBtn.className = 'btnSecondary'
  sourceBtn.href = book.link
  sourceBtn.target = '_blank'
  sourceBtn.rel = 'noopener noreferrer'
  sourceBtn.textContent = book.source

  actions.appendChild(readBtn)
  actions.appendChild(sourceBtn)

  card.appendChild(header)
  card.appendChild(actions)

  return card
}

function updatePanel() {
  els.filterPanel.hidden = !state.showFilters

  els.filterOptions.innerHTML = ''
  for (const option of FILTER_OPTIONS) {
    const chip = document.createElement('button')
    chip.className = `filterChip${state.filter === option.key ? ' active' : ''}`
    chip.textContent = option.label
    chip.addEventListener('click', () => {
      state.filter = option.key
      state.showFilters = false
      render()
      updatePanel()
    })
    els.filterOptions.appendChild(chip)
  }
}

updatePanel()