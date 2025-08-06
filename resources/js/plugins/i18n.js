import { reactive, ref } from 'vue'
import { useApi } from '@/composables/useApi'

const currentLanguage = ref('vi')
const messages = reactive({})
const api = useApi()

export const i18n = {
  currentLanguage,
  messages,
  
  async init() {
    try {
      await this.loadMessages()
    } catch (error) {
      console.error('Failed to initialize i18n:', error)
    }
  },
  
  async setLanguage(lang) {
    try {
      await api.post('/api/language', { language: lang })
      currentLanguage.value = lang
      await this.loadMessages()
    } catch (error) {
      console.error('Error setting language:', error)
    }
  },
  
  async loadMessages() {
    try {
      const response = await api.get('/api/language')
      currentLanguage.value = response.language
      Object.assign(messages, response.messages)
    } catch (error) {
      console.error('Error loading messages:', error)
    }
  },
  
  t(key) {
    return messages[key] || key
  }
}

export function useI18n() {
  return {
    currentLanguage,
    messages,
    setLanguage: i18n.setLanguage.bind(i18n),
    t: i18n.t.bind(i18n)
  }
}