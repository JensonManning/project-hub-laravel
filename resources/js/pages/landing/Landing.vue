<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Project Hub" />
  <div class="min-h-screen bg-neutral-950 relative overflow-hidden">
    <!-- Top Navigation -->
    <header class="relative z-20 py-4">
      <div class="container mx-auto px-6">
        <div class="flex items-center justify-between">
          <div class="text-white opacity-0 text-xl font-bold">Project Hub</div>
          <nav class="flex items-center justify-end gap-4">
            <Link
              v-if="$page.props.auth.user"
              :href="route('dashboard')"
              class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-white hover:border-[#7e25254a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
            >
              {{ $page.props.auth.user.name }}
            </Link>
            <Link
              v-else
              :href="route('login')"
              class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-white hover:border-[#75202035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
            >
              Log in
            </Link>
            <Link
              :href="route('register')"
              class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-white hover:border-[#8128284a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
            >
              Register
            </Link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Particle Animation -->
    <svg class="fixed inset-0 w-full h-full opacity-60 pointer-events-none z-0" viewBox="0 0 100 100" preserveAspectRatio="none">
      <!-- Individual Particles -->
      <g>
        <!-- Left to Right Particles -->
        <g v-for="i in 200" :key="`particle-lr-${i}`">
          <circle 
            :cx="`${Math.random() * 100}`"
            :cy="`${Math.random() * 100}`"
            :r="0.03 + Math.random() * 0.07"
            :fill="`rgba(220, 220, 255, ${0.2 + Math.random() * 0.5})`"
            stroke-width="0">
            <animate
              attributeName="cx"
              :from="`${Math.random() * -10}`"
              to="110"
              :dur="`${40 + Math.random() * 60}s`"
              repeatCount="indefinite"
              :begin="`${Math.random() * -60}s`"
            />
            <animate
              attributeName="cy"
              :values="`${Math.random() * 100};${Math.random() * 100 - 2};${Math.random() * 100 + 2};${Math.random() * 100}`"
              :dur="`${80 + Math.random() * 40}s`"
              repeatCount="indefinite"
              :begin="`${Math.random() * -40}s`"
            />
            <animate
              attributeName="opacity"
              :values="`${Math.random() * 0.3};${0.4 + Math.random() * 0.6};${Math.random() * 0.3}`"
              :dur="`${40 + Math.random() * 30}s`"
              repeatCount="indefinite"
              :begin="`${Math.random() * -20}s`"
            />
          </circle>
        </g>
        
        <!-- Right to Left Particles -->
        <g v-for="i in 200" :key="`particle-rl-${i}`">
          <circle 
            :cx="`${Math.random() * 100}`"
            :cy="`${Math.random() * 100}`"
            :r="0.03 + Math.random() * 0.07"
            :fill="`rgba(220, 220, 255, ${0.2 + Math.random() * 0.5})`"
            stroke-width="0">
            <animate
              attributeName="cx"
              from="110"
              :to="`${Math.random() * -10}`"
              :dur="`${40 + Math.random() * 60}s`"
              repeatCount="indefinite"
              :begin="`${Math.random() * -60}s`"
            />
            <animate
              attributeName="cy"
              :values="`${Math.random() * 100};${Math.random() * 100 - 2};${Math.random() * 100 + 2};${Math.random() * 100}`"
              :dur="`${80 + Math.random() * 40}s`"
              repeatCount="indefinite"
              :begin="`${Math.random() * -40}s`"
            />
            <animate
              attributeName="opacity"
              :values="`${Math.random() * 0.3};${0.4 + Math.random() * 0.6};${Math.random() * 0.3}`"
              :dur="`${40 + Math.random() * 30}s`"
              repeatCount="indefinite"
              :begin="`${Math.random() * -20}s`"
            />
          </circle>
        </g>
      </g>

      <!-- Noise Overlay for Texture -->
      <filter id="noise">
        <feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="3" />
        <feColorMatrix type="matrix" values="1 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
      </filter>
      <rect width="100%" height="100%" filter="url(#noise)" class="opacity-5"/>
    </svg>

    <!-- Hero Section -->
    <section class="relative h-screen z-10">
      <div class="container mx-auto px-6 text-center h-full flex flex-col justify-center relative">
        <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
          Project Hub
          <span class="block text-4xl md:text-5xl mt-2 bg-gradient-to-r from-red-400 to-red-800 bg-clip-text text-transparent">
            Next generation project management
          </span>
        </h1>
        <p class="text-xl text-blue-200 mb-8 max-w-2xl mx-auto">
          The all-in-one project management solution that drives efficiency and productivity.
        </p>
        <div class="space-x-4">
          <Link
            :href="route('register')"
            class="bg-red-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-800 transition-colors shadow-lg hover:shadow-red-700/30"
          >
            Get Started
          </Link>
          <Link
            :href="route('login')"
            class="border border-red-400 text-red-300 px-8 py-3 rounded-lg font-semibold hover:bg-red-900/30 transition-colors"
          >
            Learn More
          </Link>
        </div>
      </div>
    </section>

    <!-- Feature Cards Section -->
    <section class="relative py-20 z-10 bg-zinc-900/50 backdrop-blur-sm">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-center text-white mb-12">Powerful Features for Project Management</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Card 1: Get More Clients -->
          <div class="p-8 bg-neutral-800/70 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:translate-y-[-5px] backdrop-blur-sm">
            <div class="text-red-800 mb-4">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Get More Clients</h3>
            <p class="text-neutral-300">Gain more clients when you have a project management system that helps you manage your projects with ease.</p>
            <ul class="mt-4 text-neutral-400">
              <li class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Customized Project Creation
              </li>
              <li class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Repositories for total customization
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Simple, yet verbose interface
              </li>
            </ul>
          </div>

          <!-- Card 2: Schedule Services -->
          <div class="p-8 bg-neutral-800/70 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:translate-y-[-5px] backdrop-blur-sm">
            <div class="text-red-800 mb-4">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Services</h3>
            <p class="text-neutral-300">From the Project Manager to all other team members utilize the same tools with the right views to enhance project overview.</p>
            <ul class="mt-4 text-neutral-400">
              <li class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Custom built project flow
              </li>
              <li class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Task interface that allows you to assign tasks to team members, comment, and stay on track with ease.
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Integrate with your calendar to be able to schedule tasks and appointments.
              </li>
            </ul>
          </div>

          <!-- Card 3: Approve or Deny Requests -->
          <div class="p-8 bg-neutral-800/70 rounded-xl shadow-xl hover:shadow-2xl transition-all hover:translate-y-[-5px] backdrop-blur-sm">
            <div class="text-red-800 mb-4">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Sales to Project Completion</h3>
            <p class="text-neutral-300">Stay in control with the right tools from start to finish.</p>
            <ul class="mt-4 text-neutral-400">
              <li class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Sectioned out notifications
              </li>
              <li class="flex items-center mb-2">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Complete project overview
              </li>
              <li class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-800" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Powerful charts for higher management.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="relative z-10 py-24">
      <div class="container mx-auto px-6">
        <div class="text-center mb-20">
          <h2 class="text-4xl font-bold text-white mb-4">Powerful Dashboard Features</h2>
          <p class="text-xl text-red-200 max-w-3xl mx-auto">
            Everything you need to manage your business, all in one place.
          </p>
        </div>

        <!-- Feature 1: Customer Management -->
        <div class="flex flex-col md:flex-row items-center mb-32 relative">
          <!-- Feature Content -->
          <div class="md:w-1/2 md:pr-12 mb-10 md:mb-0 z-10">
            <div class="bg-red-800/10 backdrop-blur-sm p-2 rounded-full inline-flex mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <h3 class="text-3xl font-bold text-white mb-4">Customer Management</h3>
            <p class="text-xl text-red-200 mb-6">
              Keep track of all your clients in one place. Store contact information, appointment history, and preferences to provide personalized service.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Detailed client profiles with custom fields</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Appointment history and preferences tracking</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Automated communication and follow-ups</span>
              </li>
            </ul>
          </div>
          
          <!-- Feature Image -->
          <div class="md:w-1/2 relative z-10">
            <div class="bg-gradient-to-br from-red-800/20 to-red-800/20 backdrop-blur-sm rounded-2xl p-2 shadow-xl">
              <div class="bg-neutral-900 rounded-xl overflow-hidden">
                <img src="https://placehold.co/800x600/1a1a2e/FFFFFF?text=Customer+Dashboard" alt="Customer Management Dashboard" class="w-full h-auto" />
              </div>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-red-700/20 rounded-full blur-xl"></div>
            <div class="absolute -top-6 -left-6 w-32 h-32 bg-red-800/20 rounded-full blur-xl"></div>
          </div>
        </div>

        <!-- Feature 2: Service Management -->
        <div class="flex flex-col md:flex-row-reverse items-center mb-32 relative">
          <!-- Feature Content -->
          <div class="md:w-1/2 md:pl-12 mb-10 md:mb-0 z-10">
            <div class="bg-red-800/10 backdrop-blur-sm p-2 rounded-full inline-flex mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 11-4 0 2 2 0 014 0" />
              </svg>
            </div>
            <h3 class="text-3xl font-bold text-white mb-4">Service Management</h3>
            <p class="text-xl text-red-200 mb-6">
              Create and customize your service offerings with detailed descriptions, pricing, and duration. Make it easy for clients to book exactly what they need.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-700 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Flexible service categories and packages</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-700 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Dynamic pricing options and special offers</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-700 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Service availability scheduling and capacity limits</span>
              </li>
            </ul>
          </div>
          
          <!-- Feature Image -->
          <div class="md:w-1/2 relative z-10">
            <div class="bg-gradient-to-br from-red-800/20 to-red-900/20 backdrop-blur-sm rounded-2xl p-2 shadow-xl">
              <div class="bg-neutral-900 rounded-xl overflow-hidden">
                <img src="https://placehold.co/800x600/1a1a2e/FFFFFF?text=Services+Dashboard" alt="Service Management Dashboard" class="w-full h-auto" />
              </div>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-red-800/20 rounded-full blur-xl"></div>
            <div class="absolute -top-6 -right-6 w-32 h-32 bg-red-800/20 rounded-full blur-xl"></div>
          </div>
        </div>

        <!-- Feature 3: Business Management -->
        <div class="flex flex-col md:flex-row items-center mb-32 relative">
          <!-- Feature Content -->
          <div class="md:w-1/2 md:pr-12 mb-10 md:mb-0 z-10">
            <div class="bg-red-800/10 backdrop-blur-sm p-2 rounded-full inline-flex mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <h3 class="text-3xl font-bold text-white mb-4">Business Management</h3>
            <p class="text-xl text-red-200 mb-6">
              Take control of your business operations with powerful analytics, reporting, and financial tools designed to help you make informed decisions.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Real-time business analytics and reporting</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Revenue tracking and financial management</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Staff management and performance metrics</span>
              </li>
            </ul>
          </div>
          
          <!-- Feature Image -->
          <div class="md:w-1/2 relative z-10">
            <div class="bg-gradient-to-br from-red-800/20 to-red-900/20 backdrop-blur-sm bg-opacity-5 rounded-2xl p-2 shadow-xl">
              <div class="bg-neutral-900 rounded-xl overflow-hidden">
                <img src="https://placehold.co/800x600/1a1a2e/FFFFFF?text=Business+Dashboard" alt="Business Management Dashboard" class="w-full h-auto" />
              </div>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-red-800/20 rounded-full blur-xl"></div>
            <div class="absolute -top-6 -left-6 w-32 h-32 bg-red-800/20 rounded-full blur-xl"></div>
          </div>
        </div>

        <!-- Feature 4: Appointment Management -->
        <div class="flex flex-col md:flex-row-reverse items-center relative">
          <!-- Feature Content -->
          <div class="md:w-1/2 md:pl-12 mb-10 md:mb-0 z-10">
            <div class="bg-red-900/10 backdrop-blur-sm p-2 rounded-full inline-flex mb-6">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 class="text-3xl font-bold text-white mb-4">Appointment Management</h3>
            <p class="text-xl text-red-200 mb-6">
              Streamline your scheduling process with an intuitive calendar interface. Manage bookings, reschedules, and cancellations with ease.
            </p>
            <ul class="space-y-3">
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Drag-and-drop calendar with multiple views</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Automated reminders and notifications</span>
              </li>
              <li class="flex items-start">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-800 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-neutral-300">Online booking with customizable availability</span>
              </li>
            </ul>
          </div>
          
          <!-- Feature Image -->
          <div class="md:w-1/2 relative z-10">
            <div class="bg-gradient-to-br from-red-900/20 to-red-800/20 backdrop-blur-sm rounded-2xl p-2 shadow-xl">
              <div class="bg-neutral-900 rounded-xl overflow-hidden">
                <img src="https://placehold.co/800x600/1a1a2e/FFFFFF?text=Appointments+Dashboard" alt="Appointment Management Dashboard" class="w-full h-auto" />
              </div>
            </div>
            <!-- Decorative Elements -->
            <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-red-800/20 rounded-full blur-xl"></div>
            <div class="absolute -top-6 -right-6 w-32 h-32 bg-red-900/20 rounded-full blur-xl"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-20 z-10">
      <div class="container mx-auto px-6 text-center">
        <div class="max-w-3xl mx-auto bg-gradient-to-r from-red-900/60 to-red-800/60 p-10 rounded-2xl backdrop-blur-sm shadow-2xl">
          <h2 class="text-3xl font-bold text-white mb-4">Ready to streamline projects?</h2>
          <p class="text-red-200 mb-8">Join Project Hub and start working faster and more efficient.</p>
          <button class="bg-white text-black px-8 py-3 rounded-lg font-semibold hover:bg-red-50 transition-colors shadow-lg">
            Register
          </button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 py-12 bg-neutral-900/60 backdrop-blur-sm mt-12">
      <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="mb-8 md:mb-0">
            <h3 class="text-2xl font-bold text-white mb-2">Project Hub</h3>
            <p class="text-red-200">Ultimate project management</p>
          </div>
          <div class="flex space-x-6">
            <a href="#" class="text-red-300 hover:text-white transition-colors">
              <span class="sr-only">Twitter</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
              </svg>
            </a>
            <a href="#" class="text-red-300 hover:text-white transition-colors">
              <span class="sr-only">GitHub</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
              </svg>
            </a>
            <a href="#" class="text-red-300 hover:text-white transition-colors">
              <span class="sr-only">LinkedIn</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
        <div class="border-t border-neutral-800 mt-8 pt-8 text-center">
          <p class="text-neutral-400">2025 Project Hub</p>
        </div>
      </div>
    </footer>
  </div>

</template>