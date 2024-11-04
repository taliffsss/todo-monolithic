import axios from 'axios';

export default {
    namespaced: true,

    state: {
        tags: [],
        loading: false,
        error: null
    },

    getters: {
        allTags: state => state.tags,
        loading: state => state.loading,
        error: state => state.error,
        getTagById: state => id => state.tags.find(tag => tag.id === id)
    },

    mutations: {
        SET_TAGS(state, tags) {
            state.tags = tags;
        },
        ADD_TAG(state, tag) {
            const exists = state.tags.find(t => t.id === tag.id);
            if (!exists) {
                state.tags.push(tag);
            }
        },
        UPDATE_TAG(state, updatedTag) {
            const index = state.tags.findIndex(tag => tag.id === updatedTag.id);
            if (index !== -1) {
                state.tags.splice(index, 1, updatedTag);
            }
        },
        REMOVE_TAG(state, tagId) {
            state.tags = state.tags.filter(tag => tag.id !== tagId);
        },
        SET_LOADING(state, status) {
            state.loading = status;
        },
        SET_ERROR(state, error) {
            state.error = error;
        }
    },

    actions: {
        async fetchTags({ commit }) {
            commit('SET_LOADING', true);
            commit('SET_ERROR', null);
            try {
                const response = await axios.get('/v1/tags');
                commit('SET_TAGS', response.data.data);
                return response.data.data;
            } catch (e) {
                commit('SET_ERROR', e.response?.data?.message || 'Failed to fetch tags');
                throw e;
            } finally {
                commit('SET_LOADING', false);
            }
        },
    }
};