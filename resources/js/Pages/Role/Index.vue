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

const roles = ref([])
const loading = ref(false)
const searchQuery = ref('')
const api = useApi()
const { t } = useI18n()

let searchTimeout = null

const searchRoles = async (query = '') => {
  loading.value = true
  try {
    const url = query ? `/api/roles?search=${encodeURIComponent(query)}` : '/api/roles'
    roles.value = await api.get(url)
  } catch (error) {
    console.error('Error searching roles:', error)
  } finally {
    loading.value = false
  }
}

watch(searchQuery, (newQuery) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchRoles(newQuery)
  }, 300)
})

const deleteRole = async (roleId) => {
  if (confirm(t('confirm_delete_role'))) {
    try {
      await api.delete(`/api/roles/${roleId}`)
      await searchRoles()
    } catch (error) {
      console.error('Error deleting role:', error)
    }
  }
}

const editRole = (role) => {
  router.visit(`/role/${role.id}/edit`, {
    method: 'get',
    data: { role }
  })
}

const fetchRoles = () => searchRoles()

onMounted(() => {
  fetchRoles()
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
                <BreadcrumbPage>{{ t('role_management') }}</BreadcrumbPage>
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
            <h1 class="text-3xl font-bold text-gray-900">{{ t('role_management') }}</h1>
            <Link href="/role/create">
              <Button class="bg-blue-600 hover:bg-blue-700">
                {{ t('add_role') }}
              </Button>
            </Link>
          </div>

          <div class="bg-white p-4 rounded-lg shadow">
            <Input 
              v-model="searchQuery" 
              :placeholder="t('search_roles')" 
              class="w-full max-w-md"
            />
          </div>

          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div v-if="loading" class="flex justify-center items-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <span class="ml-2 text-gray-600">{{ t('loading') }}</span>
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('role_name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('description') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('actions') }}</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ role.description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        {{ role.status || 'Active' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button @click="editRole(role)" class="text-blue-600 hover:text-blue-900">
                        {{ t('edit') }}
                      </button>
                      <button @click="deleteRole(role.id)" class="text-red-600 hover:text-red-900">
                        {{ t('delete') }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div v-if="roles.length === 0 && !loading" class="text-center py-8 text-gray-500">
                {{ t('no_roles_found') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>