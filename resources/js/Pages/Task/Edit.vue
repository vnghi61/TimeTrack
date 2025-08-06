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
const task = page.props.task || {}

const form = reactive({
  title: '',
  description: '',
  project_id: '',
  assigned_to: '',
  priority: 'Medium',
  status: 'Todo',
  due_date: ''
})

const errors = reactive({})
const loading = ref(false)
const projects = ref([])
const api = useApi()

const loadProjects = async () => {
  try {
    projects.value = await api.get('/api/projects')
  } catch (error) {
    console.error('Error loading projects:', error)
  }
}

const initForm = () => {
  form.title = task.title || ''
  form.description = task.description || ''
  form.project_id = task.project_id || ''
  form.assigned_to = task.assigned_to || ''
  form.priority = task.priority || 'Medium'
  form.status = task.status || 'Todo'
  form.due_date = task.due_date || ''
}

const updateTask = async () => {
  Object.keys(errors).forEach(key => delete errors[key])
  
  if (!form.title) errors.title = 'Tiêu đề là bắt buộc'
  if (!form.project_id) errors.project_id = 'Dự án là bắt buộc'
  
  if (Object.keys(errors).length > 0) return
  
  loading.value = true
  try {
    await api.put(`/api/tasks/${task.id}`, form)
    router.visit('/task')
  } catch (error) {
    console.error('Error updating task:', error)
    if (error.errors) {
      Object.assign(errors, error.errors)
    } else {
      errors.general = 'Có lỗi xảy ra khi cập nhật công việc'
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProjects()
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
                <BreadcrumbLink href="/task">Quản lý Công việc</BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator class="hidden md:block" />
              <BreadcrumbItem>
                <BreadcrumbPage>Chỉnh sửa Công việc</BreadcrumbPage>
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
          <h1 class="text-3xl font-bold text-gray-900">Chỉnh sửa Công việc</h1>
          
          <div class="bg-white rounded-lg shadow p-6">
            <div v-if="errors.general" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
              {{ errors.general }}
            </div>
            
            <div class="space-y-4">
              <div>
                <InputLabel for="title" value="Tiêu đề" />
                <Input 
                  id="title" 
                  v-model="form.title" 
                  type="text" 
                  class="mt-1 block w-full" 
                  placeholder="Nhập tiêu đề công việc"
                />
                <InputError :message="errors.title" class="mt-2" />
              </div>

              <div>
                <InputLabel for="description" value="Mô tả" />
                <textarea 
                  id="description" 
                  v-model="form.description" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                  rows="3"
                  placeholder="Nhập mô tả công việc"
                ></textarea>
              </div>

              <div>
                <InputLabel for="project_id" value="Dự án" />
                <select 
                  id="project_id" 
                  v-model="form.project_id" 
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">Chọn dự án</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
                <InputError :message="errors.project_id" class="mt-2" />
              </div>

              <div>
                <InputLabel for="assigned_to" value="Người thực hiện" />
                <Input 
                  id="assigned_to" 
                  v-model="form.assigned_to" 
                  type="text" 
                  class="mt-1 block w-full" 
                  placeholder="Nhập tên người thực hiện"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <InputLabel for="priority" value="Độ ưu tiên" />
                  <select 
                    id="priority" 
                    v-model="form.priority" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                  </select>
                </div>
                <div>
                  <InputLabel for="status" value="Trạng thái" />
                  <select 
                    id="status" 
                    v-model="form.status" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="Todo">Todo</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Review">Review</option>
                    <option value="Done">Done</option>
                  </select>
                </div>
              </div>

              <div>
                <InputLabel for="due_date" value="Ngày hết hạn" />
                <Input 
                  id="due_date" 
                  v-model="form.due_date" 
                  type="date" 
                  class="mt-1 block w-full"
                />
              </div>

              <div class="flex gap-3 pt-4">
                <Button @click="updateTask" :disabled="loading" class="bg-blue-600 hover:bg-blue-700">
                  <span v-if="loading" class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Đang xử lý...
                  </span>
                  <span v-else>Cập nhật Công việc</span>
                </Button>
                <Button @click="router.visit('/task')" variant="outline">
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