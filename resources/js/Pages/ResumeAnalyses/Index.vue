<script setup>
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    analyses: {
        type: Array,
        default: () => [],
    },
});

const getScoreColor = (score) => {
    if (score >= 80) return "bg-green-500 text-green-600";
    if (score >= 40) return "bg-amber-500 text-amber-600";
    return "bg-red-500 text-red-600";
};

const getStatusBadge = (status) => {
    const badges = {
        pending:
            "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
        processing:
            "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
        completed:
            "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
        failed: "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
    };
    return badges[status] || badges.pending;
};

const getStatusText = (status) => {
    const texts = {
        pending: "Pending",
        processing: "Processing...",
        completed: "Completed",
        failed: "Failed",
    };
    return texts[status] || status;
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString("en-US", {
        month: "numeric",
        day: "numeric",
        year: "numeric",
        hour: "numeric",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
    });
};

// Auto-refresh every 5 seconds if there are pending/processing analyses
const hasPendingAnalyses = props.analyses.some(
    (a) => a.status === "pending" || a.status === "processing",
);

if (hasPendingAnalyses) {
    setInterval(() => {
        router.reload({ only: ["analyses"] });
    }, 5000);
}
</script>

<template>
    <Head title="Resume Analyses" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Resume Analyses
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="analyses.length === 0" class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">
                        No resume analyses yet.
                    </p>
                    <a
                        :href="route('resume-matching')"
                        class="mt-4 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                    >
                        Analyze a Resume
                    </a>
                </div>

                <div
                    v-else
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6"
                >
                    <div
                        v-for="analysis in analyses"
                        :key="analysis.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer relative group"
                        @click="
                            router.visit(
                                route('resume-analyses.show', analysis.id),
                            )
                        "
                    >
                        <!-- Header: Title & Status -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1 pr-4">
                                <h3
                                    class="text-lg font-bold text-indigo-900 dark:text-indigo-300 truncate"
                                    :title="analysis.job_description.job_role"
                                >
                                    {{ analysis.job_description.job_role }}
                                </h3>
                                <div
                                    class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mt-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 shrink-0"
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
                                    <span
                                        class="truncate"
                                        :title="analysis.original_filename"
                                        >{{
                                            analysis.original_filename ||
                                            "Resume"
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="text-[11px] text-gray-400 dark:text-gray-500 mt-1"
                                >
                                    Submitted
                                    {{ formatDate(analysis.created_at) }}
                                </div>
                            </div>
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                    getStatusBadge(analysis.status),
                                ]"
                            >
                                {{ getStatusText(analysis.status) }}
                            </span>
                        </div>

                        <!-- Progress Section -->
                        <div
                            v-if="
                                analysis.status === 'completed' &&
                                analysis.result
                            "
                            class="mt-6"
                        >
                            <div class="flex items-end justify-between mb-2">
                                <span
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Match Score:
                                    {{ analysis.result.match_score }}%
                                </span>
                                <span
                                    :class="[
                                        'text-3xl font-black leading-none',
                                        getScoreColor(
                                            analysis.result.match_score,
                                        ).split(' ')[1],
                                    ]"
                                >
                                    {{ analysis.result.match_score }}%
                                </span>
                            </div>
                            <div
                                class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden"
                            >
                                <div
                                    :class="[
                                        'h-full transition-all duration-1000 ease-out',
                                        getScoreColor(
                                            analysis.result.match_score,
                                        ).split(' ')[0],
                                    ]"
                                    :style="{
                                        width: `${analysis.result.match_score}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Fallback for Non-completed statuses -->
                        <div
                            v-else-if="analysis.status === 'failed'"
                            class="mt-6 pt-4 border-t border-red-50 dark:border-red-900/20"
                        >
                            <p class="text-xs text-red-500 line-clamp-2">
                                {{
                                    analysis.error_message ||
                                    "Analysis failed due to a processing error."
                                }}
                            </p>
                        </div>
                        <div
                            v-else
                            class="mt-6 pt-4 border-t border-gray-50 dark:border-gray-700/50 flex items-center gap-2"
                        >
                            <div class="flex space-x-1">
                                <div
                                    class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.3s]"
                                ></div>
                                <div
                                    class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce [animation-delay:-0.15s]"
                                ></div>
                                <div
                                    class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce"
                                ></div>
                            </div>
                            <span class="text-xs text-gray-400 italic"
                                >Processing resume context...</span
                            >
                        </div>

                        <!-- View Detail Hint -->
                        <div
                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
