const { defineConfig } = require("cypress");

module.exports = defineConfig({
	env: {
		wpUser: 'admin',
		wpPassword: 'a525234',
	},
  e2e: {
	  baseUrl: 'http://localhost:8888',
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
