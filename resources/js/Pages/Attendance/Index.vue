<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
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
import { Input } from '@/Components/ui/input'
import UserDropdown from '@/Components/UserDropdown.vue'
import { Clock, MapPin } from 'lucide-vue-next'

const attendances = ref([])
const loading = ref(false)
const searchQuery = ref('')
const api = useApi()

let searchTimeout = null

const searchAttendances = async (query = '') => {
  loading.value = true
  try {
    const url = query ? `/api/attendances?search=${encodeURIComponent(query)}` : '/api/attendances'
    attendances.value = await api.get(url)
  } catch (error) {
    console.error('Error searching attendances:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, (newQuery) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchAttendances(newQuery)
  }, 300)
})

const formatTime = (time) => {
  if (!time) return 'Chưa có'
  return time.substring(0, 5) // HH:MM
}

const calculateWorkHours = (checkIn, checkOut) => {
  if (!checkIn || !checkOut) return 'Chưa hoàn thành'
  
  const [inHour, inMin] = checkIn.split(':').map(Number)
  const [outHour, outMin] = checkOut.split(':').map(Number)
  
  const inMinutes = inHour * 60 + inMin
  const outMinutes = outHour * 60 + outMin
  
  const diffMinutes = outMinutes - inMinutes
  const hours = Math.floor(diffMinutes / 60)
  const minutes = diffMinutes % 60
  
  return `${hours}h ${minutes}m`
}

onMounted(() => {
  searchAttendances()
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
                <BreadcrumbPage>Lịch sử Chấm công</BreadcrumbPage>
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
            <h1 class="text-3xl font-bold text-gray-900">Lịch sử Chấm công</h1>
          </div>

          <div class="bg-white p-4 rounded-lg shadow">
            <Input 
              v-model="searchQuery" 
              placeholder="Tìm kiếm theo tên nhân viên..." 
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân viên</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ vào</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ ra</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng giờ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vị trí</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ attendance.user?.name || 'N/A' }}</div>
                      <div class="text-sm text-gray-500">{{ attendance.user?.email || 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ attendance.date }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-1">
                        <Clock class="h-4 w-4 text-green-600" />
                        <span class="text-sm text-gray-900">{{ formatTime(attendance.check_in_time) }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-1">
                        <Clock class="h-4 w-4 text-red-600" />
                        <span class="text-sm text-gray-900">{{ formatTime(attendance.check_out_time) }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ calculateWorkHours(attendance.check_in_time, attendance.check_out_time) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-xs text-gray-500">
                        <div v-if="attendance.check_in_location" class="flex items-center gap-1 mb-1">
                          <MapPin class="h-3 w-3 text-green-600" />
                          <span>Vào: {{ attendance.check_in_location }}</span>
                        </div>
                        <div v-if="attendance.check_out_location" class="flex items-center gap-1">
                          <MapPin class="h-3 w-3 text-red-600" />
                          <span>Ra: {{ attendance.check_out_location }}</span>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="attendances.length === 0 && !loading" class="text-center py-8 text-gray-500">
                Không tìm thấy dữ liệu chấm công
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>