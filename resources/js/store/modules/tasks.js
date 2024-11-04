import axios from 'axios';

export default {
    namespaced: true,

    state: {
        tasks: [],
        loading: false,
        error: null,
        pagination: {
            currentPage: 1,
            totalPages: 1,
            perPage: 10,
            total: 0
        },
        filters: {
            search: '',
            priority: '',
            status: '',
            dateFrom: '',
            dateTo: '',
            sortBy: 'created_at',
            sortDirection: 'desc'
        }
    },

    getters: {
        getTasks: state => state.tasks,
        isLoading: state => state.loading,
        getError: state => state.error,
        getPagination: state => state.pagination,
        getFilters: state => state.filters,
        getTaskById: state => id => state.tasks.find(task => task.id === id),
        getArchivedTasks: (state) => state.tasks.filter(task => task.archived_at),
        getActiveTasks: (state) => state.tasks.filter(task => !task.archived_at),
    },

    mutations: {
        SET_TASKS(state, tasks) {
            state.tasks = tasks;
        },
        ADD_TASK(state, task) {
            state.tasks.unshift(task);
        },
        UPDATE_TASK(state, updatedTask) {
            const index = state.tasks.findIndex(task => task.id === updatedTask.id);
            if (index !== -1) {
                state.tasks.splice(index, 1, updatedTask);
            }
        },
        REMOVE_TASK(state, taskId) {
            state.tasks = state.tasks.filter(task => task.id !== taskId);
        },
        SET_LOADING(state, status) {
            state.loading = status;
        },
        SET_ERROR(state, error) {
            state.error = error;
        },
        SET_PAGINATION(state, pagination) {
            state.pagination = { ...state.pagination, ...pagination };
        },
        SET_FILTERS(state, filters) {
            state.filters = { ...state.filters, ...filters };
        }
    },

    actions: {
        async fetchTasks({ commit, state }, params = {}) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const queryParams = {
                    ...state.filters,
                    ...params,
                    page: params.page || state.pagination.currentPage
                };

                const response = await axios.get('/v1/tasks', { params: queryParams });

                console.log(response.data.data);
                
                commit('SET_TASKS', response.data.data);
                commit('SET_PAGINATION', {
                    currentPage: response.data.meta.current_page,
                    totalPages: response.data.meta.last_page,
                    total: response.data.meta.total,
                    perPage: response.data.meta.per_page
                });
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Failed to fetch tasks');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async createTask({ commit }, taskData) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post('/v1/tasks', taskData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                commit('ADD_TASK', response.data.data);
                return response.data.data;
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Failed to create task');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async updateTask({ commit }, { taskId, data }) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.post(`/v1/tasks/${taskId}`, data, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                commit('UPDATE_TASK', response.data.data);
                return response.data.data;
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Failed to update task');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async deleteTask({ commit }, taskId) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                await axios.delete(`/v1/tasks/${taskId}`);
                commit('REMOVE_TASK', taskId);
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Failed to delete task');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        async completeTask({ dispatch }, task) {
            const endpoint = task.completed_at
                ? `/v1/tasks/incomplete/${task.id}`
                : `/v1/tasks/complete/${task.id}`;
            
            try {
                await axios.patch(endpoint);
                await dispatch('fetchTasks');
            } catch (e) {
                throw e.response?.data?.message || 'Failed to toggle task completion';
            }
        },

        async archiveTask({ commit }, taskId) {
            try {
              const response = await axios.patch(`/v1/tasks/archive/${taskId}`);
              commit('UPDATE_TASK', response.data);
              return response.data;
            } catch (e) {
              throw e;
            }
        },
        
        async restoreTask({ commit }, taskId) {
            try {
                const response = await axios.patch(`/v1/tasks/restore/${taskId}`);
                commit('UPDATE_TASK', response.data);
                return response.data;
            } catch (e) {
                throw e;
            }
        },

        async updateFilters({ commit, dispatch }, filters) {
            commit('SET_FILTERS', filters);
            commit('SET_PAGINATION', { currentPage: 1 });
            await dispatch('fetchTasks');
        },

        async downloadAttachment({ commit }, { taskId, attachmentId, fileName }) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios({
                    url: `/v1/tasks/${taskId}/attachments/${attachmentId}/download`,
                    method: 'GET',
                    responseType: 'blob',
                });
                
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', fileName);
                document.body.appendChild(link);
                link.click();
    
                // Cleanup
                document.body.removeChild(link);
                window.URL.revokeObjectURL(url);
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Failed to download attachment');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },

        setPage({ commit }, page) {
            commit('SET_PAGINATION', { currentPage: page });
        }
    }
};