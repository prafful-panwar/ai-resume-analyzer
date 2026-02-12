<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recent_activity: {
        type: Array,
        required: true,
    },
    top_talent: {
        type: Array,
        required: true,
    },
});

const getScoreColor = (score) => {
    if (score >= 80) return "text-green-600 dark:text-green-400";
    if (score >= 60) return "text-yellow-600 dark:text-yellow-400";
    return "text-red-600 dark:text-red-400";
};

const getStatusBadge = (status) => {
    const badges = {
        completed:
            "bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200",
        processing:
            "bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 animate-pulse",
        pending:
            "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
        failed: "bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200",
    };
    return badges[status] || badges.pending;
};
</script>

<template>
    <Head title="Hiring Command Center" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Hiring Command Center
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Pulse Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Total Analyses -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500 relative group"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Total Analyses
                                    </p>
                                    <div
                                        class="relative inline-block cursor-help group/tooltip"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div
                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 hidden group-hover/tooltip:block w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50 text-center leading-normal"
                                        >
                                            Total number of resumes analyzed
                                            across all time.
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-gray-900 border-t-0"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                                >
                                    {{ stats.total_analyses }}
                                </p>
                            </div>
                            <div
                                class="bg-indigo-100 dark:bg-indigo-900/50 p-3 rounded-full"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
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
                            </div>
                        </div>
                    </div>

                    <!-- High Potentials -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500 relative group"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        High Potentials
                                    </p>
                                    <div
                                        class="relative inline-block cursor-help group/tooltip"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div
                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 hidden group-hover/tooltip:block w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50 text-center leading-normal"
                                        >
                                            Candidates with an AI match score of
                                            80% or higher.
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-gray-900 border-t-0"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                                >
                                    {{ stats.high_potentials }}
                                </p>
                            </div>
                            <div
                                class="bg-green-100 dark:bg-green-900/50 p-3 rounded-full"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- AI Power Used -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500 relative group"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Tokens Used
                                    </p>
                                    <div
                                        class="relative inline-block cursor-help group/tooltip"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div
                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 hidden group-hover/tooltip:block w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50 text-center leading-normal"
                                        >
                                            Total AI computation power consumed
                                            (prompt + completion).
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-gray-900 border-t-0"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                                >
                                    {{
                                        stats.total_tokens?.toLocaleString() ||
                                        0
                                    }}
                                </p>
                            </div>
                            <div
                                class="bg-purple-100 dark:bg-purple-900/50 p-3 rounded-full"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-purple-600 dark:text-purple-400"
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
                            </div>
                        </div>
                    </div>

                    <!-- Pending Queue -->
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500 relative group"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <p
                                        class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        In Queue
                                    </p>
                                    <div
                                        class="relative inline-block cursor-help group/tooltip"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div
                                            class="absolute top-full left-1/2 -translate-x-1/2 mt-2 hidden group-hover/tooltip:block w-48 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50 text-center leading-normal"
                                        >
                                            Analyses currently waiting to be
                                            processed by the AI.
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 border-8 border-transparent border-b-gray-900 border-t-0"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                                >
                                    {{ stats.pending_count }}
                                </p>
                            </div>
                            <div
                                class="bg-yellow-100 dark:bg-yellow-900/50 p-3 rounded-full"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Top Talent Leaderboard -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div
                            class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center"
                        >
                            <h3
                                class="text-lg font-bold text-gray-900 dark:text-white"
                            >
                                🌟 Top Talent Leaderboard
                            </h3>
                            <Link
                                :href="route('resume-analyses.index')"
                                class="text-sm text-indigo-600 hover:text-indigo-500 font-semibold"
                                >View All</Link
                            >
                        </div>
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                        >
                                            Candidate
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                        >
                                            Role
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                                        >
                                            Score
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                >
                                    <tr
                                        v-for="talent in top_talent"
                                        :key="talent.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="text-sm font-bold text-gray-900 dark:text-white max-w-[200px] truncate"
                                                :title="
                                                    talent.result
                                                        ?.candidate_name
                                                "
                                            >
                                                {{
                                                    talent.result
                                                        ?.candidate_name ||
                                                    "Incognito"
                                                }}
                                            </div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 max-w-[200px] truncate"
                                                :title="
                                                    talent.original_filename
                                                "
                                            >
                                                {{ talent.original_filename }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="text-sm text-gray-900 dark:text-white"
                                            >
                                                {{
                                                    talent.job_description
                                                        ?.job_role || "No Role"
                                                }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'resume-analyses.show',
                                                        talent.id,
                                                    )
                                                "
                                                :class="[
                                                    'text-xl font-bold',
                                                    getScoreColor(
                                                        talent.result
                                                            ?.match_score,
                                                    ),
                                                ]"
                                            >
                                                {{
                                                    talent.result?.match_score
                                                }}%
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="top_talent.length === 0">
                                        <td
                                            colspan="3"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                        >
                                            No candidates analyzed yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div
                            class="p-6 border-b border-gray-200 dark:border-gray-700"
                        >
                            <h3
                                class="text-lg font-bold text-gray-900 dark:text-white"
                            >
                                🕒 Recent Activity
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="flow-root">
                                <ul role="list" class="-mb-8">
                                    <li
                                        v-for="(item, idx) in recent_activity"
                                        :key="item.id"
                                    >
                                        <div class="relative pb-8">
                                            <span
                                                v-if="
                                                    idx !==
                                                    recent_activity.length - 1
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
                                                            getStatusBadge(
                                                                item.status,
                                                            ),
                                                            'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800',
                                                        ]"
                                                    >
                                                        <svg
                                                            v-if="
                                                                item.status ===
                                                                'completed'
                                                            "
                                                            class="h-5 w-5 text-green-600"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M5 13l4 4L19 7"
                                                            />
                                                        </svg>
                                                        <svg
                                                            v-else-if="
                                                                item.status ===
                                                                'failed'
                                                            "
                                                            class="h-5 w-5 text-red-600"
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
                                                        <svg
                                                            v-else
                                                            class="h-5 w-5 text-gray-600"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
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
                                                            Analyzed
                                                            <span
                                                                class="font-medium text-gray-900 dark:text-white"
                                                                >{{
                                                                    item.original_filename
                                                                }}</span
                                                            >
                                                            for
                                                            <span
                                                                class="font-medium text-indigo-600 dark:text-indigo-400"
                                                                >{{
                                                                    item
                                                                        .job_description
                                                                        ?.job_role ||
                                                                    "Unknown Role"
                                                                }}</span
                                                            >
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="whitespace-nowrap text-right text-xs text-gray-500 dark:text-gray-400"
                                                    >
                                                        <time>{{
                                                            new Date(
                                                                item.created_at,
                                                            ).toLocaleTimeString(
                                                                [],
                                                                {
                                                                    hour: "2-digit",
                                                                    minute: "2-digit",
                                                                },
                                                            )
                                                        }}</time>
                                                        <div class="mt-1">
                                                            <Link
                                                                :href="
                                                                    route(
                                                                        'resume-analyses.show',
                                                                        item.id,
                                                                    )
                                                                "
                                                                class="text-indigo-600 hover:text-indigo-500 font-semibold"
                                                                >Details →</Link
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li
                                        v-if="recent_activity.length === 0"
                                        class="text-center py-6 text-gray-500"
                                    >
                                        No recent activity.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Card -->
                <div
                    class="bg-indigo-600 rounded-lg shadow-lg p-8 flex flex-col md:flex-row items-center justify-between text-white"
                >
                    <div>
                        <h2 class="text-2xl font-bold">
                            Ready for more talent?
                        </h2>
                        <p class="mt-2 text-indigo-100 opacity-90">
                            Start matching new resumes against your open roles
                            instantly.
                        </p>
                    </div>
                    <Link
                        :href="route('resume-matching')"
                        class="mt-6 md:mt-0 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 shadow-sm transition"
                    >
                        Start New Analysis
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
