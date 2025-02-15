// assets/controllers/hello_controller.js
import { Controller } from '@hotwired/stimulus'; // Keep this one

// Remove this line because it's conflicting:
 // import { Controller } from 'stimulus'; 

export default class extends Controller {
    connect() {
        console.log("Hello, Stimulus!", this.element);
    }
}
