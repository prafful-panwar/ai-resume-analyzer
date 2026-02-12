import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import eslintConfigPrettier from "eslint-config-prettier";

export default [
  js.configs.recommended,
  ...pluginVue.configs["flat/recommended"],
  eslintConfigPrettier,
  {
    languageOptions: {
      globals: {
        window: "readonly",
        document: "readonly",
        console: "readonly",
        process: "readonly",
        route: "readonly",
        axios: "readonly",
        Echo: "readonly",
      }
    },
    rules: {
      "vue/multi-word-component-names": "off",
      "vue/prop-name-casing": "off",
      "no-undef": "off", // Temporarily off if globals covers most, or keep on and add globals. 
                         // Actually, keeping on is better but ensuring globals are there.
                         // But for now, let's keep it on and rely on globals.
      "no-useless-assignment": "off", // This seems aggressive in some cases.
      "no-unused-vars": ["error", { "argsIgnorePattern": "^_" }],
    }
  }
];
