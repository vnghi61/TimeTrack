import { ref, reactive } from 'vue'
import { useApi } from './useApi'

const currentLanguage = ref('vi')
const messages = reactive({})
const api = useApi()

export function useLanguage() {
  const setLanguage = async (lang) => {
    try {
      await api.post('/api/language', { language: lang })
      currentLanguage.value = lang
      await loadMessages()
    } catch (error) {
      console.error('Error setting language:', error)
    }
  }

  const loadMessages = async () => {
    try {
      const response = await api.get('/api/language')
      currentLanguage.value = response.language
      Object.assign(messages, response.messages)
    } catch (error) {
      console.error('Error loading messages:', error)
    }
  }

  const t = (key) => {
    return messages[key] || key
  }

  return {
    currentLanguage,
    messages,
    setLanguage,
    loadMessages,
    t
  }
}