<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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
import UserDropdown from '@/Components/UserDropdown.vue'

const departments = ref([])
const loading = ref(false)
const searchQuery = ref('')
const api = useApi()
const { t } = useI18n()

let searchTimeout = null

const searchDepartments = async (query = '') => {
  loading.value = true
  try {
    const url = query ? `/api/departments?search=${encodeURIComponent(query)}` : '/api/departments'
    departments.value = await api.get(url)
  } catch (error) {
    console.error('Error searching departments:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, (newQuery) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchDepartments(newQuery)
  }, 300)
})

const deleteDepartment = async (departmentId) => {
  if (confirm('Bạn có chắc chắn muốn xóa phòng ban này?')) {
    try {
      await api.delete(`/api/departments/${departmentId}`)
      await searchDepartments()
    } catch (error) {
      console.error('Error deleting department:', error)
    }
  }
}

const editDepartment = (department) => {
  router.visit(`/department/${department.id}/edit`, {
    method: 'get',
    data: { department }
  })
}

const fetchDepartments = () => searchDepartments()

onMounted(() => {
  fetchDepartments()
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
                <BreadcrumbPage>Quản lý Phòng ban</BreadcrumbPage>
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
          <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Quản lý Phòng ban</h1>
            <Link href="/department/create">
              <Button class="bg-blue-600 hover:bg-blue-700">
                Thêm Phòng ban Mới
              </Button>
            </Link>
          </div>

          <div class="bg-white p-4 rounded-lg shadow">
            <Input 
              v-model="searchQuery" 
              placeholder="Tìm kiếm theo tên phòng ban..." 
              class="w-full max-w-md"
            />
          </div>

          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <span class="ml-2 text-gray-600">Đang tải...</span>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên Phòng ban</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trưởng phòng</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="department in departments" :key="department.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ department.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ department.description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ department.manager || 'Chưa có' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        {{ department.status || 'Active' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button @click="editDepartment(department)" class="text-blue-600 hover:text-blue-900">
                        Sửa
                      </button>
                      <button @click="deleteDepartment(department.id)" class="text-red-600 hover:text-red-900">
                        Xóa
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="departments.length === 0 && !loading" class="text-center py-8 text-gray-500">
                Không tìm thấy phòng ban nào
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>