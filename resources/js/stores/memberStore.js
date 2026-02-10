// stores/memberStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

export const useMemberStore = defineStore('member', () => {
  const members = ref([])
  const loading = ref(false)
  const error = ref(null)

  const fetchMembers = async () => {
    try {
      loading.value = true
      const response = await axios.get('/api/members')
      members.value = response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch members'
    } finally {
      loading.value = false
    }
  }

  const addMember = async (memberData) => {
    try {
      loading.value = true
      const response = await axios.post('/api/members', memberData)
      members.value.push(response.data)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to add member'
      throw err
    } finally {
      loading.value = false
    }
  }

  const updateMember = async (id, memberData) => {
    try {
      loading.value = true
      const response = await axios.put(`/api/members/${id}`, memberData)
      const index = members.value.findIndex(m => m.id === id)
      if (index !== -1) {
        members.value[index] = response.data
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to update member'
      throw err
    } finally {
      loading.value = false
    }
  }

  const deleteMember = async (id) => {
    try {
      loading.value = true
      await axios.delete(`/api/members/${id}`)
      members.value = members.value.filter(m => m.id !== id)
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete member'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    members,
    loading,
    error,
    fetchMembers,
    addMember,
    updateMember,
    deleteMember
  }
})
