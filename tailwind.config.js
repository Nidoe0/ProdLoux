/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/css/**/*.css'
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#0B5FFF',
          700: '#084FD6'
        },
        accent: '#00B38A',
        neutral: {
          900: '#111827',
          700: '#374151',
          400: '#9CA3AF'
        }
      },
      borderRadius: {
        lg: '12px'
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui']
      }
    }
  },
  plugins: []
}
