<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link, usePage, router } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);

const page = usePage();
const notifications = computed(() => page.props.notifications || []);

// Global Toast State
const showGlobalToast = ref(false);
const globalToastMessage = ref("");
const globalToastType = ref("success");
const globalToastLink = ref(null);
const closeGlobalToast = () => {
    showGlobalToast.value = false;
    localStorage.removeItem("active_toast");
};

const processedNotificationIds = ref(
    new Set(JSON.parse(localStorage.getItem("processed_toasts") || "[]")),
);

const saveProcessedId = (id) => {
    processedNotificationIds.value.add(id);
    localStorage.setItem(
        "processed_toasts",
        JSON.stringify([...processedNotificationIds.value]),
    );
};

let notificationInterval = null;

onMounted(() => {
    // Restore active toast if any
    const activeToast = JSON.parse(localStorage.getItem("active_toast"));
    if (activeToast) {
        globalToastMessage.value = activeToast.message;
        globalToastType.value = activeToast.type;
        globalToastLink.value = activeToast.link;
        showGlobalToast.value = true;
    }

    // Capture existing notifications as processed ONLY on the very first visit
    // if the set is empty. Otherwise, we might miss them during navigation.
    if (processedNotificationIds.value.size === 0) {
        notifications.value.forEach((n) => saveProcessedId(n.id));
    }

    // Poll for new notifications every 15 seconds
    notificationInterval = setInterval(() => {
        router.reload({
            only: ["notifications"],
            preserveScroll: true,
            preserveState: true,
        });
    }, 15000);
});

onUnmounted(() => {
    if (notificationInterval) clearInterval(notificationInterval);
});

// Watch for new notifications
watch(
    notifications,
    (newVal) => {
        newVal.forEach((n) => {
            if (!processedNotificationIds.value.has(n.id)) {
                saveProcessedId(n.id);

                // Trigger Global Toast
                globalToastMessage.value = n.data.message;
                globalToastType.value =
                    n.data.status === "failed" ? "error" : "success";
                globalToastLink.value = route(
                    "resume-analyses.show",
                    n.data.analysis_id,
                );
                showGlobalToast.value = true;

                // Persist the active toast content
                localStorage.setItem(
                    "active_toast",
                    JSON.stringify({
                        message: globalToastMessage.value,
                        type: globalToastType.value,
                        link: globalToastLink.value,
                    }),
                );
            }
        });
    },
    { deep: true },
);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-gray-100 bg-white">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <!-- <NavLink
                                    :href="route('resume-analyzer')"
                                    :active="route().current('resume-analyzer')"
                                >
                                    Inline Resume Analyzer
                                </NavLink> -->
                                <NavLink
                                    :href="route('job-descriptions.index')"
                                    :active="
                                        route().current('job-descriptions.*')
                                    "
                                >
                                    Job Descriptions
                                </NavLink>
                                <NavLink
                                    :href="route('resume-matching')"
                                    :active="route().current('resume-matching')"
                                >
                                    Resume Matching
                                </NavLink>
                                <NavLink
                                    :href="route('resume-analyses.index')"
                                    :active="
                                        route().current('resume-analyses.*')
                                    "
                                >
                                    Resume Analyses
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{
                                                    $page.props.auth?.user?.name
                                                }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <!-- <ResponsiveNavLink
                            :href="route('resume-analyzer')"
                            :active="route().current('resume-analyzer')"
                        >
                            Resume Analyzer
                        </ResponsiveNavLink> -->
                        <ResponsiveNavLink
                            :href="route('job-descriptions.index')"
                            :active="route().current('job-descriptions.*')"
                        >
                            Job Descriptions
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('resume-matching')"
                            :active="route().current('resume-matching')"
                        >
                            Resume Matching
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('resume-analyses.index')"
                            :active="route().current('resume-analyses.*')"
                        >
                            Resume Analyses
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth?.user?.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth?.user?.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Global Toast Notification -->
        <Teleport to="body">
            <div
                v-if="showGlobalToast"
                class="fixed right-4 top-4 z-[9999] transform transition-all duration-300 ease-in-out"
            >
                <div
                    :class="[
                        'flex w-80 items-start space-x-4 rounded-lg p-4 shadow-2xl border-l-4',
                        globalToastType === 'success'
                            ? 'bg-white dark:bg-gray-800 border-green-500'
                            : 'bg-white dark:bg-gray-800 border-red-500',
                    ]"
                >
                    <div class="flex-shrink-0">
                        <svg
                            v-if="globalToastType === 'success'"
                            class="h-6 w-6 text-green-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6 text-red-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p
                            class="text-sm font-bold text-gray-900 dark:text-white"
                        >
                            {{
                                globalToastType === "success"
                                    ? "Success"
                                    : "Error"
                            }}
                        </p>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-300"
                        >
                            {{ globalToastMessage }}
                        </p>
                        <div class="mt-3">
                            <Link
                                :href="globalToastLink"
                                class="text-sm font-bold text-indigo-600 hover:text-indigo-500 underline"
                                @click="closeGlobalToast"
                            >
                                View Results
                            </Link>
                        </div>
                    </div>
                    <button
                        class="flex-shrink-0 text-gray-400 hover:text-gray-500 focus:outline-none"
                        @click="closeGlobalToast"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </Teleport>
    </div>
</template>
