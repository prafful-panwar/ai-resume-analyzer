<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { onMounted, onUnmounted, watch } from "vue";
import {
    formatRecommendation,
    getScoreColorClasses,
    getRecommendationBadgeClasses,
} from "@/Utils/analysis";

const props = defineProps({
    analysis: {
        type: Object,
        required: true,
    },
});

const retryForm = useForm({});

// Polling Logic
let refreshInterval = null;

const stopPolling = () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
};

const startPolling = () => {
    if (refreshInterval) return;

    refreshInterval = setInterval(() => {
        router.reload({
            only: ["analysis"],
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {},
            onError: (_err) => {},
        });
    }, 3000);
};

onMounted(() => {
    if (
        props.analysis.status === "pending" ||
        props.analysis.status === "processing"
    ) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});

// Watch for status changes to start/stop polling
watch(
    () => props.analysis.status,
    (newStatus, _oldStatus) => {
        // Handle Polling State
        if (newStatus === "pending" || newStatus === "processing") {
            startPolling();
        } else if (newStatus === "completed" || newStatus === "failed") {
            stopPolling();
            // Toast is handled globally in AuthenticatedLayout.vue
        }
    },
);

const retryAnalysis = () => {
    retryForm.post(route("resume-analyses.retry", props.analysis.id));
};

const redoAnalysis = () => {
    retryForm.transform((data) => ({
        ...data,
        force: true,
    }));
    retryForm.post(route("resume-analyses.retry", props.analysis.id), {
        onFinish: () => retryForm.reset(),
    });
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
    <Head :title="`Analysis - ${analysis.job_description.job_role}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Resume Analysis - {{ analysis.job_description.job_role }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Status -->
                        <div
                            v-if="
                                analysis.status === 'pending' ||
                                analysis.status === 'processing'
                            "
                            class="text-center py-12"
                        >
                            <div class="inline-flex items-center gap-3">
                                <svg
                                    class="h-8 w-8 animate-spin text-indigo-600"
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
                                <span class="text-lg">{{
                                    analysis.status === "pending"
                                        ? "Queued for processing..."
                                        : "Processing..."
                                }}</span>
                            </div>
                            <p
                                class="mt-4 text-sm text-gray-500 dark:text-gray-400"
                            >
                                This page will auto-refresh when complete.
                            </p>
                        </div>

                        <!-- Failed -->
                        <div
                            v-else-if="analysis.status === 'failed'"
                            class="rounded-md bg-red-50 dark:bg-red-900/20 p-4"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p
                                        class="text-red-800 dark:text-red-200 font-semibold"
                                    >
                                        Analysis Failed
                                    </p>
                                    <p
                                        class="text-red-700 dark:text-red-300 text-sm mt-2"
                                    >
                                        {{ analysis.error_message }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="ml-4 inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                                    :disabled="retryForm.processing"
                                    @click="retryAnalysis"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        />
                                    </svg>
                                    {{
                                        retryForm.processing
                                            ? "Retrying..."
                                            : "Retry Analysis"
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- Completed - Show Results (reuse from ResumeMatching.vue) -->
                        <div
                            v-else-if="
                                analysis.status === 'completed' &&
                                analysis.result
                            "
                            class="space-y-6"
                        >
                            <!-- Comparison Header: Job vs Candidate -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Job Context -->
                                <div
                                    class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm"
                                >
                                    <h3
                                        class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Evaluating Against
                                    </h3>
                                    <div
                                        class="mt-2 text-indigo-900 dark:text-indigo-300"
                                    >
                                        <h2 class="text-lg font-bold">
                                            {{
                                                analysis.job_description
                                                    .job_role
                                            }}
                                        </h2>
                                        <p class="mt-1 text-sm opacity-80">
                                            <strong>Required Exp:</strong>
                                            {{
                                                analysis.job_description
                                                    .experience_min
                                            }}
                                            -
                                            {{
                                                analysis.job_description
                                                    .experience_max
                                            }}
                                            years
                                        </p>
                                    </div>
                                </div>

                                <!-- Candidate Info -->
                                <div
                                    class="rounded-lg border border-indigo-100 bg-indigo-50/50 dark:border-indigo-900/30 dark:bg-indigo-900/10 p-4 shadow-sm"
                                >
                                    <h4
                                        class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300"
                                    >
                                        Candidate Information
                                    </h4>
                                    <div
                                        class="mt-2 text-indigo-900 dark:text-indigo-300"
                                    >
                                        <h2 class="text-lg font-bold">
                                            {{ analysis.result.candidate_name }}
                                        </h2>
                                        <p class="mt-1 text-sm opacity-80">
                                            <strong>Experience:</strong>
                                            {{
                                                analysis.result
                                                    .candidate_experience_years
                                            }}
                                            years
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Resume Info -->
                            <div
                                class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h4
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            📄
                                            {{
                                                analysis.original_filename ||
                                                "Resume"
                                            }}
                                        </h4>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                                        >
                                            Analysis #{{ analysis.id }} •
                                            Submitted
                                            {{
                                                new Date(
                                                    analysis.created_at,
                                                ).toLocaleString()
                                            }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700 dark:hover:bg-gray-700 disabled:opacity-50"
                                            :disabled="retryForm.processing"
                                            @click="redoAnalysis"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                                />
                                            </svg>
                                            {{
                                                retryForm.processing
                                                    ? "Retrying..."
                                                    : "Redo Analysis"
                                            }}
                                        </button>
                                        <a
                                            :href="
                                                route(
                                                    'resume-analyses.download',
                                                    analysis.id,
                                                )
                                            "
                                            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Match Score -->
                            <div class="text-center mb-6">
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
                                                analysis.result.match_score,
                                            ),
                                        ]"
                                    >
                                        {{ analysis.result.match_score }}%
                                    </span>
                                    <span
                                        :class="[
                                            'mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                            getRecommendationBadge(
                                                analysis.result.recommendation,
                                            ),
                                        ]"
                                    >
                                        {{
                                            getRecommendationText(
                                                analysis.result.recommendation,
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Token Usage (AI Expert Feature) -->
                            <div
                                v-if="analysis.total_tokens"
                                class="rounded-lg border border-indigo-100 bg-indigo-50/30 dark:border-indigo-900/30 dark:bg-indigo-900/10 p-4"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-indigo-600 dark:text-indigo-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"
                                        />
                                    </svg>
                                    <h4
                                        class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300"
                                    >
                                        AI Usage Statistics
                                    </h4>
                                </div>
                                <div class="flex gap-6">
                                    <div>
                                        <p
                                            class="text-xs text-indigo-700/70 dark:text-indigo-400/70"
                                        >
                                            Total Tokens
                                        </p>
                                        <p
                                            class="text-lg font-bold text-indigo-900 dark:text-indigo-200"
                                        >
                                            {{ analysis.total_tokens }}
                                        </p>
                                    </div>
                                    <div
                                        class="w-px bg-indigo-200 dark:bg-indigo-800"
                                    ></div>
                                    <div>
                                        <p
                                            class="text-xs text-indigo-700/70 dark:text-indigo-400/70"
                                        >
                                            Prompt
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-indigo-800 dark:text-indigo-300"
                                        >
                                            {{ analysis.prompt_tokens }}
                                        </p>
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs text-indigo-700/70 dark:text-indigo-400/70"
                                        >
                                            Completion
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-indigo-800 dark:text-indigo-300"
                                        >
                                            {{ analysis.completion_tokens }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Matched Skills -->
                            <div
                                v-if="
                                    analysis.result.matched_skills &&
                                    analysis.result.matched_skills.length > 0
                                "
                            >
                                <h4
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2"
                                >
                                    ✓ Matched Skills
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="skill in analysis.result
                                            .matched_skills"
                                        :key="skill"
                                        class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-900/50 dark:text-green-200"
                                    >
                                        {{ skill }}
                                    </span>
                                </div>
                            </div>

                            <!-- Missing Skills -->
                            <div
                                v-if="
                                    analysis.result.missing_skills &&
                                    analysis.result.missing_skills.length > 0
                                "
                            >
                                <h4
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2"
                                >
                                    ✗ Missing Skills
                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="skill in analysis.result
                                            .missing_skills"
                                        :key="skill"
                                        class="inline-flex rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800 dark:bg-red-900/50 dark:text-red-200"
                                    >
                                        {{ skill }}
                                    </span>
                                </div>
                            </div>

                            <!-- Summary -->
                            <div
                                v-if="analysis.result.summary"
                                class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4"
                            >
                                <h4
                                    class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2"
                                >
                                    Summary
                                </h4>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ analysis.result.summary }}
                                </p>
                            </div>

                            <!-- Logs Section -->
                            <div
                                v-if="analysis.logs && analysis.logs.length > 0"
                                class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8"
                            >
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Analysis History & Logs
                                </h3>
                                <div class="flow-root">
                                    <ul role="list" class="-mb-8">
                                        <li
                                            v-for="(
                                                log, logIdx
                                            ) in analysis.logs"
                                            :key="log.id"
                                        >
                                            <div class="relative pb-8">
                                                <span
                                                    v-if="
                                                        logIdx !==
                                                        analysis.logs.length - 1
                                                    "
                                                    class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                                                    aria-hidden="true"
                                                ></span>
                                                <div
                                                    class="relative flex space-x-3"
                                                >
                                                    <div>
                                                        <span
                                                            :class="[
                                                                log.status ===
                                                                'completed'
                                                                    ? 'bg-green-500'
                                                                    : 'bg-red-500',
                                                                'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800',
                                                            ]"
                                                        >
                                                            <svg
                                                                v-if="
                                                                    log.status ===
                                                                    'completed'
                                                                "
                                                                class="h-5 w-5 text-white"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                                aria-hidden="true"
                                                            >
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                            <svg
                                                                v-else
                                                                class="h-5 w-5 text-white"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                                aria-hidden="true"
                                                            >
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div
                                                        class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5"
                                                    >
                                                        <div>
                                                            <p
                                                                class="text-sm text-gray-500 dark:text-gray-400"
                                                            >
                                                                Attempt #{{
                                                                    log.attempt
                                                                }}
                                                                -
                                                                <span
                                                                    class="font-medium text-gray-900 dark:text-gray-100"
                                                                >
                                                                    {{
                                                                        log.status ===
                                                                        "completed"
                                                                            ? "Completed"
                                                                            : "Failed"
                                                                    }}
                                                                </span>
                                                            </p>
                                                            <div
                                                                v-if="
                                                                    log.error_message
                                                                "
                                                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                                                            >
                                                                {{
                                                                    log.error_message
                                                                }}
                                                            </div>
                                                            <div
                                                                v-else-if="
                                                                    log.result
                                                                "
                                                                class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                                                            >
                                                                Score:
                                                                {{
                                                                    log.result
                                                                        .match_score
                                                                }}%
                                                                <span
                                                                    v-if="
                                                                        log.total_tokens
                                                                    "
                                                                    class="ml-2 text-xs opacity-70"
                                                                >
                                                                    •
                                                                    {{
                                                                        log.total_tokens
                                                                    }}
                                                                    tokens
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div
                                                            class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400"
                                                        >
                                                            {{
                                                                new Date(
                                                                    log.created_at,
                                                                ).toLocaleString()
                                                            }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Local toast removed. Notifications handled by AuthenticatedLayout -->
    </AuthenticatedLayout>
</template>
