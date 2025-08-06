import { ref, watch } from 'vue'

const theme = ref(localStorage.getItem('theme') || 'light')

export function useTheme() {
  const setTheme = (newTheme) => {
    theme.value = newTheme
    localStorage.setItem('theme', newTheme)
    
    // Remove both classes first
    document.documentElement.classList.remove('dark')
    document.body.classList.remove('dark')
    
    if (newTheme === 'dark') {
      document.documentElement.classList.add('dark')
      document.body.classList.add('dark')
    }
    
    // Force repaint
    document.body.style.display = 'none'
    document.body.offsetHeight
    document.body.style.display = ''
  }

  const toggleTheme = () => {
    setTheme(theme.value === 'light' ? 'dark' : 'light')
  }

  // Initialize theme on load
  if (theme.value === 'dark') {
    document.documentElement.classList.add('dark')
    document.body.classList.add('dark')
  }

  return {
    theme,
    setTheme,
    toggleTheme
  }
}