<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Resume Analyzer - AI-Powered Resume Matching" />

    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800"
    >
        <!-- Navigation -->
        <nav
            class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1
                            class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent"
                        >
                            Resume Analyzer
                        </h1>
                    </div>
                    <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
                            >
                                Get Started
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h2
                    class="text-5xl md:text-6xl font-extrabold text-gray-900 dark:text-white mb-6"
                >
                    AI-Powered Resume
                    <span
                        class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent"
                    >
                        Matching
                    </span>
                </h2>
                <p
                    class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto"
                >
                    Analyze resumes against job descriptions using advanced AI.
                    Get instant matching scores, skill analysis, and hiring
                    recommendations.
                </p>
                <div class="flex gap-4 justify-center">
                    <Link
                        :href="route('register')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all transform hover:scale-105 shadow-lg"
                    >
                        Start Analyzing
                    </Link>
                    <a
                        href="#features"
                        class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all border-2 border-gray-200 dark:border-gray-700"
                    >
                        Learn More
                    </a>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <h3
                class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12"
            >
                Powerful Features
            </h3>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-indigo-600 dark:text-indigo-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <h4
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        AI-Powered Analysis
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300">
                        Advanced AI analyzes resumes and provides detailed
                        matching scores, skill gaps, and recommendations.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-purple-600 dark:text-purple-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                    </div>
                    <h4
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        Queue-Based Processing
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300">
                        Asynchronous processing with automatic retries, rate
                        limiting, and real-time status updates.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-green-600 dark:text-green-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                    <h4
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        Job Description Management
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300">
                        Create and manage multiple job descriptions. Analyze
                        unlimited resumes against each position.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-yellow-600 dark:text-yellow-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"
                            />
                        </svg>
                    </div>
                    <h4
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        Detailed Insights
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300">
                        Get comprehensive analysis including skills match,
                        experience alignment, and hiring recommendations.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-red-600 dark:text-red-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            />
                        </svg>
                    </div>
                    <h4
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        Secure & Private
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300">
                        Your data is encrypted and secure. Filename sanitization
                        prevents security vulnerabilities.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div
                    class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg hover:shadow-xl transition-shadow"
                >
                    <div
                        class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-blue-600 dark:text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </div>
                    <h4
                        class="text-xl font-semibold text-gray-900 dark:text-white mb-2"
                    >
                        Retry Failed Jobs
                    </h4>
                    <p class="text-gray-600 dark:text-gray-300">
                        Automatic retries with exponential backoff. Manual retry
                        option for failed analyses.
                    </p>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-white dark:bg-gray-800 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3
                    class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12"
                >
                    How It Works
                </h3>
                <div class="grid md:grid-cols-4 gap-8">
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4"
                        >
                            1
                        </div>
                        <h4
                            class="font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Create Job Description
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Add job requirements, skills, and experience needed
                        </p>
                    </div>
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-purple-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4"
                        >
                            2
                        </div>
                        <h4
                            class="font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Upload Resume
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Upload PDF resume for analysis
                        </p>
                    </div>
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-green-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4"
                        >
                            3
                        </div>
                        <h4
                            class="font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            AI Analysis
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            AI analyzes and scores the match
                        </p>
                    </div>
                    <div class="text-center">
                        <div
                            class="w-16 h-16 bg-yellow-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4"
                        >
                            4
                        </div>
                        <h4
                            class="font-semibold text-gray-900 dark:text-white mb-2"
                        >
                            Get Results
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            View detailed analysis and recommendations
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div
                class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-12 text-center"
            >
                <h3 class="text-3xl font-bold text-white mb-4">
                    Ready to streamline your hiring process?
                </h3>
                <p class="text-indigo-100 text-lg mb-8">
                    Start analyzing resumes with AI-powered matching today
                </p>
                <Link
                    :href="route('register')"
                    class="inline-block bg-white text-indigo-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg"
                >
                    Get Started Free
                </Link>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p>
                    &copy; 2026 Resume Analyzer. Built with Laravel, Vue.js &
                    AI.
                </p>
            </div>
        </footer>
    </div>
</template>
