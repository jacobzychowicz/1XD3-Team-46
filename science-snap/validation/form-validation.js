// Edgar, Jamie, Noah, Jacob
// Date Created: 2026-03-20
// Description: Universal form validation - validates all forms with real-time feedback on input validity

// validation/form-validation.js
// Universal AJAX form validation for all forms on the site
// - Uses existing HTML5 validation rules
// - Provides live feedback as user types
// - Green border for valid, red for invalid
// - Handles all forms automatically

(function() {

  /**
   * Sets the visual state of a form field - applies green border for valid, red for invalid
   *
   * @param {HTMLElement} field - The form field element to update
   * @param {boolean} valid - Whether the field is valid (true) or invalid (false)
   * @param {string} message - Error message to display if invalid
   * @returns void
   */
  function setFieldState(field, valid, message) {
    if (valid) {
      field.style.borderColor = 'green';
      removeErrorMessage(field);
    } else {
      field.style.borderColor = 'red';
      showErrorMessage(field, message);
    }
  }

  /**
   * Clears the visual state of a form field - removes border styling and error messages
   *
   * @param {HTMLElement} field - The form field element to clear
   * @returns void
   */
  function clearFieldState(field) {
    field.style.borderColor = '';
    removeErrorMessage(field);
  }

  /**
   * Displays an error message below a form field
   *
   * @param {HTMLElement} field - The form field element
   * @param {string} message - The error message to display
   * @returns void
   */
  function showErrorMessage(field, message) {
    removeErrorMessage(field);
    if (!message) return;
    const msg = document.createElement('div');
    msg.className = 'input-feedback';
    msg.style.color = 'red';
    msg.textContent = message;
    field.insertAdjacentElement('afterend', msg);
  }

  /**
   * Removes any error message displayed below a form field
   *
   * @param {HTMLElement} field - The form field element
   * @returns void
   */
  function removeErrorMessage(field) {
    if (field.nextElementSibling && field.nextElementSibling.classList.contains('input-feedback')) {
      field.nextElementSibling.remove();
    }
  }

  /**
   * Gets the appropriate validation error message for a form field based on its validity state
   *
   * @param {HTMLElement} field - The form field element to validate
   * @returns {string} - The validation error message, or empty string if valid
   */
  function getValidationMessage(field) {
    // Required
    if (field.validity.valueMissing) return 'This field is required.';

    // Email (match PHP: FILTER_VALIDATE_EMAIL)
    if (field.type === 'email' || field.name.toLowerCase().includes('email')) {
      // PHP's FILTER_VALIDATE_EMAIL is roughly:
      // /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i
      const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
      if (field.value && !emailPattern.test(field.value)) {
        return 'Please enter a valid email address.';
      }
    }

    // Password (match PHP server-side)
    if (field.type === 'password' || field.name.toLowerCase().includes('password')) {
      const val = field.value || '';
      const errors = [];
      if (val.length < 8) errors.push('at least 8 characters');
      if (!/[A-Z]/.test(val)) errors.push('one uppercase letter');
      if (!/[a-z]/.test(val)) errors.push('one lowercase letter');
      if (!/[0-9]/.test(val)) errors.push('one number');
      // If you want to require a symbol, uncomment below:
      // if (!/[^A-Za-z0-9]/.test(val)) errors.push('one symbol');
      if (errors.length > 0) {
        return 'Password must contain: ' + errors.join(', ') + '.';
      }
    }

    // Confirm password
    if (field.name && field.name.toLowerCase().includes('confirm')) {
      const form = field.form;
      if (form) {
        const pw = form.querySelector('input[type="password"][name]:not([name*="confirm"])');
        if (pw && field.value !== pw.value) {
          return 'Passwords do not match.';
        }
      }
    }

    // HTML5 built-in
    if (field.validity.typeMismatch) {
      if (field.type === 'url') return 'Please enter a valid URL.';
      return 'Please enter a valid value.';
    }
    if (field.validity.tooShort) return `Please lengthen to at least ${field.minLength} characters.`;
    if (field.validity.tooLong) return `Please shorten to no more than ${field.maxLength} characters.`;
    if (field.validity.patternMismatch) return 'Please match the requested format.';
    if (field.validity.rangeUnderflow) return `Value must be at least ${field.min}.`;
    if (field.validity.rangeOverflow) return `Value must be at most ${field.max}.`;
    if (field.validity.stepMismatch) return 'Please enter a valid value.';
    return field.validationMessage || 'Invalid input.';
  }

  /**
   * Validates a single form field and updates its visual state and error message
   *
   * @param {HTMLElement} field - The form field element to validate
   * @returns {boolean} - True if field is valid, false otherwise
   */
  function validateField(field) {
    if (field.disabled || field.type === 'hidden' || field.type === 'submit' || field.type === 'button') return true;
    if (field.willValidate === false) return true;
    if (field.value === '' && !field.required) {
      clearFieldState(field);
      return true;
    }

    // Custom password validation: override HTML5 validity for password fields
    if (field.type === 'password' || field.name.toLowerCase().includes('password')) {
      const val = field.value || '';
      const errors = [];
      if (val.length < 8) errors.push('at least 8 characters');
      if (!/[A-Z]/.test(val)) errors.push('one uppercase letter');
      if (!/[a-z]/.test(val)) errors.push('one lowercase letter');
      if (!/[0-9]/.test(val)) errors.push('one number');
      // If you want to require a symbol, uncomment below:
      // if (!/[^A-Za-z0-9]/.test(val)) errors.push('one symbol');
      if (errors.length > 0) {
        setFieldState(field, false, 'Password must contain: ' + errors.join(', ') + '.');
        return false;
      }
    }

    // Custom confirm password validation
    if (field.name && field.name.toLowerCase().includes('confirm')) {
      const form = field.form;
      if (form) {
        const pw = form.querySelector('input[type="password"][name]:not([name*="confirm"])');
        if (pw && field.value !== pw.value) {
          setFieldState(field, false, 'Passwords do not match.');
          return false;
        }
      }
    }

    // Email validation override
    if (field.type === 'email' || field.name.toLowerCase().includes('email')) {
      const emailPattern = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
      if (field.value && !emailPattern.test(field.value)) {
        setFieldState(field, false, 'Please enter a valid email address.');
        return false;
      }
    }

    // Default HTML5 validation
    const valid = field.checkValidity();
    setFieldState(field, valid, valid ? '' : getValidationMessage(field));
    return valid;
  }

  /**
   * Validates all fields in a form
   *
   * @param {HTMLFormElement} form - The form element containing fields to validate
   * @returns {boolean} - True if all fields are valid, false if any field is invalid
   */
  function validateForm(form) {
    let valid = true;
    Array.from(form.elements).forEach(field => {
      if (!validateField(field)) valid = false;
    });
    return valid;
  }

  /**
   * Sets up validation listeners on all forms in the document for real-time validation feedback
   *
   * @returns void
   */
  function setupValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      // Validate on input
      form.addEventListener('input', function(e) {
        if (e.target && e.target.form === form) {
          validateField(e.target);
        }
      });
      // Validate on blur
      form.addEventListener('blur', function(e) {
        if (e.target && e.target.form === form) {
          validateField(e.target);
        }
      }, true);
      // Validate all on submit
      form.addEventListener('submit', function(e) {
        if (!validateForm(form)) {
          e.preventDefault();
        }
      });
    });
  }

  // Run on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', setupValidation);
})();
