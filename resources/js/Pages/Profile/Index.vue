<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useApi } from '@/composables/useApi'
import { useI18n } from '@/plugins/i18n'
import AppSidebar from '@/Components/AppSidebar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb'
import { Separator } from '@/Components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/Components/ui/sidebar'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import UserDropdown from '@/Components/UserDropdown.vue'
import { User, Mail, Calendar, Shield } from 'lucide-vue-next'

const form = reactive({
  name: '',
  email: '',
  birthday: '',
  current_password: '',
  password: '',
  password_confirmation: ''
})

const user = ref({})
const errors = reactive({})
const loading = ref(false)
const api = useApi()
const { t } = useI18n()

const loadProfile = async () => {
  try {
    const response = await api.get('/api/profile')
    user.value = response
    form.name = response.name || ''
    form.email = response.email || ''
    form.birthday = response.birthday || ''
  } catch (error) {
    console.error('Error loading profile:', error)
  }
}

const updateProfile = async () => {
  Object.keys(errors).forEach(key => delete errors[key])
  
  if (!form.name) errors.name = 'Tên là bắt buộc'
  if (!form.email) errors.email = 'Email là bắt buộc'
  
  if (Object.keys(errors).length > 0) return
  
  loading.value = true
  try {
    await api.put('/api/profile', {
      name: form.name,
      email: form.email,
      birthday: form.birthday
    })
    alert('Cập nhật thông tin thành công!')
    await loadProfile()
  } catch (error) {
    console.error('Error updating profile:', error)
    if (error.errors) {
      Object.assign(errors, error.errors)
    } else {
      errors.general = 'Có lỗi xảy ra khi cập nhật thông tin'
    }
  } finally {
    loading.value = false
  }
}

const updatePassword = async () => {
  Object.keys(errors).forEach(key => delete errors[key])
  
  if (!form.current_password) errors.current_password = 'Mật khẩu hiện tại là bắt buộc'
  if (!form.password) errors.password = 'Mật khẩu mới là bắt buộc'
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Xác nhận mật khẩu không khớp'
  }
  
  if (Object.keys(errors).length > 0) return
  
  loading.value = true
  try {
    await api.put('/api/profile/password', {
      current_password: form.current_password,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    alert('Đổi mật khẩu thành công!')
    form.current_password = ''
    form.password = ''
    form.password_confirmation = ''
  } catch (error) {
    console.error('Error updating password:', error)
    if (error.errors) {
      Object.assign(errors, error.errors)
    } else {
      errors.general = 'Có lỗi xảy ra khi đổi mật khẩu'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProfile()
})
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
        <div class="flex items-center gap-2 px-4 flex-1">
          <SidebarTrigger class="-ml-1" />
          <Separator orientation="vertical" class="mr-2 h-4" />
          <Breadcrumb>
            <BreadcrumbList>
              <BreadcrumbItem class="hidden md:block">
                <BreadcrumbLink href="/dashboard">Dashboard</BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbPage>{{ t('profile') }}</BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>
        </div>
        <div class="px-4">
          <UserDropdown />
        </div>
      </header>
      
      <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <div class="space-y-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ t('profile') }}</h1>
          
          <!-- Profile Info -->
          <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center gap-6 mb-6">
              <Avatar class="h-20 w-20">
                <AvatarImage :src="user.avatar" :alt="user.name" />
                <AvatarFallback class="bg-blue-500 text-white text-2xl">
                  {{ user.name?.charAt(0).toUpperCase() }}
                </AvatarFallback>
              </Avatar>
              <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</h2>
                <p class="text-gray-600 dark:text-gray-300">{{ user.email }}</p>
                <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                  <div class="flex items-center gap-1">
                    <Calendar class="h-4 w-4" />
                    <span>{{ user.birthday || 'Chưa có' }}</span>
                  </div>
                  <div class="flex items-center gap-1">
                    <Shield class="h-4 w-4" />
                    <span>{{ user.role || 'User' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Update Profile -->
          <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Cập nhật thông tin</h3>
            
            <div v-if="errors.general" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
              {{ errors.general }}
            </div>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="name" value="Tên" />
                <Input 
                  id="name" 
                  v-model="form.name" 
                  type="text" 
                  class="mt-1 block w-full" 
                />
                <InputError :message="errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="email" value="Email" />
                <Input 
                  id="email" 
                  v-model="form.email" 
                  type="email" 
                  class="mt-1 block w-full" 
                />
                <InputError :message="errors.email" class="mt-2" />
              </div>

              <div>
                <InputLabel for="birthday" value="Ngày sinh" />
                <Input 
                  id="birthday" 
                  v-model="form.birthday" 
                  type="date" 
                  class="mt-1 block w-full" 
                />
              </div>

              <Button @click="updateProfile" :disabled="loading" class="bg-blue-600 hover:bg-blue-700">
                {{ loading ? 'Đang xử lý...' : 'Cập nhật thông tin' }}
              </Button>
            </div>
          </div>

          <!-- Change Password -->
          <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Đổi mật khẩu</h3>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="current_password" value="Mật khẩu hiện tại" />
                <Input 
                  id="current_password" 
                  v-model="form.current_password" 
                  type="password" 
                  class="mt-1 block w-full" 
                />
                <InputError :message="errors.current_password" class="mt-2" />
              </div>

              <div>
                <InputLabel for="password" value="Mật khẩu mới" />
                <Input 
                  id="password" 
                  v-model="form.password" 
                  type="password" 
                  class="mt-1 block w-full" 
                />
                <InputError :message="errors.password" class="mt-2" />
              </div>

              <div>
                <InputLabel for="password_confirmation" value="Xác nhận mật khẩu" />
                <Input 
                  id="password_confirmation" 
                  v-model="form.password_confirmation" 
                  type="password" 
                  class="mt-1 block w-full" 
                />
                <InputError :message="errors.password_confirmation" class="mt-2" />
              </div>

              <Button @click="updatePassword" :disabled="loading" class="bg-green-600 hover:bg-green-700">
                {{ loading ? 'Đang xử lý...' : 'Đổi mật khẩu' }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>