<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/useApi'
import AppSidebar from '@/components/AppSidebar.vue'
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb'
import { Separator } from '@/components/ui/separator'
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from '@/components/ui/sidebar'
import { Button } from '@/components/ui/button'
import UserDropdown from '@/Components/UserDropdown.vue'
import { Clock, MapPin, Users, Briefcase, CheckSquare, Calendar, UserCheck, UserX } from 'lucide-vue-next'
import { useI18n } from '@/plugins/i18n'

const api = useApi()
const attendance = ref(null)
const canCheckIn = ref(true)
const canCheckOut = ref(false)
const loading = ref(false)
const currentTime = ref(new Date().toLocaleString())
const stats = ref(null)
const statsLoading = ref(false)
const { t } = useI18n()

const updateTime = () => {
  currentTime.value = new Date().toLocaleString()
}

const getLocation = () => {
  return new Promise((resolve) => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve(`${position.coords.latitude}, ${position.coords.longitude}`)
        },
        () => resolve('Không thể xác định vị trí')
      )
    } else {
      resolve('Trình duyệt không hỗ trợ định vị')
    }
  })
}

const checkIn = async () => {
  loading.value = true
  try {
    const location = await getLocation()
    await api.post('/api/attendance/check-in', { location })
    await loadTodayStatus()
    alert(t('check_in_success'))
  } catch (error) {
    alert(error.response?.data?.error || t('error_occurred'))
  } finally {
    loading.value = false
  }
}

const checkOut = async () => {
  loading.value = true
  try {
    const location = await getLocation()
    await api.post('/api/attendance/check-out', { location })
    await loadTodayStatus()
    alert(t('check_out_success'))
  } catch (error) {
    alert(error.response?.data?.error || t('error_occurred'))
  } finally {
    loading.value = false
  }
}

const loadTodayStatus = async () => {
  try {
    const response = await api.get('/api/attendance/today-status')
    attendance.value = response.attendance
    canCheckIn.value = response.can_check_in
    canCheckOut.value = response.can_check_out
  } catch (error) {
    console.error('Error loading attendance status:', error)
  }
}

const loadStats = async () => {
  statsLoading.value = true
  try {
    stats.value = await api.get('/api/dashboard/stats')
  } catch (error) {
    console.error('Error loading stats:', error)
  } finally {
    statsLoading.value = false
  }
}

const formatBirthday = (birthday) => {
  if (!birthday) return ''
  const date = new Date(birthday)
  return `${date.getDate()}/${date.getMonth() + 1}`
}

onMounted(() => {
  loadTodayStatus()
  loadStats()
  setInterval(updateTime, 1000)
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
        </div>
        <div class="px-4">
          <UserDropdown />
        </div>
      </header>
      <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
        <div class="space-y-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ t('dashboard') }}</h1>
          
          <!-- Thông tin chấm công -->
          <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center gap-4 mb-4">
              <Clock class="h-8 w-8 text-blue-600" />
              <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ t('attendance') }}</h2>
                <p class="text-gray-600 dark:text-gray-300">{{ currentTime }}</p>
              </div>
            </div>
            
            <div v-if="attendance" class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div class="grid grid-cols-2 gap-4">
                <div v-if="attendance.check_in_time">
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('check_in_time') }}</p>
                  <p class="font-semibold text-gray-900 dark:text-gray-100">{{ attendance.check_in_time }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    <MapPin class="h-3 w-3" />
                    {{ attendance.check_in_location }}
                  </p>
                </div>
                <div v-if="attendance.check_out_time">
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('check_out_time') }}</p>
                  <p class="font-semibold text-gray-900 dark:text-gray-100">{{ attendance.check_out_time }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    <MapPin class="h-3 w-3" />
                    {{ attendance.check_out_location }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Nút chấm công -->
          <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <div class="flex gap-3">
              <Button 
                v-if="canCheckIn" 
                @click="checkIn" 
                :disabled="loading"
                class="bg-green-600 hover:bg-green-700"
              >
                <Clock class="h-4 w-4 mr-2" />
                {{ loading ? t('processing') : t('check_in') }}
              </Button>
              
              <Button 
                v-if="canCheckOut" 
                @click="checkOut" 
                :disabled="loading"
                class="bg-red-600 hover:bg-red-700"
              >
                <Clock class="h-4 w-4 mr-2" />
                {{ loading ? t('processing') : t('check_out') }}
              </Button>
            </div>
          </div>
          
          <!-- Stats cards -->
          <div v-if="statsLoading" class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-2 text-gray-600 dark:text-gray-300">{{ t('loading_stats') }}</span>
          </div>
          
          <div v-else-if="stats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Dự án -->
            <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('total_projects') }}</p>
                  <p class="text-2xl font-bold text-blue-600">{{ stats.projects.total }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('in_progress') }} {{ stats.projects.active }}</p>
                </div>
                <Briefcase class="h-8 w-8 text-blue-600" />
              </div>
            </div>
            
            <!-- Công việc -->
            <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('total_tasks') }}</p>
                  <p class="text-2xl font-bold text-green-600">{{ stats.tasks.total }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('completed') }} {{ stats.tasks.completed }}</p>
                </div>
                <CheckSquare class="h-8 w-8 text-green-600" />
              </div>
            </div>
            
            <!-- Đang làm việc -->
            <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('working_now') }}</p>
                  <p class="text-2xl font-bold text-orange-600">{{ stats.attendance.working_now }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('present') }} {{ stats.attendance.present }}</p>
                </div>
                <UserCheck class="h-8 w-8 text-orange-600" />
              </div>
            </div>
            
            <!-- Nghỉ làm -->
            <div class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('on_leave') }}</p>
                  <p class="text-2xl font-bold text-red-600">{{ stats.attendance.absent }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('not_checked_in') }}</p>
                </div>
                <UserX class="h-8 w-8 text-red-600" />
              </div>
            </div>
          </div>
          
          <!-- Sinh nhật sắp tới -->
          <div v-if="stats && stats.birthdays.length > 0" class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center gap-2 mb-4">
              <Calendar class="h-6 w-6 text-purple-600" />
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ t('upcoming_birthdays') }}</h2>
            </div>
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
              <div v-for="user in stats.birthdays" :key="user.id" class="flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ formatBirthday(user.birthday) }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Người đang làm việc -->
          <div v-if="stats && stats.attendance.current_attendees.length > 0" class="bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-700 rounded-lg shadow p-6">
            <div class="flex items-center gap-2 mb-4">
              <Users class="h-6 w-6 text-green-600" />
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ t('working_at_office') }}</h2>
            </div>
            <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
              <div v-for="att in stats.attendance.current_attendees" :key="att.id" class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ att.user?.name?.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ att.user?.name }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-300">{{ t('checked_in_at') }} {{ att.check_in_time?.substring(0, 5) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>
