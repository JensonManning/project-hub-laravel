<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, onBeforeUnmount } from 'vue';
import { CheckCircle, ArrowLeft } from 'lucide-vue-next';

const countdown = ref(15);
const timer = ref<number | null>(null);

onMounted(() => {
    // Start countdown timer
    timer.value = window.setInterval(() => {
        countdown.value--;
        
        if (countdown.value <= 0) {
            if (timer.value) {
                clearInterval(timer.value);
            }
            window.location.href = route('login');
        }
    }, 1000);
});

onBeforeUnmount(() => {
    if (timer.value) {
        clearInterval(timer.value);
    }
});
</script>

<template>
    <AuthBase title="Registration Successful" description="Your account is pending approval">
        <Head title="Registration Successful" />

        <div class="flex flex-col items-center justify-center gap-6 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 text-primary">
                <CheckCircle class="h-10 w-10" />
            </div>
            
            <div class="space-y-2">
                <h2 class="text-xl font-semibold text-foreground">Thank you for registering!</h2>
                <p class="text-muted-foreground">
                    An administrator has been notified and will review your account shortly.
                    You will receive an email once your account has been approved.
                </p>
            </div>
            
            <div class="glass-card w-full rounded-lg p-4">
                <p class="text-sm text-muted-foreground">
                    Redirecting to login page in <span class="font-semibold text-primary">{{ countdown }}</span> seconds...
                </p>
            </div>
            
            <Button as-child class="w-full futuristic-glow mt-2">
                <Link :href="route('login')" class="flex w-full items-center justify-center">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Return to Login
                </Link>
            </Button>
        </div>
    </AuthBase>
</template>
