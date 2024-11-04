<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          {{ isLogin ? 'Sign in to your account' : 'Create your account' }}
        </h2>
      </div>
      
      <div v-if="error" class="rounded-md bg-red-50 p-4">
        <div class="flex">
          <div class="text-sm text-red-700">
            {{ error }}
          </div>
        </div>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm -space-y-px">
          <div v-if="!isLogin">
            <label for="name" class="sr-only">Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="Full name"
            />
          </div>

          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              :class="`appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 ${!isLogin && 'rounded-none'} ${isLogin && 'rounded-t-md'} focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm`"
              placeholder="Email address"
            />
          </div>

          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              :placeholder="isLogin ? 'Password' : 'Create password'"
            />
          </div>
        </div>

        <div class="space-y-3">
          <button
            type="submit"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <LockClosedIcon class="h-5 w-5 text-blue-500 group-hover:text-blue-400" aria-hidden="true" />
            </span>
            {{ isLoading ? 'Please wait...' : (isLogin ? 'Sign in' : 'Sign up') }}
          </button>

          <button
            v-if="isLogin"
            type="button"
            @click="handleGuestLogin"
            :disabled="isLoading"
            class="relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            Continue as Guest
          </button>
        </div>

        <div class="text-center text-sm">
          <span class="text-gray-500">
            {{ isLogin ? "Don't have an account?" : 'Already have an account?' }}
          </span>
          <button
            type="button"
            @click="toggleMode"
            class="ml-1 font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition ease-in-out duration-150"
          >
            {{ isLogin ? 'Sign up' : 'Sign in' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { LockClosedIcon } from '@heroicons/vue/20/solid';

const store = useStore();
const router = useRouter();

const isLogin = ref(true);
const isLoading = ref(false);
const error = ref('');

const form = ref({
  name: '',
  email: '',
  password: ''
});

const resetForm = () => {
  form.value = {
    name: '',
    email: '',
    password: ''
  };
  error.value = '';
};

const toggleMode = () => {
  isLogin.value = !isLogin.value;
  resetForm();
};

const handleSubmit = async () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  error.value = '';

  try {
    if (isLogin.value) {
      await store.dispatch('auth/login', form.value);
    } else {
      await store.dispatch('auth/register', form.value);
    }
    router.push('/');
  } catch (err) {
    error.value = err.response?.data?.message || `Failed to ${isLogin.value ? 'sign in' : 'sign up'}`;
  } finally {
    isLoading.value = false;
  }
};

const handleGuestLogin = async () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  error.value = '';
  
  try {
    await store.dispatch('auth/loginAsGuest');
    router.push('/');
  } catch (err) {
    error.value = 'Guest login failed. Please try again.';
  } finally {
    isLoading.value = false;
  }
};
</script>