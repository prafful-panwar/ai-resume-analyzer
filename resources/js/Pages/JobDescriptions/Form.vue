<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps({
    jobDescription: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => !!props.jobDescription);

const requirementsText = ref(
    props.jobDescription?.requirements?.join(", ") || "",
);

const form = useForm({
    job_role: props.jobDescription?.job_role || "",
    experience_min: props.jobDescription?.experience_min || 0,
    experience_max: props.jobDescription?.experience_max || 0,
    description: props.jobDescription?.description || "",
    requirements: props.jobDescription?.requirements || [],
});

const saveJobDescription = () => {
    // Parse requirements from comma-separated text
    const requirements = requirementsText.value
        .split(",")
        .map((r) => r.trim())
        .filter((r) => r.length > 0);

    form.requirements = requirements;

    if (isEdit.value) {
        form.put(`/job-descriptions/${props.jobDescription.id}`);
    } else {
        form.post("/job-descriptions");
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Job Description' : 'Create Job Description'" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ isEdit ? "Edit Job Description" : "Create Job Description" }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6">
                        <!-- Error Message -->
                        <div
                            v-if="form.hasErrors"
                            class="mb-6 rounded-md bg-red-50 p-4 dark:bg-red-900/20"
                        >
                            <p
                                class="text-sm font-medium text-red-800 dark:text-red-200"
                            >
                                Please fix the errors below
                            </p>
                        </div>

                        <form
                            class="space-y-6"
                            @submit.prevent="saveJobDescription"
                        >
                            <!-- Job Role -->
                            <div>
                                <label
                                    for="job_role"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Job Role *
                                </label>
                                <input
                                    id="job_role"
                                    v-model="form.job_role"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    placeholder="e.g., Senior Laravel Developer"
                                />
                                <p
                                    v-if="form.errors.job_role"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.job_role }}
                                </p>
                            </div>

                            <!-- Experience Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        for="experience_min"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Min Experience (years) *
                                    </label>
                                    <input
                                        id="experience_min"
                                        v-model.number="form.experience_min"
                                        type="number"
                                        min="0"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    />
                                    <p
                                        v-if="form.errors.experience_min"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.experience_min }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="experience_max"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Max Experience (years) *
                                    </label>
                                    <input
                                        id="experience_max"
                                        v-model.number="form.experience_max"
                                        type="number"
                                        :min="form.experience_min"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    />
                                    <p
                                        v-if="form.errors.experience_max"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ form.errors.experience_max }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Job Description *
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="8"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    placeholder="Enter the full job description..."
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- Requirements/Skills -->
                            <div>
                                <label
                                    for="requirements"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >
                                    Skills/Requirements (comma-separated)
                                </label>
                                <input
                                    id="requirements"
                                    v-model="requirementsText"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                    placeholder="e.g., PHP, Laravel, Vue.js, MySQL, Docker"
                                />
                                <p
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Separate skills with commas
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-3">
                                <a
                                    href="/job-descriptions"
                                    class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing"
                                        >Saving...</span
                                    >
                                    <span v-else
                                        >{{ isEdit ? "Update" : "Create" }} Job
                                        Description</span
                                    >
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
