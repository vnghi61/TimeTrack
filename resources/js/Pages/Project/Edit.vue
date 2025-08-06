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
const project = page.props.project || {}

const form = reactive({
  name: '',
  description: '',
  start_date: '',
  end_date: '',
  status: 'Planning',
  manager: ''
})

const errors = reactive({})
const loading = ref(false)
const api = useApi()

const initForm = () => {
  form.name = project.name || ''
  form.description = project.description || ''
  form.start_date = project.start_date || ''
  form.end_date = project.end_date || ''
  form.status = project.status || 'Planning'
  form.manager = project.manager || ''
}

const updateProject = async () => {
  Object.keys(errors).forEach(key => delete errors[key])
  
  if (!form.name) errors.name = 'Tên dự án là bắt buộc'
  
  if (Object.keys(errors).length > 0) return
  
  loading.value = true
  try {
    await api.put(`/api/projects/${project.id}`, form)
    router.visit('/project')
  } catch (error) {
    console.error('Error updating project:', error)
    if (error.errors) {
      Object.assign(errors, error.errors)
    } else {
      errors.general = 'Có lỗi xảy ra khi cập nhật dự án'
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
                <BreadcrumbLink href="/project">Quản lý Dự án</BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbPage>Chỉnh sửa Dự án</BreadcrumbPage>
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
          <h1 class="text-3xl font-bold text-gray-900">Chỉnh sửa Dự án</h1>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div v-if="errors.general" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
              {{ errors.general }}
            </div>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="name" value="Tên Dự án" />
                <Input 
                  id="name" 
                  v-model="form.name" 
                  type="text" 
                  class="mt-1 block w-full" 
                  placeholder="Nhập tên dự án"
                />
                <InputError :message="errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="description" value="Mô tả" />
                <textarea 
                  id="description" 
                  v-model="form.description" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                  rows="3"
                  placeholder="Nhập mô tả dự án"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <InputLabel for="start_date" value="Ngày bắt đầu" />
                  <Input 
                    id="start_date" 
                    v-model="form.start_date" 
                    type="date" 
                    class="mt-1 block w-full"
                  />
                </div>
                <div>
                  <InputLabel for="end_date" value="Ngày kết thúc" />
                  <Input 
                    id="end_date" 
                    v-model="form.end_date" 
                    type="date" 
                    class="mt-1 block w-full"
                  />
                </div>
              </div>

              <div>
                <InputLabel for="manager" value="Quản lý dự án" />
                <Input 
                  id="manager" 
                  v-model="form.manager" 
                  type="text" 
                  class="mt-1 block w-full" 
                  placeholder="Nhập tên quản lý dự án"
                />
              </div>

              <div>
                <InputLabel for="status" value="Trạng thái" />
                <select 
                  id="status" 
                  v-model="form.status" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="Planning">Planning</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                  <option value="On Hold">On Hold</option>
                </select>
              </div>

              <div class="flex gap-3 pt-4">
                <Button @click="updateProject" :disabled="loading" class="bg-blue-600 hover:bg-blue-700">
                  <span v-if="loading" class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Đang xử lý...
                  </span>
                  <span v-else>Cập nhật Dự án</span>
                </Button>
                <Button @click="router.visit('/project')" variant="outline">
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