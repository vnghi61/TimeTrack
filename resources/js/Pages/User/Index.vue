<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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
import UserDropdown from '@/Components/UserDropdown.vue'
import { useI18n } from '@/plugins/i18n'

const users = ref([])
const loading = ref(false)
const searchQuery = ref('')
const api = useApi()
const { t } = useI18n()

let searchTimeout = null

const searchUsers = async (query = '') => {
  loading.value = true
  try {
    const url = query ? `/api/users?search=${encodeURIComponent(query)}` : '/api/users'
    users.value = await api.get(url)
  } catch (error) {
    console.error('Error searching users:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, (newQuery) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchUsers(newQuery)
  }, 300)
})

const fetchUsers = () => searchUsers()

const deleteUser = async (userId) => {
  if (confirm('Bạn có chắc chắn muốn xóa user này?')) {
    try {
      await api.delete(`/api/users/${userId}`)
      await fetchUsers()
    } catch (error) {
      console.error('Error deleting user:', error)
    }
  }
}

const editUser = (user) => {
  router.visit(`/user/${user.id}/edit`, {
    method: 'get',
    data: { user }
  })
}



onMounted(() => {
  fetchUsers()
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
                <BreadcrumbPage>{{ t('user_management') }}</BreadcrumbPage>
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
          <!-- Header -->
          <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ t('user_management') }}</h1>
            <Link href="/user/create">
              <Button class="bg-blue-600 hover:bg-blue-700">
                {{ t('add_user') }}
              </Button>
            </Link>
          </div>

          <!-- Search -->
          <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <Input 
              v-model="searchQuery" 
:placeholder="t('search_placeholder')" 
              class="w-full max-w-md"
            />
          </div>

          <!-- Users Table -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <span class="ml-2 text-gray-600 dark:text-gray-300">Đang tải...</span>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('email') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ngày sinh</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('role') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ t('actions') }}</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900 dark:text-gray-100">{{ user.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900 dark:text-gray-100">{{ user.birthday || 'Chưa có' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        {{ user.role || 'User' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-300">
                        {{ user.status || 'Active' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button @click="editUser(user)" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                        {{ t('edit') }}
                      </button>
                      <button @click="deleteUser(user.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                        {{ t('delete') }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="users.length === 0 && !loading" class="text-center py-8 text-gray-500 dark:text-gray-400">
                {{ t('no_users_found') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>