<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import {
    formatRecommendation,
    getScoreColorClasses,
    getRecommendationBadgeClasses,
} from "@/Utils/analysis";
import SkillChipList from "@/Components/SkillChipList.vue";

const props = defineProps({
    jobDescriptions: {
        type: Array,
        default: () => [],
    },
});

const selectedJdId = ref(null);
const resumeFile = ref(null);
const analyzing = ref(false);
const result = ref(null);
const error = ref(null);
const fileInput = ref(null);
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const jdId = urlParams.get("jd");
    if (jdId && props.jobDescriptions.find((jd) => jd.id === parseInt(jdId))) {
        selectedJdId.value = jdId;
    }
});

const selectedJd = computed(() => {
    if (!selectedJdId.value) return null;
    return props.jobDescriptions.find(
        (jd) => jd.id === parseInt(selectedJdId.value),
    );
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type !== "application/pdf") {
            error.value = "Please upload a PDF file";
            resumeFile.value = null;
            return;
        }
        resumeFile.value = file;
        error.value = null;
    }
};

const analyzeResume = () => {
    if (!resumeFile.value || !selectedJdId.value) {
        error.value = "Please select a job description and upload a resume";
        return;
    }

    analyzing.value = true;
    error.value = null;
    result.value = null;

    router.post(
        route("resume-matching.analyze"),
        {
            resume: resumeFile.value,
            job_description_id: selectedJdId.value,
        },
        {
            forceFormData: true,
            onSuccess: () => {
                analyzing.value = false;
            },
            onError: (errors) => {
                analyzing.value = false;
                error.value =
                    errors.resume ||
                    errors.job_description_id ||
                    "Failed to analyze resume";
            },
        },
    );
};

const clearForm = () => {
    resumeFile.value = null;
    result.value = null;
    error.value = null;
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const getMatchScoreColor = (score) => {
    return getScoreColorClasses(score).text;
};

const getRecommendationBadge = (recommendation) => {
    return getRecommendationBadgeClasses(recommendation);
};

const getRecommendationText = (recommendation) => {
    return formatRecommendation(recommendation);
};
</script>

<template>
    <Head title="Resume Matching" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Resume Matching with Job Description
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-8 space-y-6">
                            <div>
                                <label
                                    for="jd-select"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Select Job Description *
                                </label>
                                <select
                                    id="jd-select"
                                    v-model="selectedJdId"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                                >
                                    <option :value="null">
                                        -- Select a Job Description --
                                    </option>
                                    <option
                                        v-for="jd in jobDescriptions"
                                        :key="jd.id"
                                        :value="jd.id"
                                    >
                                        {{ jd.job_role }} ({{
                                            jd.experience_min
                                        }}-{{ jd.experience_max }} years)
                                    </option>
                                </select>
                                <p
                                    v-if="jobDescriptions.length === 0"
                                    class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    No job descriptions found.
                                    <a
                                        :href="route('job-descriptions.create')"
                                        class="text-indigo-600 hover:text-indigo-500"
                                        >Create one first</a
                                    >.
                                </p>
                            </div>
                            <div
                                v-if="selectedJd"
                                class="rounded-md bg-indigo-50 p-4 dark:bg-indigo-900/20"
                            >
                                <h3
                                    class="text-sm font-semibold text-indigo-800 dark:text-indigo-200"
                                >
                                    {{ selectedJd.job_role }}
                                </h3>
                                <p
                                    class="mt-1 text-sm text-indigo-700 dark:text-indigo-300"
                                >
                                    Experience:
                                    {{ selectedJd.experience_min }}-{{
                                        selectedJd.experience_max
                                    }}
                                    years
                                </p>
                                <p
                                    class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 line-clamp-3"
                                >
                                    {{ selectedJd.description }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                >
                                    Upload Resume (PDF) *
                                </label>
                                <div class="flex items-center gap-4">
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".pdf"
                                        class="block w-full text-sm text-gray-900 dark:text-gray-100 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/50 dark:file:text-indigo-300"
                                        @change="handleFileChange"
                                    />
                                    <button
                                        v-if="resumeFile"
                                        type="button"
                                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                        @click="clearForm"
                                    >
                                        Clear
                                    </button>
                                </div>
                                <p
                                    v-if="resumeFile"
                                    class="mt-2 text-sm text-green-600 dark:text-green-400"
                                >
                                    ✓ {{ resumeFile.name }} ({{
                                        (resumeFile.size / 1024).toFixed(2)
                                    }}
                                    KB)
                                </p>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    :disabled="
                                        !resumeFile ||
                                        !selectedJdId ||
                                        analyzing
                                    "
                                    class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @click="analyzeResume"
                                >
                                    <span
                                        v-if="analyzing"
                                        class="flex items-center gap-2"
                                    >
                                        <svg
                                            class="h-5 w-5 animate-spin"
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
                                        Analyzing Resume...
                                    </span>
                                    <span v-else>Analyze Resume</span>
                                </button>
                            </div>
                        </div>
                        <div
                            v-if="error"
                            class="mb-6 rounded-md bg-red-50 p-4 dark:bg-red-900/20"
                        >
                            <p
                                class="text-sm font-medium text-red-800 dark:text-red-200"
                            >
                                {{ error }}
                            </p>
                        </div>
                        <div v-if="result" class="space-y-6">
                            <div
                                class="border-t border-gray-200 dark:border-gray-700 pt-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Matching Analysis
                                </h3>
                                <div class="mb-6 text-center">
                                    <div
                                        class="inline-flex flex-col items-center rounded-lg bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-6"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-600 dark:text-gray-400"
                                            >Match Score</span
                                        >
                                        <span
                                            :class="[
                                                'text-6xl font-bold',
                                                getMatchScoreColor(
                                                    result.match_score,
                                                ),
                                            ]"
                                        >
                                            {{ result.match_score }}%
                                        </span>
                                        <span
                                            :class="[
                                                'mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                                getRecommendationBadge(
                                                    result.recommendation,
                                                ),
                                            ]"
                                        >
                                            {{
                                                getRecommendationText(
                                                    result.recommendation,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="mb-6 rounded-lg bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 p-4"
                                >
                                    <h4
                                        class="text-sm font-semibold text-blue-900 dark:text-blue-200"
                                    >
                                        Candidate Information
                                    </h4>
                                    <p
                                        class="mt-1 text-blue-800 dark:text-blue-300"
                                    >
                                        <strong>Name:</strong>
                                        {{ result.candidate_name }}
                                    </p>
                                    <p class="text-blue-800 dark:text-blue-300">
                                        <strong>Experience:</strong>
                                        {{ result.candidate_experience_years }}
                                        years
                                        <span
                                            v-if="result.experience_match"
                                            class="ml-2 text-sm"
                                        >
                                            ({{
                                                result.experience_match ===
                                                "matches"
                                                    ? "✓ Matches requirement"
                                                    : result.experience_match ===
                                                        "above"
                                                      ? "↑ Above requirement"
                                                      : "↓ Below requirement"
                                            }})
                                        </span>
                                    </p>
                                </div>
                                <SkillChipList
                                    :skills="result.matched_primary_skills"
                                    label="Matched Primary Skills (Core)"
                                    icon="✓"
                                    color-scheme="green"
                                    class="mb-6"
                                />
                                <SkillChipList
                                    :skills="result.missing_primary_skills"
                                    label="Missing Primary Skills (Core)"
                                    icon="✗"
                                    color-scheme="red"
                                    class="mb-6"
                                />
                                <SkillChipList
                                    :skills="result.matched_secondary_skills"
                                    label="Matched Secondary Skills"
                                    icon="✓"
                                    color-scheme="blue"
                                    class="mb-6"
                                />
                                <SkillChipList
                                    :skills="result.missing_secondary_skills"
                                    label="Missing Secondary Skills"
                                    icon="✗"
                                    color-scheme="orange"
                                    class="mb-6"
                                />
                                <SkillChipList
                                    :skills="result.matched_generic_skills"
                                    label="Matched Generic Skills"
                                    icon="✓"
                                    color-scheme="gray"
                                    class="mb-6"
                                />
                                <SkillChipList
                                    :skills="result.missing_generic_skills"
                                    label="Missing Generic Skills"
                                    icon="✗"
                                    color-scheme="gray-dark"
                                    class="mb-6"
                                />
                                <div
                                    v-if="
                                        result.strengths &&
                                        result.strengths.length > 0
                                    "
                                    class="mb-6 rounded-lg bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 p-4"
                                >
                                    <h4
                                        class="text-sm font-semibold text-purple-900 dark:text-purple-200 mb-2"
                                    >
                                        Strengths
                                    </h4>
                                    <ul
                                        class="list-disc list-inside space-y-1 text-purple-800 dark:text-purple-300"
                                    >
                                        <li
                                            v-for="strength in result.strengths"
                                            :key="strength"
                                        >
                                            {{ strength }}
                                        </li>
                                    </ul>
                                </div>
                                <div
                                    v-if="
                                        result.concerns &&
                                        result.concerns.length > 0
                                    "
                                    class="mb-6 rounded-lg bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 p-4"
                                >
                                    <h4
                                        class="text-sm font-semibold text-orange-900 dark:text-orange-200 mb-2"
                                    >
                                        Concerns
                                    </h4>
                                    <ul
                                        class="list-disc list-inside space-y-1 text-orange-800 dark:text-orange-300"
                                    >
                                        <li
                                            v-for="concern in result.concerns"
                                            :key="concern"
                                        >
                                            {{ concern }}
                                        </li>
                                    </ul>
                                </div>
                                <div
                                    v-if="result.summary"
                                    class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4"
                                >
                                    <h4
                                        class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2"
                                    >
                                        Summary
                                    </h4>
                                    <p class="text-gray-700 dark:text-gray-300">
                                        {{ result.summary }}
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
