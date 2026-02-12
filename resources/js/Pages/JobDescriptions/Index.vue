<script setup>
import { ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";

defineProps({
    jobDescriptions: {
        type: Array,
        default: () => [],
    },
});

const showDeleteConfirm = ref(false);
const deleteId = ref(null);

const confirmDelete = (id) => {
    deleteId.value = id;
    showDeleteConfirm.value = true;
};

const deleteJobDescription = () => {
    router.delete(`/job-descriptions/${deleteId.value}`, {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteId.value = null;
        },
    });
};

const useForMatching = (jdId) => {
    router.visit(route("resume-matching", { jd: jdId }));
};
</script>

<template>
    <Head title="Job Descriptions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                >
                    Job Descriptions
                </h2>
                <a
                    href="/job-descriptions/create"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    Create New JD
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div
                    v-if="jobDescriptions.length === 0"
                    class="text-center py-12"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3
                        class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                    >
                        No job descriptions
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Get started by creating a new job description.
                    </p>
                    <div class="mt-6">
                        <a
                            href="/job-descriptions/create"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                        >
                            Create New JD
                        </a>
                    </div>
                </div>

                <!-- Job Descriptions List -->
                <div
                    v-else
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="jd in jobDescriptions"
                            :key="jd.id"
                            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ jd.job_role }}
                                    </h3>
                                    <p
                                        class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        Experience: {{ jd.experience_min }}-{{
                                            jd.experience_max
                                        }}
                                        years
                                    </p>
                                    <p
                                        class="mt-2 text-sm text-gray-700 dark:text-gray-300 line-clamp-2"
                                    >
                                        {{ jd.description }}
                                    </p>
                                    <p
                                        class="mt-2 text-xs text-gray-500 dark:text-gray-500"
                                    >
                                        Created
                                        {{
                                            new Date(
                                                jd.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div class="ml-4 flex flex-col gap-2">
                                    <button
                                        class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                                        @click="useForMatching(jd.id)"
                                    >
                                        Use for Matching
                                    </button>
                                    <a
                                        :href="`/job-descriptions/${jd.id}/edit`"
                                        class="rounded-md bg-gray-600 px-3 py-1.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500"
                                    >
                                        Edit
                                    </a>
                                    <button
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                        @click="confirmDelete(jd.id)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div
                    v-if="showDeleteConfirm"
                    class="fixed inset-0 z-50 overflow-y-auto"
                >
                    <div
                        class="flex min-h-screen items-center justify-center p-4"
                    >
                        <div
                            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                            @click="showDeleteConfirm = false"
                        ></div>
                        <div
                            class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                        >
                            <div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3
                                        class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100"
                                    >
                                        Delete Job Description
                                    </h3>
                                    <div class="mt-2">
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Are you sure you want to delete this
                                            job description? This action cannot
                                            be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                            >
                                <button
                                    type="button"
                                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:col-start-2"
                                    @click="deleteJobDescription"
                                >
                                    Delete
                                </button>
                                <button
                                    type="button"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:col-start-1 sm:mt-0"
                                    @click="showDeleteConfirm = false"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
