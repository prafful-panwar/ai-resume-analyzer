<script setup>
defineProps({
    skills: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        required: true,
    },
    icon: {
        type: String,
        default: "",
    },
    colorScheme: {
        type: String,
        default: "gray",
    },
});

const getColorClasses = (scheme) => {
    switch (scheme) {
        case "green":
            return "bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200";
        case "red":
            return "bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200";
        case "blue":
            return "bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200";
        case "orange":
            return "bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200";
        case "gray-dark":
            return "bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400";
        case "gray":
        default:
            return "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200";
    }
};
</script>

<template>
    <div v-if="skills && skills.length > 0" class="mb-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ icon }} {{ label }}
        </h4>
        <div class="flex flex-wrap gap-2">
            <span
                v-for="skill in skills"
                :key="skill"
                :class="['inline-flex rounded-full px-3 py-1 text-sm font-medium', getColorClasses(colorScheme)]"
            >
                {{ skill }}
            </span>
        </div>
    </div>
</template>
