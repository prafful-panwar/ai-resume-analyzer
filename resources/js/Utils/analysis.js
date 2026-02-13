/**
 * Format the recommendation string into a human-readable title.
 */
export const formatRecommendation = (recommendation) => {
    const texts = {
        perfect_match: "Perfect Match",
        strong_match: "Strong Match",
        good_match: "Good Match",
        partial_match: "Partial Match",
        weak_match: "Weak Match",
        poor_match: "Poor Match",
    };
    
    if (!recommendation) return "";
    
    return texts[recommendation] || 
           recommendation
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase());
};

/**
 * Get the Tailwind CSS classes for the recommendation badge.
 */
export const getRecommendationBadgeClasses = (recommendation) => {
    const badges = {
        perfect_match:
            "bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200",
        strong_match:
            "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
        good_match:
            "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
        partial_match:
            "bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200",
        weak_match:
            "bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200",
        poor_match: 
            "bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200",
    };
    return badges[recommendation] || badges.partial_match;
};

/**
 * Get the Tailwind CSS classes for score-based coloring.
 */
export const getScoreColorClasses = (score) => {
    if (score >= 80) return {
        bg: "bg-green-500",
        text: "text-green-600 dark:text-green-400",
    };
    if (score >= 60) return {
        bg: "bg-amber-500",
        text: "text-amber-600 dark:text-amber-400",
    };
    if (score >= 40) return {
        bg: "bg-orange-500",
        text: "text-orange-600 dark:text-orange-400",
    };
    return {
        bg: "bg-red-500",
        text: "text-red-600 dark:text-red-400",
    };
};

/**
 * Format date strings to a readable format.
 */
export const formatDate = (dateString) => {
    if (!dateString) return "";
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
