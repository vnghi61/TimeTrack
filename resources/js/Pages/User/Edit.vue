<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useApi } from '@/composables/useApi'
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
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import UserDropdown from '@/Components/UserDropdown.vue'

const page = usePage()
const user = page.props.user || {}

const form = reactive({
  name: '',
  email: '',
  birthday: '',
  role: 'User',
  status: 'Active'
})

const errors = reactive({})
const loading = ref(false)
const api = useApi()

const initForm = () => {
  form.name = user.name || ''
  form.email = user.email || ''
  form.birthday = user.birthday || ''
  form.role = user.role || 'User'
  form.status = user.status || 'Active'
}

const updateUser = async () => {
  Object.keys(errors).forEach(key => delete errors[key])
  
  if (!form.name) errors.name = 'Tên là bắt buộc'
  if (!form.email) errors.email = 'Email là bắt buộc'
  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Vui lòng nhập email hợp lệ'
  }
  
  if (Object.keys(errors).length > 0) return
  
  loading.value = true
  try {
    await api.put(`/api/users/${user.id}`, form)
    router.visit('/user')
  } catch (error) {
    console.error('Error updating user:', error)
    if (error.errors) {
      Object.assign(errors, error.errors)
    } else {
      errors.general = 'Có lỗi xảy ra khi cập nhật user'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  initForm()
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
                <BreadcrumbLink href="/user">Quản lý User</BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbPage>Chỉnh sửa User</BreadcrumbPage>
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
          <h1 class="text-3xl font-bold text-gray-900">Chỉnh sửa User</h1>
          
          <div class="bg-white rounded-lg shadow p-6">
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
                  placeholder="Nhập tên user"
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
                  placeholder="Nhập email"
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
                <InputError :message="errors.birthday" class="mt-2" />
              </div>

              <div>
                <InputLabel for="role" value="Vai trò" />
                <select 
                  id="role" 
                  v-model="form.role" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="User">User</option>
                  <option value="Admin">Admin</option>
                </select>
              </div>

              <div>
                <InputLabel for="status" value="Trạng thái" />
                <select 
                  id="status" 
                  v-model="form.status" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>

              <div class="flex gap-3 pt-4">
                <Button @click="updateUser" :disabled="loading" class="bg-blue-600 hover:bg-blue-700">
                  <span v-if="loading" class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Đang xử lý...
                  </span>
                  <span v-else>Cập nhật User</span>
                </Button>
                <Button @click="router.visit('/user')" variant="outline">
                  Hủy
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>