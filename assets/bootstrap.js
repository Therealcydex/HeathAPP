// Correct import if you are using the latest Stimulus version (@hotwired/stimulus)
import { Application } from '@hotwired/stimulus';

// Import Bootstrap
import 'bootstrap';

// Create the Stimulus application
const application = Application.start();

// Find controllers from a specific directory
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));
