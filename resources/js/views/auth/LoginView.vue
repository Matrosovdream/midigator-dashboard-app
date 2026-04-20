<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { useAuthStore } from '@/stores/auth';

const email = ref('');
const password = ref('');
const remember = ref(false);
const submitting = ref(false);

const auth = useAuthStore();
const router = useRouter();

async function onSubmit() {
    if (submitting.value) return;
    submitting.value = true;
    try {
        await auth.login(email.value, password.value);
        await router.push({ name: 'dashboard' });
    } catch {
        // error is exposed via auth.error
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <FloatingConfigurator />
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen min-w-[100vw] overflow-hidden">
        <div class="flex flex-col items-center justify-center">
            <div style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-20 px-8 sm:px-20" style="border-radius: 53px">
                    <div class="text-center mb-8">
                        <div class="text-surface-900 dark:text-surface-0 text-3xl font-medium mb-4">Welcome to Midigator</div>
                        <span class="text-muted-color font-medium">Sign in to continue</span>
                    </div>

                    <form @submit.prevent="onSubmit">
                        <label for="email1" class="block text-surface-900 dark:text-surface-0 text-xl font-medium mb-2">Email</label>
                        <InputText id="email1" type="email" placeholder="Email address" class="w-full md:w-[30rem] mb-8" v-model="email" autocomplete="username" required />

                        <label for="password1" class="block text-surface-900 dark:text-surface-0 font-medium text-xl mb-2">Password</label>
                        <Password id="password1" v-model="password" placeholder="Password" :toggleMask="true" class="mb-4" fluid :feedback="false" inputClass="w-full" autocomplete="current-password" required />

                        <div class="flex items-center justify-between mt-2 mb-8 gap-8">
                            <div class="flex items-center">
                                <Checkbox v-model="remember" id="rememberme1" binary class="mr-2" />
                                <label for="rememberme1">Remember me</label>
                            </div>
                            <span class="font-medium ml-2 text-right cursor-pointer text-primary">Forgot password?</span>
                        </div>

                        <Message v-if="auth.error" severity="error" :closable="false" class="mb-4">{{ auth.error }}</Message>

                        <Button type="submit" :label="submitting ? 'Signing in…' : 'Sign In'" class="w-full" :loading="submitting" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
