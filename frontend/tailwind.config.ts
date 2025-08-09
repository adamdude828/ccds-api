import type { Config } from "tailwindcss";
import { colors } from "@challenger-school/do-git-mis-components-storybook";
console.log(colors);

export default {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
    "./node_modules/@challenger-school/do-git-mis-components-storybook/dist/*.js",
  ],
  theme: {
    extend: {
      colors: {
        background: "var(--background)",
        foreground: "var(--foreground)",
        ...colors
      },
    },
  },
  plugins: [],
} satisfies Config;
