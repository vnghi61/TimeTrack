<script setup>
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from '@/plugins/i18n'
import { useTheme } from '@/composables/useTheme'
import { 
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { Button } from '@/Components/ui/button'
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar'
import { User, Settings, LogOut, Moon, Sun, Languages } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi'

const page = usePage()
const user = page.props.auth?.user || { name: 'User', email: 'user@example.com' }

const { currentLanguage, setLanguage, t } = useI18n()
const { theme, toggleTheme } = useTheme()

const toggleLanguage = async () => {
  const newLang = currentLanguage.value === 'vi' ? 'en' : 'vi'
  await setLanguage(newLang)
}

const api = useApi()

const logout = async () => {
  try {
    await api.post('/logout')
    window.location.href = '/login'
  } catch (error) {
    window.location.href = '/logout'
  }
}


</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="relative h-10 w-10 rounded-full hover:bg-gray-100   dark:hover:bg-gray-700 transition-colors">
        <Avatar class="h-10 w-10">
          <AvatarImage :src="user.avatar" :alt="user.name" />
          <AvatarFallback class="bg-blue-500 text-white">
            {{ user.name.charAt(0).toUpperCase() }}
          </AvatarFallback>
        </Avatar>
      </Button>
    </DropdownMenuTrigger>
    
    <DropdownMenuContent class="w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700" align="end">
      <DropdownMenuLabel class="font-normal text-gray-900 dark:text-gray-100">
        <div class="flex flex-col space-y-1">
          <p class="text-sm font-medium leading-none text-gray-900 dark:text-gray-100">{{ user.name }}</p>
          <p class="text-xs leading-none text-gray-500 dark:text-gray-400">{{ user.email }}</p>
        </div>
      </DropdownMenuLabel>
      
      <DropdownMenuSeparator />
      
      <DropdownMenuItem as-child class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
        <Link href="/profile" class="flex items-center text-gray-900 dark:text-gray-100">
          <User class="mr-2 h-4 w-4" />
          <span>{{ t('profile') }}</span>
        </Link>
      </DropdownMenuItem>
      
      <DropdownMenuSeparator />
      
      <DropdownMenuItem @click="toggleTheme" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100">
        <component :is="theme === 'dark' ? Sun : Moon" class="mr-2 h-4 w-4" />
        <span>{{ theme === 'dark' ? t('theme_light') : t('theme_dark') }}</span>
      </DropdownMenuItem>
      
      <DropdownMenuItem @click="toggleLanguage" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100">
        <Languages class="mr-2 h-4 w-4" />
        <span>{{ currentLanguage === 'vi' ? 'English' : 'Tiếng Việt' }}</span>
      </DropdownMenuItem>
      
      <DropdownMenuSeparator />
      
      <DropdownMenuItem @click="logout" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400">
        <LogOut class="mr-2 h-4 w-4" />
        <span>{{ t('logout') }}</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>