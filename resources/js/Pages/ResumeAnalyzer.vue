<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const resumeText = ref("");
const analyzing = ref(false);
const result = ref(null);
const error = ref(null);
const showJson = ref(false);

const characterCount = computed(() => resumeText.value.length);

const sampleResume = `John Doe
Software Engineer

Experience:
- Senior Developer at Tech Corp (2020-2023)
  Led a team of 5 developers, implemented microservices architecture
- Junior Developer at StartupXYZ (2018-2020)
  Developed RESTful APIs and frontend components

Skills: PHP, Laravel, JavaScript, Vue.js, React, Python, Docker, AWS

Education:
BS Computer Science, MIT, 2018`;

const analyzeResume = async () => {
    if (!resumeText.value.trim()) {
        error.value = "Please enter resume text";
        return;
    }

    analyzing.value = true;
    error.value = null;
    result.value = null;

    try {
        const response = await axios.post("/api/analyze-resume", {
            resume_text: resumeText.value,
        });

        if (response.data.success) {
            result.value = response.data.data;
        } else {
            error.value = "Failed to analyze resume";
        }
    } catch (err) {
        if (err.response?.data?.message) {
            error.value = err.response.data.message;
        } else if (err.response?.data?.error) {
            error.value = err.response.data.error;
        } else {
            error.value = "An error occurred while analyzing the resume";
        }
    } finally {
        analyzing.value = false;
    }
};

const clearForm = () => {
    resumeText.value = "";
    result.value = null;
    error.value = null;
    showJson.value = false;
};

const loadSample = () => {
    resumeText.value = sampleResume;
    result.value = null;
    error.value = null;
};

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(
            JSON.stringify(result.value, null, 2),
        );
        alert("Copied to clipboard!");
    } catch (err) {
        console.error("Failed to copy:", err);
    }
};
</script>

<template>
    <Head title="Inline Resume Analyzer" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Inline Resume Analyzer
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Input Section -->
                        <div class="mb-8">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="mb-4">
                                    <label
                                        for="resume"
                                        class="block text-lg font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        Resume Text
                                    </label>
                                    <p
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Paste resume text to
                                        <strong>extract structured data</strong>
                                        (Skills, Education, Experience). Useful
                                        for
                                        <strong
                                            >checking ATS readability</strong
                                        >
                                        or quickly parsing raw text into a
                                        standardized format.
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                                        @click="loadSample"
                                    >
                                        Load Sample
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
                                        @click="clearForm"
                                    >
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <textarea
                                id="resume"
                                v-model="resumeText"
                                rows="12"
                                class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 dark:bg-gray-700 dark:text-gray-100 dark:ring-gray-600 sm:text-sm sm:leading-6"
                                placeholder="Paste resume text here..."
                            ></textarea>

                            <div class="mt-2 flex items-center justify-between">
                                <span
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ characterCount }} characters
                                </span>
                                <button
                                    :disabled="analyzing || !resumeText.trim()"
                                    type="button"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:cursor-not-allowed disabled:opacity-50"
                                    @click="analyzeResume"
                                >
                                    <span
                                        v-if="analyzing"
                                        class="flex items-center gap-2"
                                    >
                                        <svg
                                            class="h-4 w-4 animate-spin"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        Analyzing...
                                    </span>
                                    <span v-else>Analyze Resume</span>
                                </button>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div
                            v-if="error"
                            class="mb-6 rounded-md bg-red-50 p-4 dark:bg-red-900/20"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-red-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p
                                        class="text-sm font-medium text-red-800 dark:text-red-200"
                                    >
                                        {{ error }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Results Section -->
                        <div v-if="result" class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    Analysis Results
                                </h3>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500"
                                        @click="showJson = !showJson"
                                    >
                                        {{
                                            showJson
                                                ? "Show Formatted"
                                                : "Show JSON"
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                                        @click="copyToClipboard"
                                    >
                                        Copy JSON
                                    </button>
                                </div>
                            </div>

                            <!-- JSON View -->
                            <div
                                v-if="showJson"
                                class="rounded-lg bg-gray-900 p-4"
                            >
                                <pre
                                    class="overflow-x-auto text-sm text-green-400"
                                    >{{ JSON.stringify(result, null, 2) }}</pre
                                >
                            </div>

                            <!-- Formatted View -->
                            <div v-else class="grid gap-6 md:grid-cols-2">
                                <!-- Personal Info -->
                                <div
                                    v-if="
                                        result.name ||
                                        result.Name ||
                                        result.job_title ||
                                        result.JobTitle
                                    "
                                    class="rounded-lg bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 dark:from-indigo-900/20 dark:to-indigo-800/20"
                                >
                                    <h4
                                        class="mb-4 text-lg font-semibold text-indigo-900 dark:text-indigo-200"
                                    >
                                        Personal Information
                                    </h4>
                                    <div class="space-y-2">
                                        <p
                                            v-if="result.name || result.Name"
                                            class="text-gray-700 dark:text-gray-300"
                                        >
                                            <span class="font-medium"
                                                >Name:</span
                                            >
                                            {{ result.name || result.Name }}
                                        </p>
                                        <p
                                            v-if="
                                                result.job_title ||
                                                result.JobTitle
                                            "
                                            class="text-gray-700 dark:text-gray-300"
                                        >
                                            <span class="font-medium"
                                                >Job Title:</span
                                            >
                                            {{
                                                result.job_title ||
                                                result.JobTitle
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Skills -->
                                <div
                                    v-if="result.skills || result.Skills"
                                    class="rounded-lg bg-gradient-to-br from-green-50 to-green-100 p-6 dark:from-green-900/20 dark:to-green-800/20"
                                >
                                    <h4
                                        class="mb-4 text-lg font-semibold text-green-900 dark:text-green-200"
                                    >
                                        Skills
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="(
                                                skill, index
                                            ) in result.skills || result.Skills"
                                            :key="index"
                                            class="rounded-full bg-green-200 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-800 dark:text-green-200"
                                        >
                                            {{ skill }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Experience -->
                                <div
                                    v-if="result.experience"
                                    class="rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 p-6 dark:from-purple-900/20 dark:to-purple-800/20 md:col-span-2"
                                >
                                    <h4
                                        class="mb-4 text-lg font-semibold text-purple-900 dark:text-purple-200"
                                    >
                                        Experience
                                    </h4>
                                    <div class="space-y-4">
                                        <div
                                            v-for="(
                                                exp, index
                                            ) in result.experience"
                                            :key="index"
                                            class="rounded-md bg-white/50 p-4 dark:bg-gray-800/50"
                                        >
                                            <p
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    exp.position || exp.company
                                                }}
                                            </p>
                                            <p
                                                v-if="
                                                    exp.company && exp.position
                                                "
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                {{ exp.company }}
                                            </p>
                                            <p
                                                v-if="exp.dates"
                                                class="text-sm text-gray-500 dark:text-gray-500"
                                            >
                                                {{ exp.dates }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Education -->
                                <div
                                    v-if="result.education"
                                    class="rounded-lg bg-gradient-to-br from-orange-50 to-orange-100 p-6 dark:from-orange-900/20 dark:to-orange-800/20 md:col-span-2"
                                >
                                    <h4
                                        class="mb-4 text-lg font-semibold text-orange-900 dark:text-orange-200"
                                    >
                                        Education
                                    </h4>
                                    <div class="space-y-4">
                                        <div
                                            v-for="(
                                                edu, index
                                            ) in result.education"
                                            :key="index"
                                            class="rounded-md bg-white/50 p-4 dark:bg-gray-800/50"
                                        >
                                            <p
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{ edu.degree }}
                                            </p>
                                            <p
                                                v-if="edu.institution"
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                {{ edu.institution }}
                                            </p>
                                            <p
                                                v-if="edu.year"
                                                class="text-sm text-gray-500 dark:text-gray-500"
                                            >
                                                {{ edu.year }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Raw Response (if AI didn't return structured data) -->
                                <div
                                    v-if="result.raw_response"
                                    class="rounded-lg bg-yellow-50 p-6 dark:bg-yellow-900/20 md:col-span-2"
                                >
                                    <h4
                                        class="mb-4 text-lg font-semibold text-yellow-900 dark:text-yellow-200"
                                    >
                                        Raw AI Response
                                    </h4>
                                    <p
                                        class="text-sm text-gray-700 dark:text-gray-300"
                                    >
                                        {{ result.raw_response }}
                                    </p>
                                    <p
                                        v-if="result.note"
                                        class="mt-2 text-xs text-yellow-700 dark:text-yellow-400"
                                    >
                                        {{ result.note }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
